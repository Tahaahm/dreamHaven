"""
db_loader.py
Loads data from the Dream Mulk MySQL database for ML training and inference.
Reads ONLY from existing properties, areas, branches tables.
Never writes to those tables.

Schema notes (actual DB):
  branches : id, city_name_en, city_name_ar, city_name_ku, latitude, longitude
  areas    : id, branch_id, area_name_en, area_name_ar, area_name_ku, latitude, longitude
  properties: id, owner_id, owner_type, name(JSON), price(JSON), area(decimal),
              furnished, listing_type, rooms(JSON), type(JSON), locations(JSON),
              address_details(JSON), floor_number, year_built, status, views,
              favorites_count, rating, created_at
"""

import os
import json
import logging
import pandas as pd
import sqlalchemy
from sqlalchemy import text
from typing import Optional, List

logger = logging.getLogger(__name__)


def _parse_json_field(val, *keys):
    """Extract a value from a JSON string or dict. Tries multiple keys."""
    try:
        if isinstance(val, str):
            val = json.loads(val)
        if isinstance(val, dict):
            for k in keys:
                if k in val:
                    return val[k]
            # Try 'en' locale fallback
            if "en" in val:
                return val["en"]
        if isinstance(val, (int, float)):
            return val
    except Exception:
        pass
    return None


def _extract_price(price_val):
    """Extract numeric price from JSON or plain value."""
    try:
        if price_val is None:
            return None
        if isinstance(price_val, (int, float)):
            return float(price_val)
        if isinstance(price_val, str):
            parsed = json.loads(price_val)
            if isinstance(parsed, (int, float)):
                return float(parsed)
            if isinstance(parsed, dict):
                # Try common keys
                for k in ("amount", "value", "price", "en"):
                    if k in parsed:
                        return float(parsed[k])
                # Return first numeric value
                for v in parsed.values():
                    try:
                        return float(v)
                    except Exception:
                        pass
    except Exception:
        pass
    return None


def _extract_rooms(rooms_val):
    """Extract bedroom count from rooms JSON."""
    try:
        if rooms_val is None:
            return 0
        if isinstance(rooms_val, (int, float)):
            return int(rooms_val)
        if isinstance(rooms_val, str):
            parsed = json.loads(rooms_val)
            if isinstance(parsed, (int, float)):
                return int(parsed)
            if isinstance(parsed, dict):
                for k in ("bedrooms", "bedroom", "rooms", "count"):
                    if k in parsed:
                        return int(parsed[k])
                # Return first int value
                for v in parsed.values():
                    try:
                        return int(v)
                    except Exception:
                        pass
    except Exception:
        pass
    return 0


def _extract_type(type_val):
    """Extract property type string from JSON."""
    try:
        if type_val is None:
            return "unknown"
        if isinstance(type_val, str):
            parsed = json.loads(type_val)
            if isinstance(parsed, str):
                return parsed
            if isinstance(parsed, dict):
                return parsed.get("en") or parsed.get("type") or "unknown"
        return str(type_val)
    except Exception:
        return str(type_val) if type_val else "unknown"


def _extract_location(locations_val):
    """Extract lat/lng from locations JSON."""
    try:
        if locations_val is None:
            return None, None
        if isinstance(locations_val, str):
            loc = json.loads(locations_val)
        else:
            loc = locations_val

        if isinstance(loc, dict):
            lat = loc.get("lat") or loc.get("latitude")
            lng = loc.get("lng") or loc.get("longitude") or loc.get("long")
            return (float(lat) if lat is not None else None,
                    float(lng) if lng is not None else None)

        if isinstance(loc, list) and len(loc) > 0:
            first = loc[0]
            if isinstance(first, dict):
                lat = first.get("lat") or first.get("latitude")
                lng = first.get("lng") or first.get("longitude")
                return (float(lat) if lat is not None else None,
                        float(lng) if lng is not None else None)
    except Exception:
        pass
    return None, None


def _extract_city_from_address(address_val):
    """Extract city name from address_details JSON."""
    try:
        if isinstance(address_val, str):
            addr = json.loads(address_val)
        else:
            addr = address_val
        if isinstance(addr, dict):
            city = addr.get("city") or addr.get("branch")
            if isinstance(city, dict):
                return city.get("en") or city.get("ar") or "unknown"
            return str(city) if city else "unknown"
    except Exception:
        pass
    return "unknown"


