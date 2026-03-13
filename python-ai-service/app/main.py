"""
FastAPI Application for Video Frame Extraction Service + Real Estate AI
======================================================
FIXES:
  1. Double-read bug eliminated — bytes read once, written directly to disk
  2. CLIP model removed — replaced with fast OpenCV scoring
  3. Memory freed immediately after disk write
  4. Proper error messages returned to client (no silent infinite loading)
  5. BASE64 BYPASS + MOBILE OPTIMIZED: Images are compressed and sent
     as Base64 strings to avoid 404 proxy errors and mobile memory crashes.

ADDED:
  6. Real Estate AI routes — price prediction, zone clustering,
     heatmap generation, area scoring (all under /re/ prefix)
"""

from fastapi import FastAPI, File, UploadFile, HTTPException, Form
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse
from fastapi.staticfiles import StaticFiles
from pathlib import Path
from typing import Optional
import aiofiles
import hashlib
import time
import os
import psutil
import base64

from app.config import (
    UPLOAD_DIR,
    OUTPUT_DIR,
    MAX_VIDEO_SIZE_MB,
    SUPPORTED_VIDEO_FORMATS,
    DEFAULT_NUM_FRAMES,
    MIN_FRAMES,
    MAX_FRAMES,
    ALLOWED_ORIGINS,
    DEBUG
)
from app.frame_extractor import get_extractor

# ── Real Estate AI imports ────────────────────────────────────────────────────
from app.real_estate.db_loader         import DatabaseLoader
from app.real_estate.schemas           import (
    PredictRequest, PredictBatchRequest, ClusterZonesRequest,
    HeatmapRequest, TrainRequest, AreaScoresRequest,
)
from app.real_estate.price_predictor   import PricePredictorModel
from app.real_estate.zone_clusterer    import ZoneClustererModel
from app.real_estate.heatmap_generator import HeatmapGeneratorModel
from app.real_estate.investment_scorer import InvestmentScorerModel

# ─────────────────────────────────────────────────────────────────────────────

# Initialize FastAPI app
app = FastAPI(
    title="Dream Mulk AI Service",
    description="Video frame extraction + Real estate intelligence",
    version="2.0.0",
    debug=DEBUG
)

# CORS Middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=ALLOWED_ORIGINS,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Mount static files (kept for debugging, though frontend now uses base64)
app.mount("/outputs", StaticFiles(directory=str(OUTPUT_DIR)), name="outputs")

# ── Global instances ──────────────────────────────────────────────────────────
# Video
extractor = None

# Real Estate AI
_db_loader        = None
_price_model      = None
_zone_model       = None
_heatmap_model    = None
_investment_model = None


@app.on_event("startup")
async def startup_event():
    """Initialize all services on startup."""
    global extractor
    global _db_loader, _price_model, _zone_model, _heatmap_model, _investment_model

    print("\n" + "=" * 70)
    print("🚀 Starting Dream Mulk AI Service v2.0")
    print("=" * 70)

    # Ensure directories exist
    UPLOAD_DIR.mkdir(parents=True, exist_ok=True)
    OUTPUT_DIR.mkdir(parents=True, exist_ok=True)

    # ── Video frame extractor ─────────────────────────────────────────────────
    print("\n📦 Initializing frame extractor (OpenCV-based, no ML model)...")
    extractor = get_extractor()
    print("✅ Frame extractor ready — fast OpenCV scoring active!")

    # ── Real Estate AI models ─────────────────────────────────────────────────
    print("\n🏠 Initializing Real Estate AI models...")
    _db_loader        = DatabaseLoader()
    _price_model      = PricePredictorModel()
    _zone_model       = ZoneClustererModel()
    _heatmap_model    = HeatmapGeneratorModel()
    _investment_model = InvestmentScorerModel()

    # Load saved price model from disk if it exists
    _price_model.load_if_exists()

    print("✅ Real Estate AI ready!")
    print("=" * 70)


@app.on_event("shutdown")
async def shutdown_event():
    """Cleanup on shutdown."""
    print("\n🛑 Shutting down service...")
    print("✅ Shutdown complete")


# ─────────────────────────────────────────────────────────────────────────────
# GENERAL ROUTES
# ─────────────────────────────────────────────────────────────────────────────

