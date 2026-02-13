"""
ULTIMATE Frame Extractor - State-of-the-Art 2024
Combines best techniques from latest research:
- CLIP semantic embeddings for meaningful content
- Adaptive clustering for optimal frame selection
- Motion-based scene change detection
- Multi-feature diversity filtering (histogram + structure + semantics)
- Eliminates: blurry frames, similar angles, boring content
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


class UltimateFrameExtractor:
    """
    State-of-the-art frame extraction combining:
    - Semantic understanding (CLIP features)
    - Multi-metric diversity (color + structure + content)
    - Motion-based scene detection
    - Advanced quality scoring
    """

    def __init__(self):
        """Initialize with multiple scoring mechanisms"""
        self.scorer = get_scorer()

        # Diversity thresholds
        self.histogram_threshold = 0.85  # Color similarity
        self.structural_threshold = 0.90  # SSIM for structure
        self.motion_threshold = 30.0     # Motion magnitude

    def validate_video(self, video_path: Path) -> Tuple[bool, Optional[str]]:
        """Validate video file"""
        if not video_path.exists():
            return False, "Video file not found"

        if not video_path.is_file():
            return False, "Path is not a file"

        cap = cv2.VideoCapture(str(video_path))
        if not cap.isOpened():
            return False, "Cannot open video - may be corrupted"

        fps = cap.get(cv2.CAP_PROP_FPS)
        frame_count = int(cap.get(cv2.CAP_PROP_FRAME_COUNT))

        if fps == 0:
            cap.release()
            return False, "Invalid video - FPS is zero"

        duration = frame_count / fps
        if duration > MAX_VIDEO_DURATION_SECONDS:
            cap.release()
            return False, f"Video too long (max {MAX_VIDEO_DURATION_SECONDS}s)"

        cap.release()
        return True, None

    def calculate_histogram_similarity(self, frame1: np.ndarray, frame2: np.ndarray) -> float:
        """
        Calculate color histogram similarity (HSV color space)
        Returns: 0-1 (1 = identical)
        """
        hsv1 = cv2.cvtColor(frame1, cv2.COLOR_BGR2HSV)
        hsv2 = cv2.cvtColor(frame2, cv2.COLOR_BGR2HSV)

        # Multi-channel histogram
        hist1 = cv2.calcHist([hsv1], [0, 1, 2], None, [50, 60, 60], [0, 180, 0, 256, 0, 256])
        hist2 = cv2.calcHist([hsv2], [0, 1, 2], None, [50, 60, 60], [0, 180, 0, 256, 0, 256])

        cv2.normalize(hist1, hist1, alpha=0, beta=1, norm_type=cv2.NORM_MINMAX)
        cv2.normalize(hist2, hist2, alpha=0, beta=1, norm_type=cv2.NORM_MINMAX)

        return cv2.compareHist(hist1, hist2, cv2.HISTCMP_CORREL)

    def calculate_structural_similarity(self, frame1: np.ndarray, frame2: np.ndarray) -> float:
        """
        Calculate structural similarity using SSIM
        Detects frames with similar composition/layout
        Returns: 0-1 (1 = identical structure)
        """
        # Resize for faster computation
        size = (256, 256)
        f1 = cv2.resize(frame1, size)
        f2 = cv2.resize(frame2, size)

        # Convert to grayscale
        gray1 = cv2.cvtColor(f1, cv2.COLOR_BGR2GRAY)
        gray2 = cv2.cvtColor(f2, cv2.COLOR_BGR2GRAY)

        # Compute SSIM
        C1 = (0.01 * 255) ** 2
        C2 = (0.03 * 255) ** 2

        mu1 = cv2.GaussianBlur(gray1, (11, 11), 1.5)
        mu2 = cv2.GaussianBlur(gray2, (11, 11), 1.5)

        mu1_sq = mu1 ** 2
        mu2_sq = mu2 ** 2
        mu1_mu2 = mu1 * mu2

        sigma1_sq = cv2.GaussianBlur(gray1 ** 2, (11, 11), 1.5) - mu1_sq
        sigma2_sq = cv2.GaussianBlur(gray2 ** 2, (11, 11), 1.5) - mu2_sq
        sigma12 = cv2.GaussianBlur(gray1 * gray2, (11, 11), 1.5) - mu1_mu2

        ssim_map = ((2 * mu1_mu2 + C1) * (2 * sigma12 + C2)) / \
                   ((mu1_sq + mu2_sq + C1) * (sigma1_sq + sigma2_sq + C2))

        return float(np.mean(ssim_map))

    def detect_motion_magnitude(self, frame1: np.ndarray, frame2: np.ndarray) -> float:
        """
        Detect motion between frames using optical flow
        Returns: motion magnitude (higher = more motion)
        """
        gray1 = cv2.cvtColor(frame1, cv2.COLOR_BGR2GRAY)
        gray2 = cv2.cvtColor(frame2, cv2.COLOR_BGR2GRAY)

        # Calculate dense optical flow
        flow = cv2.calcOpticalFlowFarneback(
            gray1, gray2, None,
            pyr_scale=0.5, levels=3, winsize=15,
            iterations=3, poly_n=5, poly_sigma=1.2, flags=0
        )

        # Calculate magnitude
        magnitude = np.sqrt(flow[..., 0]**2 + flow[..., 1]**2)
        return float(np.mean(magnitude))

    def is_scene_change(self, frame1: np.ndarray, frame2: np.ndarray) -> bool:
        """
        Detect if there's a scene change between frames
        Uses motion + histogram difference
        """
        motion = self.detect_motion_magnitude(frame1, frame2)
        hist_sim = self.calculate_histogram_similarity(frame1, frame2)

        # Scene change if: high motion OR very different colors
        return motion > self.motion_threshold or hist_sim < 0.6

    def is_diverse_enough(
        self,
        new_frame: np.ndarray,
        selected_frames: List[np.ndarray],
        verbose: bool = False
    ) -> Tuple[bool, str]:
        """
        Multi-metric diversity check:
        1. Color histogram similarity
        2. Structural similarity (SSIM)
        3. Ensures no repetitive angles/compositions

        Returns: (is_diverse, reason)
        """
        if not selected_frames:
            return True, "First frame"

        for idx, existing_frame in enumerate(selected_frames):
            # Check 1: Color similarity
            hist_sim = self.calculate_histogram_similarity(new_frame, existing_frame)
            if hist_sim > self.histogram_threshold:
                return False, f"Too similar colors to frame #{idx+1} ({hist_sim*100:.0f}%)"

            # Check 2: Structural similarity (composition/layout)
            struct_sim = self.calculate_structural_similarity(new_frame, existing_frame)
            if struct_sim > self.structural_threshold:
                return False, f"Same composition as frame #{idx+1} ({struct_sim*100:.0f}%)"

        return True, "Unique frame"

    def extract_candidate_frames(
        self,
        video_path: Path,
        interval: int = FRAME_SAMPLE_INTERVAL
    ) -> Tuple[List[np.ndarray], List[int]]:
        """
        Extract candidate frames with scene change detection
        Returns: (frames, frame_indices)
        """
        print(f"\n📹 Opening video: {video_path.name}")

        cap = cv2.VideoCapture(str(video_path))
        if not cap.isOpened():
            raise ValueError("Cannot open video file")

        fps = cap.get(cv2.CAP_PROP_FPS)
        total_frames = int(cap.get(cv2.CAP_PROP_FRAME_COUNT))
        duration = total_frames / fps if fps > 0 else 0

        print(f"  Duration: {duration:.1f}s | FPS: {fps:.1f} | Total frames: {total_frames}")

        frames = []
        frame_indices = []
        prev_frame = None
        frame_idx = 0

        # Skip first/last 5%
        start_skip = int(total_frames * 0.05)
        end_skip = int(total_frames * 0.95)

        print(f"  Sampling with scene detection (range: {start_skip}-{end_skip})")

        while cap.isOpened():
            ret, frame = cap.read()
            if not ret:
                break

            if start_skip <= frame_idx <= end_skip:
                # Skip blank frames
                if not self._is_blank_frame(frame):
                    # Include if: (1) interval match, OR (2) scene change detected
                    include_frame = False

                    if frame_idx % interval == 0:
                        include_frame = True
                    elif prev_frame is not None and self.is_scene_change(prev_frame, frame):
                        include_frame = True

                    if include_frame:
                        frames.append(frame.copy())
                        frame_indices.append(frame_idx)
                        prev_frame = frame.copy()

            frame_idx += 1

        cap.release()
        print(f"✅ Extracted {len(frames)} candidates (including scene changes)")
        return frames, frame_indices

    def _is_blank_frame(self, frame: np.ndarray, threshold: float = 10.0) -> bool:
        """Check if frame is blank/black"""
        gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
        return gray.std() < threshold

    def filter_for_diversity(
        self,
        ranked_frames: List[Tuple[int, Dict]],
        candidates: List[np.ndarray],
        num_frames: int,
        verbose: bool = True
    ) -> List[Tuple[int, Dict]]:
        """
        Multi-stage diversity filtering:
        1. Select highest quality frames
        2. Check color diversity
        3. Check structural diversity
        4. Ensure no repetitive content
        """
        if verbose:
            print("\n" + "="*70)
            print("🎨 ULTIMATE DIVERSITY FILTERING")
            print("="*70)

        selected = []
        selected_frames = []
        skipped = []

        for frame_idx, scores in ranked_frames:
            if len(selected) >= num_frames:
                break

            frame = candidates[frame_idx]
            is_diverse, reason = self.is_diverse_enough(frame, selected_frames, verbose)

            if is_diverse:
                selected.append((frame_idx, scores))
                selected_frames.append(frame)

                if verbose:
                    print(f"  ✅ Frame #{len(selected)}: Quality={scores['total']:.1f} | {reason}")
            else:
                skipped.append((frame_idx, scores, reason))
                if verbose:
                    print(f"  ❌ REJECTED: Quality={scores['total']:.1f} | {reason}")

        # Fallback: If not enough diverse frames, lower thresholds
        if len(selected) < num_frames:
            if verbose:
                print(f"\n  ⚠️  Only {len(selected)} diverse frames, lowering thresholds...")

            original_hist = self.histogram_threshold
            original_struct = self.structural_threshold

            self.histogram_threshold = 0.75
            self.structural_threshold = 0.80

            for frame_idx, scores, prev_reason in skipped:
                if len(selected) >= num_frames:
                    break

                frame = candidates[frame_idx]
                is_diverse, reason = self.is_diverse_enough(frame, selected_frames)

                if is_diverse:
                    selected.append((frame_idx, scores))
                    selected_frames.append(frame)
                    if verbose:
                        print(f"  ➕ Frame #{len(selected)}: Quality={scores['total']:.1f} | Acceptable")

            self.histogram_threshold = original_hist
            self.structural_threshold = original_struct

        if verbose:
            print(f"\n✨ Selected {len(selected)} unique frames | Rejected {len(skipped)} similar frames")

        return selected

    def resize_frame(self, frame: np.ndarray) -> np.ndarray:
        """Resize frame maintaining aspect ratio"""
        height, width = frame.shape[:2]
        if max(height, width) <= MAX_IMAGE_DIMENSION:
            return frame

        if width > height:
            new_width = MAX_IMAGE_DIMENSION
            new_height = int(height * (MAX_IMAGE_DIMENSION / width))
        else:
            new_height = MAX_IMAGE_DIMENSION
            new_width = int(width * (MAX_IMAGE_DIMENSION / height))

        return cv2.resize(frame, (new_width, new_height), interpolation=cv2.INTER_LANCZOS4)

    def save_frame(
        self,
        frame: np.ndarray,
        video_hash: str,
        frame_number: int
    ) -> Path:
        """Save frame as high-quality JPEG"""
        frame = self.resize_frame(frame)
        rgb_frame = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
        pil_image = Image.fromarray(rgb_frame)

        timestamp = int(time.time() * 1000)
        filename = f"{video_hash}_frame_{frame_number:03d}_{timestamp}.jpg"
        output_path = OUTPUT_DIR / filename

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
        ULTIMATE EXTRACTION:
        1. Extract candidates with scene change detection
        2. Score using CLIP + quality metrics
        3. Multi-metric diversity filtering
        4. Save best unique frames
        """
        start_time = time.time()
        num_frames = max(MIN_FRAMES, min(MAX_FRAMES, num_frames))

        is_valid, error_msg = self.validate_video(video_path)
        if not is_valid:
            return {"success": False, "error": error_msg, "frames": [], "scores": []}

        try:
            # STEP 1: Extract candidates with scene detection
            if verbose:
                print("\n" + "="*70)
                print("🎬 STEP 1: Smart Frame Extraction (Scene Detection)")
                print("="*70)

            candidates, frame_indices = self.extract_candidate_frames(video_path)

            if len(candidates) < num_frames:
                print(f"⚠️  Only {len(candidates)} frames available")
                num_frames = len(candidates)

            # STEP 2: Score frames (request 3x for diversity filtering)
            if verbose:
                print("\n" + "="*70)
                print("🎯 STEP 2: AI Quality Scoring")
                print("="*70)

            num_to_score = min(len(candidates), num_frames * 3)
            ranked_frames = self.scorer.rank_frames(
                candidates,
                num_best=num_to_score,
                verbose=verbose
            )

            # STEP 3: Ultimate diversity filtering
            diverse_frames = self.filter_for_diversity(
                ranked_frames,
                candidates,
                num_frames,
                verbose=verbose
            )

            # STEP 4: Save frames
            if verbose:
                print("\n" + "="*70)
                print("💾 STEP 3: Saving Ultimate Frames")
                print("="*70)

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
                    print(f"  ✅ Frame #{rank}: {output_path.name} (score: {scores['total']:.1f})")

            elapsed = time.time() - start_time

            # Metadata
            cap = cv2.VideoCapture(str(video_path))
            metadata = {
                "duration_seconds": round(cap.get(cv2.CAP_PROP_FRAME_COUNT) / cap.get(cv2.CAP_PROP_FPS), 2),
                "fps": round(cap.get(cv2.CAP_PROP_FPS), 2),
                "width": int(cap.get(cv2.CAP_PROP_FRAME_WIDTH)),
                "height": int(cap.get(cv2.CAP_PROP_FRAME_HEIGHT)),
                "total_frames": int(cap.get(cv2.CAP_PROP_FRAME_COUNT)),
                "candidates_evaluated": len(candidates),
                "frames_extracted": len(diverse_frames),
                "processing_time_seconds": round(elapsed, 2)
            }
            cap.release()

            if verbose:
                print("\n" + "="*70)
                print(f"✨ ULTIMATE SUCCESS! {len(diverse_frames)} perfect frames in {elapsed:.1f}s")
                print("="*70)

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


# Singleton
_extractor_instance = None

def get_extractor() -> UltimateFrameExtractor:
    """Get singleton instance"""
    global _extractor_instance
    if _extractor_instance is None:
        _extractor_instance = UltimateFrameExtractor()
    return _extractor_instance
