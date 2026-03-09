"""
zone_clusterer.py
K-means geospatial clustering → GeoJSON price zone polygons.
"""

import logging
import numpy as np
import pandas as pd
from datetime import datetime
from typing import Dict, List

logger = logging.getLogger(__name__)

TIER_CONFIG = [
    ("affordable", 0.00, 0.25, "#22C55E"),
    ("medium",     0.25, 0.60, "#EAB308"),
    ("expensive",  0.60, 0.85, "#F97316"),
    ("luxury",     0.85, 1.00, "#EF4444"),
]


class ZoneClustererModel:

    def cluster(self, df: pd.DataFrame, n_clusters: int = 4,
                algorithm: str = "kmeans") -> Dict:

        from sklearn.cluster import KMeans
        from sklearn.preprocessing import StandardScaler
        from scipy.spatial import ConvexHull

        logger.info(f"Clustering {len(df)} properties into {n_clusters} zones")

        features        = df[["latitude", "longitude", "price_per_m2"]].fillna(df[["latitude", "longitude", "price_per_m2"]].median())
        scaler          = StandardScaler()
        features_scaled = scaler.fit_transform(features)

        km = KMeans(n_clusters=n_clusters, random_state=42, n_init=10)
        df = df.copy()
        df["cluster_id"] = km.fit_predict(features_scaled)

        global_min   = df["price_per_m2"].min()
        global_range = max(df["price_per_m2"].max() - global_min, 1)

        zones   = []
        version = int(datetime.now().timestamp())

        for cid in sorted(df["cluster_id"].unique()):
            cdf = df[df["cluster_id"] == cid]
            if len(cdf) < 3:
                continue

            avg_m2      = cdf["price_per_m2"].mean()
            percentile  = (avg_m2 - global_min) / global_range
            tier, color = self._tier(percentile)

            # ConvexHull polygon
            try:
                pts  = cdf[["longitude", "latitude"]].values
                hull = ConvexHull(pts)
                ring = pts[hull.vertices].tolist()
                ring.append(ring[0])
                geojson = {"type": "Feature", "geometry": {"type": "Polygon", "coordinates": [ring]}, "properties": {}}
            except Exception:
                geojson = self._bbox_polygon(cdf)

            zones.append({
                "cluster_id":     int(cid),
                "tier":           tier,
                "color_hex":      color,
                "geojson_polygon": geojson,
                "centroid_lat":   round(float(cdf["latitude"].mean()),  7),
                "centroid_lng":   round(float(cdf["longitude"].mean()), 7),
                "bbox": {
                    "min_lat": round(float(cdf["latitude"].min()),  7),
                    "max_lat": round(float(cdf["latitude"].max()),  7),
                    "min_lng": round(float(cdf["longitude"].min()), 7),
                    "max_lng": round(float(cdf["longitude"].max()), 7),
                },
                "stats": {
                    "avg_price_per_m2": round(float(avg_m2), 2),
                    "min_price_per_m2": round(float(cdf["price_per_m2"].min()), 2),
                    "max_price_per_m2": round(float(cdf["price_per_m2"].max()), 2),
                    "avg_total_price":  round(float(cdf["price"].mean()), 2) if "price" in cdf.columns else 0,
                    "property_count":   len(cdf),
                    "demand_score":     50.0,
                    "investment_score": 50.0,
                },
            })

        # Silhouette score
        silhouette = 0.0
        try:
            from sklearn.metrics import silhouette_score
            if len(set(df["cluster_id"])) > 1:
                silhouette = float(silhouette_score(features_scaled, df["cluster_id"], sample_size=1000))
        except Exception:
            pass

        logger.info(f"Clustering done: {len(zones)} zones, silhouette={silhouette:.4f}")
        return {
            "version":          version,
            "algorithm":        algorithm,
            "n_clusters":       len(zones),
            "silhouette_score": round(silhouette, 4),
            "zones":            zones,
        }

    def _tier(self, percentile: float):
        for tier, lo, hi, color in TIER_CONFIG:
            if lo <= percentile < hi:
                return tier, color
        return "luxury", "#EF4444"

    def _bbox_polygon(self, df: pd.DataFrame) -> Dict:
        min_lat, max_lat = df["latitude"].min(),  df["latitude"].max()
        min_lng, max_lng = df["longitude"].min(), df["longitude"].max()
        ring = [
            [min_lng, min_lat], [max_lng, min_lat],
            [max_lng, max_lat], [min_lng, max_lat],
            [min_lng, min_lat],
        ]
        return {"type": "Feature", "geometry": {"type": "Polygon", "coordinates": [ring]}, "properties": {}}