@app.get("/")
async def root():
    return {
        "service": "Dream Mulk AI Service",
        "version": "2.0.0",
        "status": "operational",
        "endpoints": {
            "health":          "/health",
            "extract":         "/extract-frames",
            "stats":           "/stats",
            "cleanup":         "/cleanup",
            "re_health":       "/re/health",
            "re_train":        "/re/train",
            "re_predict":      "/re/predict",
            "re_predict_batch":"/re/predict/batch",
            "re_zones":        "/re/cluster-zones",
            "re_heatmap":      "/re/heatmap",
            "re_area_scores":  "/re/area-scores",
        }
    }


@app.get("/health")
async def health_check():
    return {
        "status":          "healthy",
        "service":         "dream-mulk-ai",
        "extractor_ready": extractor is not None,
        "scoring_mode":    "opencv-fast",
        "re_model_loaded": _price_model.is_loaded() if _price_model else False,
        "re_db_connected": _db_loader.test_connection() if _db_loader else False,
    }


@app.get("/stats")
async def system_stats():
    memory = psutil.virtual_memory()
    cpu    = psutil.cpu_percent(interval=1)
    return {
        "cpu_percent":          cpu,
        "memory_total_gb":      round(memory.total / (1024 ** 3), 2),
        "memory_used_gb":       round(memory.used  / (1024 ** 3), 2),
        "memory_percent":       memory.percent,
        "uploads_dir_size_mb":  _dir_size_mb(UPLOAD_DIR),
        "outputs_dir_size_mb":  _dir_size_mb(OUTPUT_DIR),
    }


def _dir_size_mb(path: Path) -> float:
    total = sum(f.stat().st_size for f in path.rglob("*") if f.is_file())
    return round(total / (1024 ** 2), 2)


@app.get("/test")
async def test_endpoint():
    return {
        "status": "ok",
        "message": "Dream Mulk AI service operational",
        "config": {
            "max_video_size_mb":  MAX_VIDEO_SIZE_MB,
            "default_frames":     DEFAULT_NUM_FRAMES,
            "supported_formats":  SUPPORTED_VIDEO_FORMATS,
            "scoring_mode":       "opencv-fast (no CLIP)",
            "re_price_model":     _price_model.is_loaded() if _price_model else False,
        }
    }


# ─────────────────────────────────────────────────────────────────────────────
# VIDEO FRAME EXTRACTION ROUTES
# ─────────────────────────────────────────────────────────────────────────────

