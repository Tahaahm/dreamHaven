"""
investment_scorer.py
Computes demand and liquidity scores per area from activity metrics.
"""

import logging
import pandas as pd
from typing import Dict, List, Optional

logger = logging.getLogger(__name__)


class InvestmentScorerModel:

    def compute_area_scores(self, df: pd.DataFrame) -> Dict[int, Dict]:
        """
        df: from DatabaseLoader.load_area_activity()
        returns: { area_id: { demand_score, liquidity_score } }
        """
        if df is None or len(df) == 0:
            return {}

        max_new   = df["new_listings_30d"].max() or 1
        max_count = df["listing_count"].max()     or 1
        results   = {}

        for _, row in df.iterrows():
            area_id = int(row["area_id"])

            velocity      = (row["new_listings_30d"] / max_new)       * 100
            volume        = min((row["listing_count"] / max_count) * 100, 100)
            demand_score  = round(velocity * 0.6 + volume * 0.4, 2)

            avg_age         = float(row.get("avg_age_days", 60) or 60)
            liquidity_score = round(max(0.0, 100.0 - (avg_age / 180.0) * 100.0), 2)

            results[area_id] = {
                "demand_score":    demand_score,
                "liquidity_score": liquidity_score,
            }

        return results
