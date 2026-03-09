"""
price_predictor.py
XGBoost regression model — predicts fair market price for a property.
"""

import os
import pickle
import logging
import numpy as np
import pandas as pd
from datetime import datetime
from typing import Dict, Any, Optional, List

from sklearn.model_selection import train_test_split
from sklearn.preprocessing import LabelEncoder
from sklearn.metrics import mean_squared_error, mean_absolute_error, r2_score
import xgboost as xgb

logger = logging.getLogger(__name__)

MODEL_DIR    = os.getenv("MODEL_DIR", "/opt/dreammulk/models")
MODEL_FILE   = os.path.join(MODEL_DIR, "price_predictor.pkl")
ENCODER_FILE = os.path.join(MODEL_DIR, "price_encoder.pkl")
VERSION_FILE = os.path.join(MODEL_DIR, "price_version.txt")

NUMERIC_FEATURES = [
    "area_size", "bedrooms", "bathrooms", "floor", "year_built",
    "latitude", "longitude",
    "area_avg_price_per_m2", "area_demand_score",
    "area_listing_count", "area_price_growth_30d",
    "nearby_poi_count",
    "has_parking", "has_elevator", "has_balcony",
    "has_garden", "has_pool", "has_security", "is_furnished",
]
CATEGORICAL_FEATURES = ["property_type", "listing_type"]
TARGET = "price"