@app.post("/extract-frames")
async def extract_frames(
    video: UploadFile = File(..., description="Video file to process"),
    num_frames: Optional[int] = Form(DEFAULT_NUM_FRAMES, description="Number of frames to extract (5-20)")
):
    video_path = None

    try:
        # ── Validate num_frames ──────────────────────────────────────────────
        num_frames = max(MIN_FRAMES, min(MAX_FRAMES, num_frames or DEFAULT_NUM_FRAMES))

        # ── Validate file extension ──────────────────────────────────────────
        if not video.filename:
            raise HTTPException(status_code=400, detail="No filename provided")

        file_ext = Path(video.filename).suffix.lower()
        if file_ext not in SUPPORTED_VIDEO_FORMATS:
            raise HTTPException(
                status_code=400,
                detail=f"Unsupported format '{file_ext}'. Supported: {', '.join(SUPPORTED_VIDEO_FORMATS)}"
            )

        # ── Read bytes ONCE ──────────────────────────────────────────────────
        content       = await video.read()
        file_size_mb  = len(content) / (1024 * 1024)

        if file_size_mb > MAX_VIDEO_SIZE_MB:
            raise HTTPException(
                status_code=400,
                detail=f"Video too large ({file_size_mb:.1f} MB). Maximum allowed: {MAX_VIDEO_SIZE_MB} MB"
            )

        if len(content) == 0:
            raise HTTPException(status_code=400, detail="Uploaded file is empty")

        # ── Write to disk directly from the bytes we already have ────────────
        timestamp  = int(time.time() * 1000)
        file_hash  = hashlib.md5(video.filename.encode()).hexdigest()[:8]
        filename   = f"{file_hash}_{timestamp}{file_ext}"
        video_path = UPLOAD_DIR / filename

        async with aiofiles.open(video_path, "wb") as f:
            await f.write(content)

        # Free RAM immediately
        del content

        print(f"\n📥 Received: {video.filename} ({file_size_mb:.1f} MB)")
        print(f"   Saved to:  {video_path}")
        print(f"   Extracting {num_frames} best frames...")

        # ── Extract frames ───────────────────────────────────────────────────
        if extractor is None:
            raise HTTPException(
                status_code=503,
                detail="Extractor not initialized. Try again in a moment."
            )

        result = extractor.extract_best_frames(
            video_path=video_path,
            num_frames=num_frames,
            verbose=True
        )

        if not result["success"]:
            raise HTTPException(
                status_code=500,
                detail=result.get("error", "Frame extraction failed")
            )

        # ── Build response with Base64 Images ────────────────────────────────
        frame_data_list = []
        for fp in result["frames"]:
            file_path = Path(fp)
            if file_path.exists():
                with open(file_path, "rb") as image_file:
                    encoded_string = base64.b64encode(image_file.read()).decode("utf-8")
                    frame_data_list.append({
                        "filename": file_path.name,
                        "base64":   f"data:image/jpeg;base64,{encoded_string}"
                    })

        return JSONResponse(content={
            "success": True,
            "message": f"Successfully extracted {len(frame_data_list)} frames",
            "data": {
                "frames":   frame_data_list,
                "scores":   result["scores"],
                "metadata": result["metadata"]
            }
        })

    except HTTPException:
        raise

    except Exception as e:
        import traceback
        tb = traceback.format_exc()
        print(f"\n❌ Error processing video: {e}")
        if DEBUG:
            print(tb)
        raise HTTPException(status_code=500, detail=f"Internal server error: {str(e)}")

    finally:
        # Always clean up the uploaded video file
        if video_path and video_path.exists():
            try:
                video_path.unlink()
                print(f"🗑️  Deleted upload: {video_path.name}")
            except Exception as e:
                print(f"⚠️  Failed to delete upload: {e}")


@app.post("/cleanup")
async def cleanup_old_files(max_age_hours: int = 1):
    """Delete output files older than max_age_hours."""
    current_time      = time.time()
    max_age_seconds   = max_age_hours * 3600
    deleted_uploads   = 0
    deleted_outputs   = 0

    for file_path in UPLOAD_DIR.rglob("*"):
        if file_path.is_file() and (current_time - file_path.stat().st_mtime) > max_age_seconds:
            file_path.unlink()
            deleted_uploads += 1

    for file_path in OUTPUT_DIR.rglob("*"):
        if file_path.is_file() and (current_time - file_path.stat().st_mtime) > max_age_seconds:
            file_path.unlink()
            deleted_outputs += 1

    return {
        "success":         True,
        "deleted_uploads": deleted_uploads,
        "deleted_outputs": deleted_outputs,
        "max_age_hours":   max_age_hours
    }


# ─────────────────────────────────────────────────────────────────────────────
# REAL ESTATE AI ROUTES  (all prefixed with /re/)
# ─────────────────────────────────────────────────────────────────────────────

@app.get("/re/health")
async def re_health():
    """Check real estate AI service health."""
    return {
        "status":       "ok",
        "price_model":  _price_model.is_loaded() if _price_model else False,
        "db_connected": _db_loader.test_connection() if _db_loader else False,
    }


@app.post("/re/train")
async def re_train(request: TrainRequest):
    """
    Train (or retrain) the XGBoost price prediction model.
    Pulls training data directly from the MySQL database.
    Called weekly by Laravel's TrainAIModelsJob.
    May take 5–30 minutes — Laravel uses a long timeout for this endpoint.
    """
    if _db_loader is None or _price_model is None:
        raise HTTPException(503, "Real estate AI not initialized")

    df = _db_loader.load_training_data()

    if df is None or len(df) < 50:
        raise HTTPException(
            400,
            f"Not enough training data: {len(df) if df is not None else 0} rows (need 50+)"
        )

    try:
        result = _price_model.train(df, request.hyperparameters or {})
        print(f"✅ Training complete: R²={result['metrics']['r2_score']:.4f}")
        return result
    except Exception as e:
        print(f"❌ Training failed: {e}")
        raise HTTPException(500, str(e))


