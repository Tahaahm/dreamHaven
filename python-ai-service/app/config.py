"""
Configuration file for Video AI Frame Extraction Service
"""
import os
from pathlib import Path

# Base Paths
BASE_DIR = Path(__file__).resolve().parent.parent
UPLOAD_DIR = BASE_DIR / "uploads"
OUTPUT_DIR = BASE_DIR / "outputs"
LOG_DIR = BASE_DIR / "logs"
MODEL_CACHE_DIR = BASE_DIR / "models"

# Create directories if they don't exist
for directory in [UPLOAD_DIR, OUTPUT_DIR, LOG_DIR, MODEL_CACHE_DIR]:
    directory.mkdir(parents=True, exist_ok=True)

# API Configuration
API_HOST = os.getenv("API_HOST", "0.0.0.0")
API_PORT = int(os.getenv("API_PORT", "8001"))
API_WORKERS = int(os.getenv("API_WORKERS", "2"))

# Video Processing Settings
MAX_VIDEO_SIZE_MB = int(os.getenv("MAX_VIDEO_SIZE_MB", "500"))  # 500MB max
MAX_VIDEO_DURATION_SECONDS = int(os.getenv("MAX_VIDEO_DURATION", "600"))  # 10 minutes
SUPPORTED_VIDEO_FORMATS = [".mp4", ".avi", ".mov", ".mkv", ".webm", ".flv"]

# Frame Extraction Settings
DEFAULT_NUM_FRAMES = 10
MIN_FRAMES = 5
MAX_FRAMES = 20
FRAME_SAMPLE_INTERVAL = 30  # Extract 1 frame every 30 frames for analysis

# Image Processing
OUTPUT_IMAGE_FORMAT = "JPEG"
OUTPUT_IMAGE_QUALITY = 95  # High quality for real estate
MAX_IMAGE_DIMENSION = 1920  # Resize to max 1920px (maintain aspect ratio)

# AI Model Configuration
CLIP_MODEL_NAME = "openai/clip-vit-base-patch32"
USE_GPU = os.getenv("USE_GPU", "false").lower() == "true"
DEVICE = "cuda" if USE_GPU else "cpu"

# Quality Scoring Weights
SCORE_WEIGHTS = {
    "sharpness": 0.30,      # Sharpness/blur detection
    "brightness": 0.20,      # Proper exposure
    "composition": 0.25,     # Rule of thirds, etc.
    "clip_relevance": 0.25,  # Semantic relevance to real estate
}

# CLIP Prompts for Real Estate
REAL_ESTATE_PROMPTS = [
    "a professional real estate photo",
    "a well-lit interior room",
    "an attractive building exterior",
    "a modern kitchen",
    "a beautiful bathroom",
    "a spacious living room",
    "a clean bedroom",
    "architectural details",
    "natural lighting",
    "professional property photography"
]

# Cleanup Settings
AUTO_CLEANUP_HOURS = int(os.getenv("AUTO_CLEANUP_HOURS", "1"))  # Delete files after 1 hour
KEEP_ORIGINAL_VIDEO = os.getenv("KEEP_ORIGINAL_VIDEO", "false").lower() == "true"

# Logging
LOG_LEVEL = os.getenv("LOG_LEVEL", "INFO")
LOG_FORMAT = "<green>{time:YYYY-MM-DD HH:mm:ss}</green> | <level>{level: <8}</level> | <cyan>{name}</cyan>:<cyan>{function}</cyan> - <level>{message}</level>"
LOG_FILE = LOG_DIR / "app.log"
LOG_ROTATION = "500 MB"
LOG_RETENTION = "7 days"

# Performance Settings
MAX_CONCURRENT_PROCESSES = int(os.getenv("MAX_CONCURRENT_PROCESSES", "4"))
MEMORY_LIMIT_GB = int(os.getenv("MEMORY_LIMIT_GB", "8"))

# Security
ALLOWED_ORIGINS = os.getenv("ALLOWED_ORIGINS", "*").split(",")
MAX_UPLOAD_SIZE = MAX_VIDEO_SIZE_MB * 1024 * 1024  # Convert to bytes

# Debug Mode
DEBUG = os.getenv("DEBUG", "false").lower() == "true"

print(f"""
╔══════════════════════════════════════════════════════════════════╗
║           Video AI Frame Extraction Service - Config             ║
╚══════════════════════════════════════════════════════════════════╝
  Base Directory: {BASE_DIR}
  Upload Directory: {UPLOAD_DIR}
  Output Directory: {OUTPUT_DIR}
  Device: {DEVICE}
  Max Video Size: {MAX_VIDEO_SIZE_MB}MB
  Default Frames: {DEFAULT_NUM_FRAMES}
  Auto Cleanup: {AUTO_CLEANUP_HOURS} hours
  Debug Mode: {DEBUG}
═══════════════════════════════════════════════════════════════════
""")
