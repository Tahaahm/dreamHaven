"""
Frame Extractor Module with Diversity Filtering
Main logic for extracting best frames from videos - PREVENTS REPETITIVE FRAMES
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
    Extract and rank frames from video files with diversity filtering
    """

    def __init__(self):
        """Initialize the extractor"""
        self.scorer = get_scorer()
        # Similarity threshold - frames more similar than this will be rejected
        self.similarity_threshold = 0.85  # 85% similarity = too similar

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

    def calculate_histogram_similarity(self, frame1: np.ndarray, frame2: np.ndarray) -> float:
        """
        Calculate similarity between two frames using histogram comparison
        Returns value between 0 (completely different) and 1 (identical)

        Args:
            frame1: First frame (OpenCV BGR image)
            frame2: Second frame (OpenCV BGR image)

        Returns:
            Similarity score (0-1, where 1 = identical)
        """
        # Convert to HSV for better color comparison
        hsv1 = cv2.cvtColor(frame1, cv2.COLOR_BGR2HSV)
        hsv2 = cv2.cvtColor(frame2, cv2.COLOR_BGR2HSV)

        # Calculate histograms
        hist1 = cv2.calcHist([hsv1], [0, 1], None, [50, 60], [0, 180, 0, 256])
        hist2 = cv2.calcHist([hsv2], [0, 1], None, [50, 60], [0, 180, 0, 256])

        # Normalize histograms
        cv2.normalize(hist1, hist1, alpha=0, beta=1, norm_type=cv2.NORM_MINMAX)
        cv2.normalize(hist2, hist2, alpha=0, beta=1, norm_type=cv2.NORM_MINMAX)

        # Compare using correlation (returns 0-1, 1 = identical)
        similarity = cv2.compareHist(hist1, hist2, cv2.HISTCMP_CORREL)
        return similarity

    def is_diverse_enough(self, new_frame: np.ndarray, selected_frames: List[np.ndarray]) -> bool:
        """
        Check if new frame is diverse enough compared to already selected frames

        Args:
            new_frame: Frame to check
            selected_frames: List of already selected frames

        Returns:
            True if frame is different enough to be included
        """
        if not selected_frames:
            return True  # First frame is always included

        for existing_frame in selected_frames:
            similarity = self.calculate_histogram_similarity(new_frame, existing_frame)
            if similarity > self.similarity_threshold:
                return False  # Too similar to an existing frame

        return True  # Diverse enough from all selected frames

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

    def filter_for_diversity(
        self,
        ranked_frames: List[Tuple[int, Dict]],
        candidates: List[np.ndarray],
        num_frames: int,
        verbose: bool = True
    ) -> List[Tuple[int, Dict]]:
        """
        Filter ranked frames to ensure visual diversity

        Args:
            ranked_frames: List of (frame_idx, scores) tuples sorted by quality
            candidates: Original candidate frames
            num_frames: Target number of frames
            verbose: Print filtering progress

        Returns:
            Filtered list of diverse frames
        """
        if verbose:
            print("\n" + "="*60)
            print("🎨 DIVERSITY FILTERING - Removing Similar Frames")
            print("="*60)

        selected = []
        selected_frames = []  # Store actual frame data for comparison
        skipped_count = 0

        for frame_idx, scores in ranked_frames:
            if len(selected) >= num_frames:
                break

            frame = candidates[frame_idx]

            # Check if this frame is diverse enough
            if self.is_diverse_enough(frame, selected_frames):
                selected.append((frame_idx, scores))
                selected_frames.append(frame)

                diversity_status = "FIRST FRAME" if len(selected) == 1 else "✓ DIVERSE"
                if verbose:
                    print(f"  ✅ Frame #{len(selected)}: Quality={scores['total']:.1f} | {diversity_status}")
            else:
                skipped_count += 1
                if verbose:
                    print(f"  ❌ Skipped: Quality={scores['total']:.1f} | TOO SIMILAR (>{self.similarity_threshold*100:.0f}% match)")

        # If we don't have enough diverse frames, lower threshold and try again
        if len(selected) < num_frames and len(ranked_frames) > len(selected):
            if verbose:
                print(f"\n  ⚠️  Only {len(selected)} diverse frames found at {self.similarity_threshold*100:.0f}% threshold")
                print(f"  🔄 Lowering threshold to 75% to fill remaining slots...")

            # Temporarily lower threshold
            original_threshold = self.similarity_threshold
            self.similarity_threshold = 0.75

            for frame_idx, scores in ranked_frames:
                if len(selected) >= num_frames:
                    break

                # Skip already selected frames
                if any(idx == frame_idx for idx, _ in selected):
                    continue

                frame = candidates[frame_idx]

                if self.is_diverse_enough(frame, selected_frames):
                    selected.append((frame_idx, scores))
                    selected_frames.append(frame)

                    if verbose:
                        print(f"  ➕ Frame #{len(selected)}: Quality={scores['total']:.1f} | ACCEPTABLE (75% threshold)")

            # Restore original threshold
            self.similarity_threshold = original_threshold

        if verbose:
            print(f"\n✨ Diversity filtering complete: {len(selected)} unique frames selected ({skipped_count} similar frames removed)")

        return selected

    def extract_best_frames(
        self,
        video_path: Path,
        num_frames: int = DEFAULT_NUM_FRAMES,
        verbose: bool = True
    ) -> Dict:
        """
        Main method: Extract best N frames from video with diversity filtering

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

            # Step 2: Score and rank frames (get more than needed for diversity filtering)
            if verbose:
                print("\n" + "="*60)
                print("🎯 STEP 2: Scoring & Ranking Frames")
                print("="*60)

            # Request more frames than needed for diversity filtering
            num_to_score = min(len(candidates), num_frames * 3)

            ranked_frames = self.scorer.rank_frames(
                candidates,
                num_best=num_to_score,
                verbose=verbose
            )

            # Step 2.5: Apply diversity filtering
            diverse_frames = self.filter_for_diversity(
                ranked_frames,
                candidates,
                num_frames,
                verbose=verbose
            )

            # Step 3: Save best diverse frames
            if verbose:
                print("\n" + "="*60)
                print("💾 STEP 3: Saving Best Diverse Frames")
                print("="*60)

            video_hash = hashlib.md5(video_path.name.encode()).hexdigest()[:12]

            saved_frames = []
            frame_scores = []

            for rank, (frame_idx, scores) in enumerate(diverse_frames, 1):
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
                "frames_extracted": len(diverse_frames),
                "processing_time_seconds": round(elapsed_time, 2)
            }
            cap.release()

            if verbose:
                print("\n" + "="*60)
                print(f"✨ SUCCESS! Extracted {len(diverse_frames)} diverse, high-quality frames in {elapsed_time:.1f}s")
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