@app.post("/re/predict")
async def re_predict(request: PredictRequest):
    """
    Predict fair market price for a single property.
    Called on-demand when user views a property detail page.
    Returns: predicted_price, verdict (overpriced/fair/underpriced), confidence.
    """
    if _price_model is None:
        raise HTTPException(503, "Real estate AI not initialized")

    if not _price_model.is_loaded():
        raise HTTPException(503, "Price model not loaded — run /re/train first")

    try:
        return _price_model.predict(request.features)
    except Exception as e:
        print(f"❌ Prediction failed: {e}")
        raise HTTPException(500, str(e))


@app.post("/re/predict/batch")
async def re_predict_batch(request: PredictBatchRequest):
    """
    Batch predict prices for multiple properties.
    Much faster than calling /re/predict in a loop.
    Called by Laravel's ComputePropertyValuationsJob (weekly batch run).
    """
    if _price_model is None:
        raise HTTPException(503, "Real estate AI not initialized")

    if not _price_model.is_loaded():
        raise HTTPException(503, "Price model not loaded — run /re/train first")

    try:
        results = _price_model.predict_batch(request.properties)
        return {"results": results}
    except Exception as e:
        print(f"❌ Batch prediction failed: {e}")
        raise HTTPException(500, str(e))


@app.post("/re/cluster-zones")
async def re_cluster_zones(request: ClusterZonesRequest):
    """
    Run K-means geospatial clustering to generate price zone polygons.
    Returns GeoJSON FeatureCollection — one polygon per price tier.
    Called daily by Laravel's ComputePriceZonesJob.
    """
    if _db_loader is None or _zone_model is None:
        raise HTTPException(503, "Real estate AI not initialized")

    df = _db_loader.load_properties_for_clustering(branch_id=request.branch_id)

    if df is None or len(df) < request.n_clusters:
        raise HTTPException(
            400,
            f"Not enough properties to cluster: {len(df) if df is not None else 0} (need {request.n_clusters}+)"
        )

    try:
        return _zone_model.cluster(
            df,
            n_clusters=request.n_clusters,
            algorithm=request.algorithm,
        )
    except Exception as e:
        print(f"❌ Clustering failed: {e}")
        raise HTTPException(500, str(e))


@app.post("/re/heatmap")
async def re_heatmap(request: HeatmapRequest):
    """
    Generate heatmap grid tiles for the Flutter map layer.
    Supports three types: price | demand | density | all
    Called daily by Laravel's ComputeHeatmapJob.
    Returns weighted lat/lng points ready for Flutter heatmap widget.
    """
    if _db_loader is None or _heatmap_model is None:
        raise HTTPException(503, "Real estate AI not initialized")

    df = _db_loader.load_properties_for_heatmap(branch_id=request.branch_id)

    if df is None or len(df) == 0:
        raise HTTPException(400, "No properties found for heatmap generation")

    try:
        return _heatmap_model.generate(
            df,
            heatmap_type=request.type,
            resolution=request.resolution,
        )
    except Exception as e:
        print(f"❌ Heatmap generation failed: {e}")
        raise HTTPException(500, str(e))


@app.post("/re/area-scores")
async def re_area_scores(request: AreaScoresRequest):
    """
    Compute ML-based demand and liquidity scores per area.
    Called by Laravel's ComputeAreaInsightsJob to enrich
    the SQL-computed metrics with velocity-based scoring.
    Returns: { area_id: { demand_score, liquidity_score } }
    """
    if _db_loader is None or _investment_model is None:
        raise HTTPException(503, "Real estate AI not initialized")

    df = _db_loader.load_area_activity(area_ids=request.area_ids)

    if df is None or len(df) == 0:
        return {"scores": {}}

    try:
        scores = _investment_model.compute_area_scores(df)
        return {"scores": scores}
    except Exception as e:
        print(f"❌ Area scores failed: {e}")
        raise HTTPException(500, str(e))


# ─────────────────────────────────────────────────────────────────────────────

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(
        "app.main:app",
        host="0.0.0.0",
        port=8001,
        reload=DEBUG,
        log_level="info"
    )
