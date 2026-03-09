"""
db_loader.py
Loads data from the Dream Mulk MySQL database for ML training and inference.
Reads ONLY from existing properties, areas, branches tables.
Never writes to those tables.
"""

import os
import json
import logging
import pandas as pd
import sqlalchemy
from sqlalchemy import text
from typing import Optional, List

logger = logging.getLogger(__name__)


class DatabaseLoader:

    def __init__(self):
        self.engine = self._create_engine()

    def _create_engine(self) -> sqlalchemy.Engine:
        url = (
            f"mysql+pymysql://{os.getenv('DB_USERNAME', 'root')}:"
            f"{os.getenv('DB_PASSWORD', '')}@"
            f"{os.getenv('DB_HOST', '127.0.0.1')}:"
            f"{os.getenv('DB_PORT', '3306')}/"
            f"{os.getenv('DB_DATABASE', 'dream_mulk')}"
            f"?charset=utf8mb4"
        )
        return sqlalchemy.create_engine(
            url,
            pool_size=5,
            pool_recycle=3600,
            connect_args={"connect_timeout": 10},
        )

    def test_connection(self) -> bool:
        try:
            with self.engine.connect() as conn:
                conn.execute(text("SELECT 1"))
            return True
        except Exception as e:
            logger.error(f"DB connection failed: {e}")
            return False

    # ── Training data ─────────────────────────────────────────────────────────

    def load_training_data(self) -> Optional[pd.DataFrame]:
        """All active properties with features for XGBoost training."""
        query = text("""
            SELECT
                p.id AS property_id,
                p.price,
                p.area_size,
                p.bedrooms,
                p.bathrooms,
                p.property_type,
                p.listing_type,
                COALESCE(p.floor, 0)         AS floor,
                COALESCE(p.year_built, 2000) AS year_built,
                p.location,
                p.area_id,
                p.branch_id,
                COALESCE(p.has_parking,  0) AS has_parking,
                COALESCE(p.has_elevator, 0) AS has_elevator,
                COALESCE(p.has_balcony,  0) AS has_balcony,
                COALESCE(p.has_garden,   0) AS has_garden,
                COALESCE(p.has_pool,     0) AS has_pool,
                COALESCE(p.has_security, 0) AS has_security,
                COALESCE(p.is_furnished, 0) AS is_furnished,
                COALESCE(ami.average_price_per_m2, 0) AS area_avg_price_per_m2,
                COALESCE(ami.demand_score, 0)          AS area_demand_score,
                COALESCE(ami.listing_count, 0)         AS area_listing_count,
                COALESCE(ami.price_growth_30d, 0)      AS area_price_growth_30d
            FROM properties p
            LEFT JOIN area_market_insights ami ON ami.area_id = p.area_id
            WHERE
                p.status    = 'active'
                AND p.price > 0
                AND p.area_size > 0
                AND p.price < 50000000
            ORDER BY p.created_at DESC
        """)
        try:
            df = pd.read_sql(query, self.engine)
            df = self._parse_location(df)
            logger.info(f"Loaded {len(df)} properties for training")
            return df
        except Exception as e:
            logger.error(f"load_training_data failed: {e}")
            return None

    # ── Clustering data ───────────────────────────────────────────────────────

    def load_properties_for_clustering(
        self, branch_id: Optional[int] = None
    ) -> Optional[pd.DataFrame]:
        where = "AND p.branch_id = :branch_id" if branch_id else ""
        query = text(f"""
            SELECT
                p.id,
                p.location,
                p.price,
                p.area_size,
                (p.price / NULLIF(p.area_size, 0)) AS price_per_m2,
                p.property_type,
                p.area_id,
                p.branch_id
            FROM properties p
            WHERE p.status = 'active' AND p.price > 0 AND p.area_size > 0
            {where}
        """)
        try:
            df = pd.read_sql(query, self.engine, params={"branch_id": branch_id} if branch_id else {})
            df = self._parse_location(df)
            df = df.dropna(subset=["latitude", "longitude", "price_per_m2"])
            p99 = df["price_per_m2"].quantile(0.99)
            df  = df[df["price_per_m2"] <= p99]
            logger.info(f"Loaded {len(df)} properties for clustering")
            return df
        except Exception as e:
            logger.error(f"load_properties_for_clustering failed: {e}")
            return None

    # ── Heatmap data ──────────────────────────────────────────────────────────

    def load_properties_for_heatmap(
        self, branch_id: Optional[int] = None
    ) -> Optional[pd.DataFrame]:
        where = "AND p.branch_id = :branch_id" if branch_id else ""
        query = text(f"""
            SELECT
                p.id,
                p.location,
                p.price,
                p.area_size,
                (p.price / NULLIF(p.area_size, 0)) AS price_per_m2,
                COALESCE(ami.demand_score, 50)      AS demand_score,
                p.branch_id
            FROM properties p
            LEFT JOIN area_market_insights ami ON ami.area_id = p.area_id
            WHERE p.status = 'active' AND p.price > 0
            {where}
        """)
        try:
            df = pd.read_sql(query, self.engine, params={"branch_id": branch_id} if branch_id else {})
            df = self._parse_location(df)
            df = df.dropna(subset=["latitude", "longitude"])
            return df
        except Exception as e:
            logger.error(f"load_properties_for_heatmap failed: {e}")
            return None

    # ── Area activity ─────────────────────────────────────────────────────────

    def load_area_activity(
        self, area_ids: Optional[List[int]] = None
    ) -> Optional[pd.DataFrame]:
        where = f"AND p.area_id IN ({','.join(map(str, area_ids))})" if area_ids else ""
        query = text(f"""
            SELECT
                p.area_id,
                COUNT(*)                               AS listing_count,
                AVG(p.price)                           AS avg_price,
                AVG(p.price / NULLIF(p.area_size, 0)) AS avg_price_per_m2,
                AVG(DATEDIFF(NOW(), p.created_at))     AS avg_age_days,
                SUM(CASE WHEN p.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                         THEN 1 ELSE 0 END)            AS new_listings_30d,
                SUM(CASE WHEN p.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                         THEN 1 ELSE 0 END)            AS new_listings_7d
            FROM properties p
            WHERE p.status = 'active' {where}
            GROUP BY p.area_id
        """)
        try:
            return pd.read_sql(query, self.engine)
        except Exception as e:
            logger.error(f"load_area_activity failed: {e}")
            return None

    # ── Helper ────────────────────────────────────────────────────────────────

    def _parse_location(self, df: pd.DataFrame) -> pd.DataFrame:
        """Parse location JSON column → latitude / longitude float columns."""
        if "location" not in df.columns:
            return df

        lats, lngs = [], []
        for loc in df["location"]:
            try:
                if isinstance(loc, str):
                    loc = json.loads(loc)
                lat = loc.get("lat") or loc.get("latitude")
                lng = loc.get("lng") or loc.get("longitude")
                lats.append(float(lat) if lat is not None else None)
                lngs.append(float(lng) if lng is not None else None)
            except Exception:
                lats.append(None)
                lngs.append(None)

        df["latitude"]  = lats
        df["longitude"] = lngs
        df.drop(columns=["location"], inplace=True, errors="ignore")
        return df
