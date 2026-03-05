"""
FastAPI Application for Video Frame Extraction Service
======================================================
FIXES:
  1. Double-read bug eliminated — bytes read once, written directly to disk
  2. CLIP model removed — replaced with fast OpenCV scoring
  3. Memory freed immediately after disk write
  4. Proper error messages returned to client (no silent infinite loading)
  5. Disk flush buffer added to prevent UI "Failed to fetch" race conditions
  6. BASE64 BYPASS: Images are sent as Base64 strings to avoid 404 proxy errors
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
import asyncio
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

# Initialize FastAPI app
app = FastAPI(
    title="Video AI Frame Extraction Service",
    description="Extract best quality frames from real estate videos",
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

# Mount static files for serving extracted frames (kept for legacy/debugging)
app.mount("/outputs", StaticFiles(directory=str(OUTPUT_DIR)), name="outputs")

# Global extractor instance
extractor = None


@app.on_event("startup")
async def startup_event():
    """Initialize extractor on startup (no ML model to load — instant)"""
    global extractor

    print("\n" + "=" * 70)
    print("🚀 Starting Video Frame Extraction Service v2.0")
    print("=" * 70)

    # Ensure directories exist
    UPLOAD_DIR.mkdir(parents=True, exist_ok=True)
    OUTPUT_DIR.mkdir(parents=True, exist_ok=True)

    # Pre-warm extractor (no heavy ML model — just OpenCV)
    print("\n📦 Initializing frame extractor (OpenCV-based, no ML model)...")
    extractor = get_extractor()

    print("✅ Service ready — fast OpenCV scoring active!")
    print("=" * 70)


@app.on_event("shutdown")
async def shutdown_event():
    """Cleanup on shutdown"""
    print("\n🛑 Shutting down service...")
    print("✅ Shutdown complete")


@app.get("/")
async def root():
    return {
        "service": "Video Frame Extraction Service",
        "version": "2.0.0",
        "status": "operational",
        "endpoints": {
            "health": "/health",
            "extract": "/extract-frames",
            "stats": "/stats",
            "cleanup": "/cleanup"
        }
    }


@app.get("/health")
async def health_check():
    return {
        "status": "healthy",
        "service": "video-frame-extractor",
        "extractor_ready": extractor is not None,
        "scoring_mode": "opencv-fast"
    }


@app.get("/stats")
async def system_stats():
    memory = psutil.virtual_memory()
    cpu = psutil.cpu_percent(interval=1)
    return {
        "cpu_percent": cpu,
        "memory_total_gb": round(memory.total / (1024 ** 3), 2),
        "memory_used_gb": round(memory.used / (1024 ** 3), 2),
        "memory_percent": memory.percent,
        "uploads_dir_size_mb": _dir_size_mb(UPLOAD_DIR),
        "outputs_dir_size_mb": _dir_size_mb(OUTPUT_DIR),
    }


def _dir_size_mb(path: Path) -> float:
    total = sum(f.stat().st_size for f in path.rglob("*") if f.is_file())
    return round(total / (1024 ** 2), 2)


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
        content = await video.read()
        file_size_mb = len(content) / (1024 * 1024)

        if file_size_mb > MAX_VIDEO_SIZE_MB:
            raise HTTPException(
                status_code=400,
                detail=f"Video too large ({file_size_mb:.1f} MB). Maximum allowed: {MAX_VIDEO_SIZE_MB} MB"
            )

        if len(content) == 0:
            raise HTTPException(status_code=400, detail="Uploaded file is empty")

        # ── Write to disk directly from the bytes we already have ────────────
        timestamp = int(time.time() * 1000)
        file_hash = hashlib.md5(video.filename.encode()).hexdigest()[:8]
        filename = f"{file_hash}_{timestamp}{file_ext}"
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
            raise HTTPException(status_code=503, detail="Extractor not initialized. Try again in a moment.")

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

            # Wait for file to exist on disk
            for _ in range(10):
                if file_path.exists() and file_path.stat().st_size > 0:
                    break
                await asyncio.sleep(0.1)

            # Read the image and encode it to Base64
            if file_path.exists():
                with open(file_path, "rb") as image_file:
                    encoded_string = base64.b64encode(image_file.read()).decode('utf-8')
                    frame_data_list.append({
                        "filename": file_path.name,
                        "base64": f"data:image/jpeg;base64,{encoded_string}"
                    })

        return JSONResponse(content={
            "success": True,
            "message": f"Successfully extracted {len(frame_data_list)} frames",
            "data": {
                "frames": frame_data_list,
                "scores": result["scores"],
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
    current_time = time.time()
    max_age_seconds = max_age_hours * 3600
    deleted_uploads = 0
    deleted_outputs = 0

    for file_path in UPLOAD_DIR.rglob("*"):
        if file_path.is_file() and (current_time - file_path.stat().st_mtime) > max_age_seconds:
            file_path.unlink()
            deleted_uploads += 1

    for file_path in OUTPUT_DIR.rglob("*"):
        if file_path.is_file() and (current_time - file_path.stat().st_mtime) > max_age_seconds:
            file_path.unlink()
            deleted_outputs += 1

    return {
        "success": True,
        "deleted_uploads": deleted_uploads,
        "deleted_outputs": deleted_outputs,
        "max_age_hours": max_age_hours
    }


@app.get("/test")
async def test_endpoint():
    return {
        "status": "ok",
        "message": "Video frame service operational",
        "config": {
            "max_video_size_mb": MAX_VIDEO_SIZE_MB,
            "default_frames": DEFAULT_NUM_FRAMES,
            "supported_formats": SUPPORTED_VIDEO_FORMATS,
            "scoring_mode": "opencv-fast (no CLIP)"
        }
    }


if __name__ == "__main__":
    import uvicorn
    uvicorn.run(
        "app.main:app",
        host="0.0.0.0",
        port=8001,
        reload=DEBUG,
        log_level="info"
    )
