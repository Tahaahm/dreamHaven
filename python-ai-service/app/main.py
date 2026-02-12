"""
FastAPI Application for Video Frame Extraction Service
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
from app.quality_scorer import get_scorer

# Initialize FastAPI app
app = FastAPI(
    title="Video AI Frame Extraction Service",
    description="Extract best quality frames from real estate videos using AI",
    version="1.0.0",
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

# Mount static files for serving extracted frames
app.mount("/outputs", StaticFiles(directory=str(OUTPUT_DIR)), name="outputs")

# Global instances
extractor = None
scorer = None


@app.on_event("startup")
async def startup_event():
    """Initialize AI models on startup"""
    global extractor, scorer

    print("\n" + "="*70)
    print("🚀 Starting Video AI Frame Extraction Service")
    print("="*70)

    # Pre-load models
    print("\n📦 Loading AI models...")
    extractor = get_extractor()
    scorer = get_scorer()

    print("\n✅ Service ready!")
    print("="*70)


@app.on_event("shutdown")
async def shutdown_event():
    """Cleanup on shutdown"""
    print("\n🛑 Shutting down service...")

    if scorer:
        scorer.cleanup()

    print("✅ Shutdown complete")


@app.get("/")
async def root():
    """Root endpoint"""
    return {
        "service": "Video AI Frame Extraction Service",
        "version": "1.0.0",
        "status": "operational",
        "endpoints": {
            "health": "/health",
            "extract": "/extract-frames",
            "stats": "/stats"
        }
    }


@app.get("/health")
async def health_check():
    """Health check endpoint"""
    return {
        "status": "healthy",
        "service": "video-frame-extractor",
        "models_loaded": extractor is not None and scorer is not None
    }


@app.get("/stats")
async def system_stats():
    """Get system resource stats"""
    memory = psutil.virtual_memory()
    cpu = psutil.cpu_percent(interval=1)

    return {
        "cpu_percent": cpu,
        "memory_total_gb": round(memory.total / (1024**3), 2),
        "memory_used_gb": round(memory.used / (1024**3), 2),
        "memory_percent": memory.percent,
        "uploads_dir_size_mb": get_directory_size(UPLOAD_DIR),
        "outputs_dir_size_mb": get_directory_size(OUTPUT_DIR)
    }


def get_directory_size(path: Path) -> float:
    """Get directory size in MB"""
    total = sum(f.stat().st_size for f in path.rglob('*') if f.is_file())
    return round(total / (1024**2), 2)


async def save_upload_file(upload_file: UploadFile) -> Path:
    """
    Save uploaded file to disk

    Args:
        upload_file: FastAPI UploadFile object

    Returns:
        Path to saved file
    """
    # Generate unique filename
    timestamp = int(time.time() * 1000)
    file_hash = hashlib.md5(upload_file.filename.encode()).hexdigest()[:8]
    extension = Path(upload_file.filename).suffix.lower()

    filename = f"{file_hash}_{timestamp}{extension}"
    file_path = UPLOAD_DIR / filename

    # Save file
    async with aiofiles.open(file_path, 'wb') as f:
        content = await upload_file.read()
        await f.write(content)

    return file_path


@app.post("/extract-frames")
async def extract_frames(
    video: UploadFile = File(..., description="Video file to process"),
    num_frames: Optional[int] = Form(DEFAULT_NUM_FRAMES, description="Number of frames to extract")
):
    """
    Extract best N frames from uploaded video

    Args:
        video: Video file (MP4, AVI, MOV, etc.)
        num_frames: Number of frames to extract (5-20)

    Returns:
        JSON with extracted frame paths and metadata
    """
    video_path = None

    try:
        # Validate num_frames
        num_frames = max(MIN_FRAMES, min(MAX_FRAMES, num_frames))

        # Validate file extension
        file_ext = Path(video.filename).suffix.lower()
        if file_ext not in SUPPORTED_VIDEO_FORMATS:
            raise HTTPException(
                status_code=400,
                detail=f"Unsupported video format. Supported: {', '.join(SUPPORTED_VIDEO_FORMATS)}"
            )

        # Check file size
        content = await video.read()
        file_size_mb = len(content) / (1024 * 1024)

        if file_size_mb > MAX_VIDEO_SIZE_MB:
            raise HTTPException(
                status_code=400,
                detail=f"Video too large ({file_size_mb:.1f}MB). Max: {MAX_VIDEO_SIZE_MB}MB"
            )

        # Reset file pointer and save
        await video.seek(0)
        video_path = await save_upload_file(video)

        print(f"\n📥 Received video: {video.filename} ({file_size_mb:.1f}MB)")
        print(f"   Saved to: {video_path}")
        print(f"   Extracting {num_frames} best frames...")

        # Extract frames
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

        # Convert absolute paths to relative URLs
        base_url = "/outputs"  # Served by FastAPI static files
        frame_urls = [
            f"{base_url}/{Path(frame_path).name}"
            for frame_path in result["frames"]
        ]

        response_data = {
            "success": True,
            "message": f"Successfully extracted {len(frame_urls)} frames",
            "data": {
                "frames": frame_urls,
                "scores": result["scores"],
                "metadata": result["metadata"]
            }
        }

        return JSONResponse(content=response_data)

    except HTTPException:
        raise

    except Exception as e:
        import traceback
        error_details = traceback.format_exc()

        print(f"\n❌ Error processing video: {str(e)}")
        if DEBUG:
            print(error_details)

        raise HTTPException(
            status_code=500,
            detail=f"Internal server error: {str(e)}"
        )

    finally:
        # Cleanup uploaded video
        if video_path and video_path.exists():
            try:
                video_path.unlink()
                print(f"🗑️  Deleted uploaded video: {video_path.name}")
            except Exception as e:
                print(f"⚠️  Failed to delete video: {e}")


@app.post("/cleanup")
async def cleanup_old_files(max_age_hours: int = 1):
    """
    Cleanup files older than specified hours

    Args:
        max_age_hours: Delete files older than this (default: 1 hour)

    Returns:
        Cleanup statistics
    """
    current_time = time.time()
    max_age_seconds = max_age_hours * 3600

    deleted_uploads = 0
    deleted_outputs = 0

    # Clean uploads
    for file_path in UPLOAD_DIR.rglob('*'):
        if file_path.is_file():
            age = current_time - file_path.stat().st_mtime
            if age > max_age_seconds:
                file_path.unlink()
                deleted_uploads += 1

    # Clean outputs
    for file_path in OUTPUT_DIR.rglob('*'):
        if file_path.is_file():
            age = current_time - file_path.stat().st_mtime
            if age > max_age_seconds:
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
    """Test endpoint to verify service is running"""
    return {
        "status": "ok",
        "message": "Video AI service is operational",
        "config": {
            "max_video_size_mb": MAX_VIDEO_SIZE_MB,
            "default_frames": DEFAULT_NUM_FRAMES,
            "supported_formats": SUPPORTED_VIDEO_FORMATS
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
