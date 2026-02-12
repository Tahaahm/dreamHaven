"""
Frame Extractor Module
Main logic for extracting best frames from videos
"""

import cv2
import numpy as np
from pathlib import Path
from typing import List, Dict, Tuple, Optional
from PIL import Image
import hashlib
import time

from app.config import (
    FRAME_SAMPLE_INTERVAL,
    DEFAULT_NUM_FRAMES,
    MIN_FRAMES,
    MAX_FRAMES,
    OUTPUT_IMAGE_FORMAT,
    OUTPUT_IMAGE_QUALITY,
    MAX_IMAGE_DIMENSION,
    MAX_VIDEO_DURATION_SECONDS,
    OUTPUT_DIR
)
from app.quality_scorer import get_scorer


class VideoFrameExtractor:
    """
    Extract and rank frames from video files
    """

    def __init__(self):
        """Initialize the extractor"""
        self.scorer = get_scorer()

    def validate_video(self, video_path: Path) -> Tuple[bool, Optional[str]]:
        """
        Validate video file

        Args:
            video_path: Path to video file

        Returns:
            (is_valid, error_message)
        """
        if not video_path.exists():
            return False, "Video file not found"

        if not video_path.is_file():
            return False, "Path is not a file"

        # Try to open with OpenCV
        cap = cv2.VideoCapture(str(video_path))

        if not cap.isOpened():
            return False, "Cannot open video file - may be corrupted or unsupported format"

        # Check duration
        fps = cap.get(cv2.CAP_PROP_FPS)
        frame_count = int(cap.get(cv2.CAP_PROP_FRAME_COUNT))

        if fps == 0:
            cap.release()
            return False, "Invalid video - FPS is zero"

        duration = frame_count / fps

        if duration > MAX_VIDEO_DURATION_SECONDS:
            cap.release()
            return False, f"Video too long (max {MAX_VIDEO_DURATION_SECONDS}s, got {duration:.0f}s)"

        cap.release()
        return True, None

    def extract_candidate_frames(
        self,
        video_path: Path,
        interval: int = FRAME_SAMPLE_INTERVAL
    ) -> List[np.ndarray]:
        """
        Extract frames at regular intervals from video

        Args:
            video_path: Path to video file
            interval: Extract 1 frame every N frames

        Returns:
            List of frames (OpenCV BGR images)
        """
        print(f"\n📹 Opening video: {video_path.name}")

        cap = cv2.VideoCapture(str(video_path))

        if not cap.isOpened():
            raise ValueError("Cannot open video file")

        # Get video properties
        fps = cap.get(cv2.CAP_PROP_FPS)
        total_frames = int(cap.get(cv2.CAP_PROP_FRAME_COUNT))
        duration = total_frames / fps if fps > 0 else 0

        print(f"  Duration: {duration:.1f}s | FPS: {fps:.1f} | Total frames: {total_frames}")

        frames = []
        frame_indices = []
        frame_idx = 0

        # Skip first 5% and last 5% (usually not good quality)
        start_skip = int(total_frames * 0.05)
        end_skip = int(total_frames * 0.95)

        print(f"  Sampling frames {start_skip} to {end_skip} (interval: {interval})")

        while cap.isOpened():
            ret, frame = cap.read()

            if not ret:
                break

            # Check if we're in valid range and at interval
            if start_skip <= frame_idx <= end_skip and frame_idx % interval == 0:
                # Check frame quality before adding
                if not self._is_blank_frame(frame):
                    frames.append(frame)
                    frame_indices.append(frame_idx)

            frame_idx += 1

        cap.release()

        print(f"✅ Extracted {len(frames)} candidate frames from video")

        return frames

    def _is_blank_frame(self, frame: np.ndarray, threshold: float = 10.0) -> bool:
        """
        Check if frame is blank/black

        Args:
            frame: OpenCV image
            threshold: Minimum std deviation to consider non-blank

        Returns:
            True if frame is blank
        """
        gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
        return gray.std() < threshold

    def resize_frame(self, frame: np.ndarray) -> np.ndarray:
        """
        Resize frame to maximum dimension while maintaining aspect ratio

        Args:
            frame: OpenCV image

        Returns:
            Resized frame
        """
        height, width = frame.shape[:2]

        if max(height, width) <= MAX_IMAGE_DIMENSION:
            return frame  # Already within limits

        # Calculate new dimensions
        if width > height:
            new_width = MAX_IMAGE_DIMENSION
            new_height = int(height * (MAX_IMAGE_DIMENSION / width))
        else:
            new_height = MAX_IMAGE_DIMENSION
            new_width = int(width * (MAX_IMAGE_DIMENSION / height))

        resized = cv2.resize(frame, (new_width, new_height), interpolation=cv2.INTER_LANCZOS4)

        return resized

    def save_frame(
        self,
        frame: np.ndarray,
        video_hash: str,
        frame_number: int
    ) -> Path:
        """
        Save frame as image file

        Args:
            frame: OpenCV image (BGR)
            video_hash: Hash of original video filename
            frame_number: Frame number for naming

        Returns:
            Path to saved image
        """
        # Resize if needed
        frame = self.resize_frame(frame)

        # Convert BGR to RGB for PIL
        rgb_frame = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
        pil_image = Image.fromarray(rgb_frame)

        # Generate filename
        timestamp = int(time.time() * 1000)
        filename = f"{video_hash}_frame_{frame_number:03d}_{timestamp}.jpg"
        output_path = OUTPUT_DIR / filename

        # Save with high quality
        pil_image.save(
            output_path,
            format=OUTPUT_IMAGE_FORMAT,
            quality=OUTPUT_IMAGE_QUALITY,
            optimize=True
        )

        return output_path

    def extract_best_frames(
        self,
        video_path: Path,
        num_frames: int = DEFAULT_NUM_FRAMES,
        verbose: bool = True
    ) -> Dict:
        """
        Main method: Extract best N frames from video

        Args:
            video_path: Path to video file
            num_frames: Number of frames to extract
            verbose: Print progress

        Returns:
            Dictionary with:
                - success: bool
                - frames: List of frame paths
                - scores: List of quality scores
                - metadata: Video metadata
                - error: Error message (if failed)
        """
        start_time = time.time()

        # Validate inputs
        num_frames = max(MIN_FRAMES, min(MAX_FRAMES, num_frames))

        # Validate video
        is_valid, error_msg = self.validate_video(video_path)
        if not is_valid:
            return {
                "success": False,
                "error": error_msg,
                "frames": [],
                "scores": []
            }

        try:
            # Step 1: Extract candidate frames
            if verbose:
                print("\n" + "="*60)
                print("🎬 STEP 1: Extracting Candidate Frames")
                print("="*60)

            candidates = self.extract_candidate_frames(video_path)

            if len(candidates) < num_frames:
                print(f"⚠️  Warning: Only {len(candidates)} frames available (requested {num_frames})")
                num_frames = len(candidates)

            # Step 2: Score and rank frames
            if verbose:
                print("\n" + "="*60)
                print("🎯 STEP 2: Scoring & Ranking Frames")
                print("="*60)

            ranked_frames = self.scorer.rank_frames(
                candidates,
                num_best=num_frames,
                verbose=verbose
            )

            # Step 3: Save best frames
            if verbose:
                print("\n" + "="*60)
                print("💾 STEP 3: Saving Best Frames")
                print("="*60)

            video_hash = hashlib.md5(video_path.name.encode()).hexdigest()[:12]

            saved_frames = []
            frame_scores = []

            for rank, (frame_idx, scores) in enumerate(ranked_frames, 1):
                frame = candidates[frame_idx]

                output_path = self.save_frame(frame, video_hash, rank)

                saved_frames.append(str(output_path))
                frame_scores.append({
                    "rank": rank,
                    "original_index": frame_idx,
                    "total_score": round(scores["total"], 2),
                    "sharpness": round(scores["sharpness"], 2),
                    "brightness": round(scores["brightness"], 2),
                    "composition": round(scores["composition"], 2),
                    "clip_relevance": round(scores["clip_relevance"], 2)
                })

                if verbose:
                    print(f"  ✅ Frame #{rank} saved: {output_path.name} (score: {scores['total']:.1f})")

            elapsed_time = time.time() - start_time

            # Get video metadata
            cap = cv2.VideoCapture(str(video_path))
            metadata = {
                "duration_seconds": round(cap.get(cv2.CAP_PROP_FRAME_COUNT) / cap.get(cv2.CAP_PROP_FPS), 2),
                "fps": round(cap.get(cv2.CAP_PROP_FPS), 2),
                "width": int(cap.get(cv2.CAP_PROP_FRAME_WIDTH)),
                "height": int(cap.get(cv2.CAP_PROP_FRAME_HEIGHT)),
                "total_frames": int(cap.get(cv2.CAP_PROP_FRAME_COUNT)),
                "candidates_evaluated": len(candidates),
                "frames_extracted": num_frames,
                "processing_time_seconds": round(elapsed_time, 2)
            }
            cap.release()

            if verbose:
                print("\n" + "="*60)
                print(f"✨ SUCCESS! Extracted {num_frames} best frames in {elapsed_time:.1f}s")
                print("="*60)

            return {
                "success": True,
                "frames": saved_frames,
                "scores": frame_scores,
                "metadata": metadata,
                "error": None
            }

        except Exception as e:
            import traceback
            error_details = traceback.format_exc()

            print(f"\n❌ ERROR: {str(e)}")
            if verbose:
                print(error_details)

            return {
                "success": False,
                "error": str(e),
                "error_details": error_details if verbose else None,
                "frames": [],
                "scores": []
            }


# Singleton instance
_extractor_instance = None


def get_extractor() -> VideoFrameExtractor:
    """Get or create singleton extractor instance"""
    global _extractor_instance
    if _extractor_instance is None:
        _extractor_instance = VideoFrameExtractor()
    return _extractor_instance