class DatabaseLoader:

    def __init__(self):
        self.engine = self._create_engine()

    def _create_engine(self) -> sqlalchemy.Engine:
        url = (
            f"mysql+pymysql://{os.getenv('DB_USERNAME', 'root')}:"
            f"{os.getenv('DB_PASSWORD', '')}@"
            f"{os.getenv('DB_HOST', '127.0.0.1')}:"
            f"{os.getenv('DB_PORT', '3306')}/"
            f"{os.getenv('DB_DATABASE', 'real_estate')}"
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
        """All approved/available properties with features for XGBoost training."""
        query = text("""
            SELECT
                p.id          AS property_id,
                p.price,
                p.area        AS area_size,
                p.rooms,
                p.type        AS property_type,
                p.listing_type,
                p.furnished   AS is_furnished,
                COALESCE(p.floor_number, 0)  AS floor,
                COALESCE(p.year_built, 2000) AS year_built,
                p.locations,
                p.address_details,
                p.views,
                p.favorites_count,
                p.rating,
                p.created_at
            FROM properties p
            WHERE
                p.status IN ('approved', 'available')
                AND p.is_active = 1
                AND p.published = 1
                AND p.price IS NOT NULL
                AND p.area  > 0
            ORDER BY p.created_at DESC
        """)
        try:
            df = pd.read_sql(query, self.engine)
            df = self._process_properties_df(df)
            # Filter out bad prices
            df = df[df["price_numeric"] > 0]
            df = df[df["price_numeric"] < 50_000_000]
            logger.info(f"Loaded {len(df)} properties for training")
            return df
        except Exception as e:
            logger.error(f"load_training_data failed: {e}")
            return None

    # ── Clustering data ───────────────────────────────────────────────────────

    def load_properties_for_clustering(
        self, branch_id: Optional[int] = None
    ) -> Optional[pd.DataFrame]:
        query = text("""
            SELECT
                p.id,
                p.locations,
                p.price,
                p.area AS area_size,
                p.address_details
            FROM properties p
            WHERE
                p.status IN ('approved', 'available')
                AND p.is_active = 1
                AND p.published = 1
                AND p.price IS NOT NULL
                AND p.area > 0
        """)
        try:
            df = pd.read_sql(query, self.engine)
            df = self._process_properties_df(df)
            df["price_per_m2"] = df["price_numeric"] / df["area_size"].replace(0, None)
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
        query = text("""
            SELECT
                p.id,
                p.locations,
                p.price,
                p.area AS area_size
            FROM properties p
            WHERE
                p.status IN ('approved', 'available')
                AND p.is_active = 1
                AND p.published = 1
                AND p.price IS NOT NULL
        """)
        try:
            df = pd.read_sql(query, self.engine)
            df = self._process_properties_df(df)
            df["price_per_m2"] = df["price_numeric"] / df["area_size"].replace(0, None)
            df = df.dropna(subset=["latitude", "longitude"])
            return df
        except Exception as e:
            logger.error(f"load_properties_for_heatmap failed: {e}")
            return None

    # ── Area activity ─────────────────────────────────────────────────────────

    def load_area_activity(
        self, area_ids: Optional[List[int]] = None
    ) -> Optional[pd.DataFrame]:
        """
        Since properties don't have area_id directly, we aggregate by
        city from address_details and join with areas.
        Returns per-city stats.
        """
        query = text("""
            SELECT
                p.address_details,
                p.price,
                p.area AS area_size,
                p.created_at,
                p.views,
                p.favorites_count
            FROM properties p
            WHERE
                p.status IN ('approved', 'available')
                AND p.is_active = 1
                AND p.published = 1
        """)
        try:
            df = pd.read_sql(query, self.engine)
            if df.empty:
                return pd.DataFrame()
            df["city"] = df["address_details"].apply(_extract_city_from_address)
            df["price_numeric"] = df["price"].apply(_extract_price)
            df["price_per_m2"]  = df["price_numeric"] / df["area_size"].replace(0, None)
            return df
        except Exception as e:
            logger.error(f"load_area_activity failed: {e}")
            return None

    # ── Areas & Branches ──────────────────────────────────────────────────────

    def load_areas(self) -> Optional[pd.DataFrame]:
        query = text("""
            SELECT
                a.id,
                a.branch_id,
                a.area_name_en AS name,
                a.area_name_ar AS name_ar,
                a.area_name_ku AS name_ku,
                a.latitude,
                a.longitude,
                b.city_name_en AS city
            FROM areas a
            JOIN branches b ON b.id = a.branch_id
            WHERE a.is_active = 1 AND a.deleted_at IS NULL
        """)
        try:
            return pd.read_sql(query, self.engine)
        except Exception as e:
            logger.error(f"load_areas failed: {e}")
            return None

    def load_branches(self) -> Optional[pd.DataFrame]:
        query = text("""
            SELECT id, city_name_en AS name, city_name_ar, city_name_ku,
                   latitude, longitude
            FROM branches
            WHERE is_active = 1 AND deleted_at IS NULL
        """)
        try:
            return pd.read_sql(query, self.engine)
        except Exception as e:
            logger.error(f"load_branches failed: {e}")
            return None

    # ── Helper ────────────────────────────────────────────────────────────────

    def _process_properties_df(self, df: pd.DataFrame) -> pd.DataFrame:
        """Parse all JSON columns into usable numeric/string fields."""
        if df.empty:
            return df

        df["price_numeric"]   = df["price"].apply(_extract_price)
        df["rooms_count"]     = df["rooms"].apply(_extract_rooms) if "rooms" in df.columns else 0
        df["property_type_s"] = df["type"].apply(_extract_type)   if "type"  in df.columns else "unknown"

        # Parse locations → lat/lng
        lats, lngs = [], []
        loc_col = "locations" if "locations" in df.columns else None
        for val in (df[loc_col] if loc_col else [None] * len(df)):
            lat, lng = _extract_location(val)
            lats.append(lat)
            lngs.append(lng)
        df["latitude"]  = lats
        df["longitude"] = lngs

        # Parse city from address_details
        if "address_details" in df.columns:
            df["city"] = df["address_details"].apply(_extract_city_from_address)

        return df
