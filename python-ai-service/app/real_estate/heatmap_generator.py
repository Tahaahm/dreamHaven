"""
heatmap_generator.py
Divides the map into a grid and computes intensity weights per cell.
Three types: price | demand | density
"""

import logging
import numpy as np
import pandas as pd
from typing import Dict, List

logger = logging.getLogger(__name__)


class HeatmapGeneratorModel:

    def generate(self, df: pd.DataFrame, heatmap_type: str = "all",
                 resolution: float = 0.005) -> Dict:

        logger.info(f"Generating heatmap: type={heatmap_type}, resolution={resolution}, rows={len(df)}")

        types      = ["price", "demand", "density"] if heatmap_type == "all" else [heatmap_type]
        all_tiles  = []

        for t in types:
            all_tiles.extend(self._compute(df, t, resolution))

        return {
            "type":       heatmap_type,
            "resolution": resolution,
            "count":      len(all_tiles),
            "tiles":      all_tiles,
        }

    def _compute(self, df: pd.DataFrame, heatmap_type: str,
                 resolution: float) -> List[Dict]:

        df = df.copy()
        df["cell_lat"] = (df["latitude"]  / resolution).apply(np.floor) * resolution
        df["cell_lng"] = (df["longitude"] / resolution).apply(np.floor) * resolution

        id_col = "id" if "id" in df.columns else "latitude"

        if heatmap_type == "price":
            agg = df.groupby(["cell_lat", "cell_lng"]).agg(
                raw_value       = ("price_per_m2",   "mean"),
                property_count  = (id_col,           "count"),
                avg_price       = ("price",           "mean") if "price" in df.columns else ("price_per_m2", "mean"),
                avg_price_per_m2= ("price_per_m2",   "mean"),
            ).reset_index()

        elif heatmap_type == "demand":
            if "demand_score" not in df.columns:
                df["demand_score"] = 50.0
            agg = df.groupby(["cell_lat", "cell_lng"]).agg(
                raw_value      = ("demand_score", "mean"),
                property_count = (id_col,         "count"),
            ).reset_index()
            agg["avg_price"]         = 0
            agg["avg_price_per_m2"]  = 0

        else:  # density
            agg = df.groupby(["cell_lat", "cell_lng"]).agg(
                property_count = ("latitude", "count"),
            ).reset_index()
            agg["raw_value"]        = agg["property_count"].astype(float)
            agg["avg_price"]        = 0
            agg["avg_price_per_m2"] = 0

        if len(agg) == 0:
            return []

        # Normalize 0–1
        mn, mx = agg["raw_value"].min(), agg["raw_value"].max()
        agg["weight"] = (agg["raw_value"] - mn) / (mx - mn) if mx > mn else 0.5

        tiles = []
        for _, row in agg.iterrows():
            tiles.append({
                "latitude":         round(float(row["cell_lat"]) + resolution / 2, 7),
                "longitude":        round(float(row["cell_lng"]) + resolution / 2, 7),
                "cell_min_lat":     round(float(row["cell_lat"]),                  7),
                "cell_max_lat":     round(float(row["cell_lat"]) + resolution,     7),
                "cell_min_lng":     round(float(row["cell_lng"]),                  7),
                "cell_max_lng":     round(float(row["cell_lng"]) + resolution,     7),
                "type":             heatmap_type,
                "weight":           round(float(row["weight"]), 4),
                "raw_value":        round(float(row["raw_value"]), 2),
                "property_count":   int(row.get("property_count", 0)),
                "avg_price":        round(float(row.get("avg_price", 0)), 2),
                "avg_price_per_m2": round(float(row.get("avg_price_per_m2", 0)), 2),
            })

        logger.info(f"Heatmap {heatmap_type}: {len(tiles)} tiles")
        return tiles
