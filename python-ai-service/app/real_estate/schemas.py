from pydantic import BaseModel, Field
from typing import Optional, Dict, Any, List


class PredictRequest(BaseModel):
    features: Dict[str, Any]


class PredictBatchRequest(BaseModel):
    properties: Dict[int, Dict[str, Any]]


class ClusterZonesRequest(BaseModel):
    n_clusters: int = Field(default=4, ge=2, le=10)
    algorithm:  str = Field(default="kmeans")
    branch_id:  Optional[int] = None


class HeatmapRequest(BaseModel):
    type:       str   = Field(default="all")  # price | demand | density | all
    resolution: float = Field(default=0.005, ge=0.001, le=0.05)
    branch_id:  Optional[int] = None


class TrainRequest(BaseModel):
    model_type:      str = Field(default="price_predictor")
    hyperparameters: Optional[Dict[str, Any]] = None


class AreaScoresRequest(BaseModel):
    area_ids: Optional[List[int]] = None
