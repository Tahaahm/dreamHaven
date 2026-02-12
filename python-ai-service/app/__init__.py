"""
Video AI Frame Extraction Service
Main package initialization
"""

__version__ = "1.0.0"
__author__ = "Dream Mulk"
__description__ = "AI-powered video frame extraction for real estate"

# Package metadata
from app import config
from app.frame_extractor import VideoFrameExtractor, get_extractor
from app.quality_scorer import FrameQualityScorer, get_scorer

__all__ = [
    'config',
    'VideoFrameExtractor',
    'get_extractor',
    'FrameQualityScorer',
    'get_scorer',
]