class PricePredictorModel:

    def __init__(self):
        self.model:    Optional[xgb.XGBRegressor] = None
        self.encoders: Dict[str, LabelEncoder]    = {}
        self.version:  str = "v1.0"
        self._loaded:  bool = False
        os.makedirs(MODEL_DIR, exist_ok=True)

    def is_loaded(self) -> bool:
        return self._loaded and self.model is not None

    def load_if_exists(self) -> bool:
        if not os.path.exists(MODEL_FILE):
            logger.info("No saved price model — needs training first")
            return False
        try:
            with open(MODEL_FILE,   "rb") as f: self.model    = pickle.load(f)
            with open(ENCODER_FILE, "rb") as f: self.encoders = pickle.load(f)
            if os.path.exists(VERSION_FILE):
                with open(VERSION_FILE) as f: self.version = f.read().strip()
            self._loaded = True
            logger.info(f"Price model loaded: {self.version}")
            return True
        except Exception as e:
            logger.error(f"Failed to load price model: {e}")
            return False

    # ── Training ──────────────────────────────────────────────────────────────

    def train(self, df: pd.DataFrame, hyperparams: Dict[str, Any] = {}) -> Dict:
        logger.info(f"Training price predictor on {len(df)} samples")
        started_at = datetime.now()

        df = self._engineer_features(df)
        df = self._clean_data(df)

        if len(df) < 50:
            raise ValueError(f"Not enough data: {len(df)} samples after cleaning (need 50+)")

        # Encode categoricals
        encoders = {}
        for col in CATEGORICAL_FEATURES:
            if col in df.columns:
                le = LabelEncoder()
                df[col + "_enc"] = le.fit_transform(df[col].fillna("unknown").astype(str))
                encoders[col] = le

        # Feature matrix
        feature_cols = [c for c in NUMERIC_FEATURES if c in df.columns]
        feature_cols += [c + "_enc" for c in CATEGORICAL_FEATURES if c + "_enc" in df.columns]

        X = df[feature_cols].fillna(0)
        y = df[TARGET]

        X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

        params = {
            "n_estimators":     hyperparams.get("n_estimators",    500),
            "max_depth":        hyperparams.get("max_depth",          6),
            "learning_rate":    hyperparams.get("learning_rate",   0.05),
            "subsample":        hyperparams.get("subsample",        0.8),
            "colsample_bytree": hyperparams.get("colsample_bytree", 0.8),
            "min_child_weight": hyperparams.get("min_child_weight",   3),
            "reg_alpha":        hyperparams.get("reg_alpha",        0.1),
            "reg_lambda":       hyperparams.get("reg_lambda",       1.0),
            "random_state": 42,
            "n_jobs": -1,
            "verbosity": 0,
        }

        model = xgb.XGBRegressor(**params)
        model.fit(X_train, y_train, eval_set=[(X_test, y_test)], verbose=False)

        y_pred = model.predict(X_test)
        rmse = float(np.sqrt(mean_squared_error(y_test, y_pred)))
        mae  = float(mean_absolute_error(y_test, y_pred))
        r2   = float(r2_score(y_test, y_pred))
        mape = float(np.mean(np.abs((y_test - y_pred) / np.maximum(y_test, 1))) * 100)

        logger.info(f"Training done — RMSE={rmse:,.0f}  R²={r2:.4f}  MAPE={mape:.2f}%")

        # Bump version
        parts = self.version.replace("v", "").split(".")
        new_version = f"v{parts[0]}.{int(parts[1]) + 1}"

        self.model    = model
        self.encoders = encoders
        self.version  = new_version
        self._loaded  = True
        self._save()

        return {
            "model_name":            "price_predictor",
            "version":               new_version,
            "algorithm":             "xgboost",
            "training_samples":      len(X_train),
            "feature_names":         feature_cols,
            "metrics": {
                "rmse": rmse, "mae": mae, "r2_score": r2, "mape": mape,
            },
            "hyperparameters":       params,
            "model_file_path":       MODEL_FILE,
            "model_file_size_bytes": os.path.getsize(MODEL_FILE) if os.path.exists(MODEL_FILE) else 0,
            "training_started_at":   started_at.isoformat(),
            "training_completed_at": datetime.now().isoformat(),
        }

    # ── Prediction ────────────────────────────────────────────────────────────

    def predict(self, features: Dict[str, Any]) -> Dict:
        return self._predict_dataframe(pd.DataFrame([features]))[0]

    def predict_batch(self, properties: Dict[int, Dict[str, Any]]) -> Dict[int, Dict]:
        if not properties:
            return {}
        ids     = list(properties.keys())
        df      = pd.DataFrame(list(properties.values()))
        results = self._predict_dataframe(df)
        return {pid: res for pid, res in zip(ids, results)}

    def _predict_dataframe(self, df: pd.DataFrame) -> List[Dict]:
        df = self._engineer_features(df)

        for col in CATEGORICAL_FEATURES:
            if col in df.columns and col in self.encoders:
                le = self.encoders[col]
                df[col + "_enc"] = df[col].fillna("unknown").astype(str).apply(
                    lambda x: int(le.transform([x])[0]) if x in le.classes_ else 0
                )

        feature_cols = [c for c in NUMERIC_FEATURES if c in df.columns]
        feature_cols += [c + "_enc" for c in CATEGORICAL_FEATURES if c + "_enc" in df.columns]

        X         = df[feature_cols].fillna(0)
        raw_preds = self.model.predict(X)

        results = []
        for i, pred_price in enumerate(raw_preds):
            pred_price = max(float(pred_price), 0)
            area       = float(df.iloc[i].get("area_size", 0) or 0)
            pred_m2    = pred_price / area if area > 0 else 0

            actual     = float(df.iloc[i].get("price", 0) or 0)
            if actual > 0:
                diff          = ((actual - pred_price) / pred_price) * 100
                over_pct      = max(diff, 0)
                under_pct     = max(-diff, 0)
            else:
                over_pct = under_pct = 0

            listing_count = float(df.iloc[i].get("area_listing_count", 0) or 0)
            confidence    = min(0.5 + (listing_count / 200) * 0.5, 0.97)
            margin        = 0.08 + (1 - confidence) * 0.14

            results.append({
                "predicted_price":        round(pred_price, 2),
                "predicted_price_per_m2": round(pred_m2, 2),
                "predicted_price_low":    round(pred_price * (1 - margin), 2),
                "predicted_price_high":   round(pred_price * (1 + margin), 2),
                "confidence_score":       round(confidence, 4),
                "overprice_percent":      round(over_pct, 2),
                "underprice_percent":     round(under_pct, 2),
                "verdict":                self._verdict(actual, pred_price),
                "algorithm":              "xgboost",
                "model_version":          self.version,
                "comparable_ids":         [],
            })
        return results

    # ── Helpers ───────────────────────────────────────────────────────────────

    def _engineer_features(self, df: pd.DataFrame) -> pd.DataFrame:
        df = df.copy()
        if "year_built" in df.columns:
            df["property_age"] = datetime.now().year - df["year_built"].fillna(datetime.now().year)
        return df

    def _clean_data(self, df: pd.DataFrame) -> pd.DataFrame:
        df = df.dropna(subset=[TARGET, "area_size"])
        df = df[(df["area_size"] > 0) & (df[TARGET] > 0)]
        q5, q95 = df[TARGET].quantile(0.05), df[TARGET].quantile(0.95)
        return df[(df[TARGET] >= q5) & (df[TARGET] <= q95 * 3)].reset_index(drop=True)

    def _verdict(self, actual: float, predicted: float) -> str:
        if actual <= 0 or predicted <= 0: return "fair_value"
        diff = ((actual - predicted) / predicted) * 100
        if diff <= -20: return "great_deal"
        if diff <= -10: return "underpriced"
        if diff >=  15: return "overpriced"
        return "fair_value"

    def _save(self):
        with open(MODEL_FILE,   "wb") as f: pickle.dump(self.model,    f)
        with open(ENCODER_FILE, "wb") as f: pickle.dump(self.encoders, f)
        with open(VERSION_FILE, "w")  as f: f.write(self.version)
        logger.info(f"Model saved → {MODEL_FILE}")
