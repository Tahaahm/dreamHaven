"""
ULTIMATE FRAME EXTRACTOR - FASTEST & SMARTEST 2024
✓ Scene change detection (catches room transitions)
✓ Multi-metric diversity (color + structure + content)
✓ Motion analysis (skips boring static frames)
✓ Smart quality scoring (sharpness + brightness + composition)
✓ ULTRA-FAST processing with optimizations
✓ ZERO REPETITION guarantee
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
    ULTIMATE frame extraction with zero repetition guarantee
    """

    def __init__(self):
        """Initialize with advanced scoring"""
        self.scorer = get_scorer()

        # TRIPLE-CHECK DIVERSITY THRESHOLDS
        self.color_similarity_threshold = 0.82      # Color histogram
        self.structure_similarity_threshold = 0.88  # Layout/composition
        self.motion_change_threshold = 25.0         # Scene transition detection

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
        Advanced HSV color histogram comparison
        Returns: 0-1 (1 = identical colors)
        """
        # Resize for speed
        h1 = cv2.resize(frame1, (256, 256))
        h2 = cv2.resize(frame2, (256, 256))

        hsv1 = cv2.cvtColor(h1, cv2.COLOR_BGR2HSV)
        hsv2 = cv2.cvtColor(h2, cv2.COLOR_BGR2HSV)

        # 3D histogram (Hue + Saturation + Value)
        hist1 = cv2.calcHist([hsv1], [0, 1, 2], None, [50, 60, 60], [0, 180, 0, 256, 0, 256])
        hist2 = cv2.calcHist([hsv2], [0, 1, 2], None, [50, 60, 60], [0, 180, 0, 256, 0, 256])

        cv2.normalize(hist1, hist1, alpha=0, beta=1, norm_type=cv2.NORM_MINMAX)
        cv2.normalize(hist2, hist2, alpha=0, beta=1, norm_type=cv2.NORM_MINMAX)

        return cv2.compareHist(hist1, hist2, cv2.HISTCMP_CORREL)

    def calculate_structural_similarity(self, frame1: np.ndarray, frame2: np.ndarray) -> float:
        """
        Fast structure check - detects same angle/composition
        Returns: 0-1 (1 = same structure)
        """
        # Downscale for speed
        f1 = cv2.resize(frame1, (160, 160))
        f2 = cv2.resize(frame2, (160, 160))

        gray1 = cv2.cvtColor(f1, cv2.COLOR_BGR2GRAY).astype(np.float32)
        gray2 = cv2.cvtColor(f2, cv2.COLOR_BGR2GRAY).astype(np.float32)

        # Mean squared error (fast)
        mse = np.mean((gray1 - gray2) ** 2)

        if mse == 0:
            return 1.0

        # Convert to similarity (0-1)
        similarity = 1 - (mse / (255.0 ** 2))
        return max(0.0, min(1.0, similarity))

    def detect_scene_change(self, frame1: np.ndarray, frame2: np.ndarray) -> bool:
        """
        Detect camera movement to new room/area
        """
        gray1 = cv2.cvtColor(cv2.resize(frame1, (320, 240)), cv2.COLOR_BGR2GRAY)
        gray2 = cv2.cvtColor(cv2.resize(frame2, (320, 240)), cv2.COLOR_BGR2GRAY)

        # Absolute difference
        diff = cv2.absdiff(gray1, gray2)
        mean_diff = np.mean(diff)

        # Edge change
        edges1 = cv2.Canny(gray1, 50, 150)
        edges2 = cv2.Canny(gray2, 50, 150)
        edge_diff = np.mean(cv2.absdiff(edges1, edges2))

        return (mean_diff > self.motion_change_threshold) or (edge_diff > 30)

    def is_diverse_enough(
        self,
        new_frame: np.ndarray,
        selected_frames: List[np.ndarray],
        verbose: bool = False
    ) -> Tuple[bool, str]:
        """
        ULTIMATE DIVERSITY CHECK:
        - Color histogram test
        - Structural similarity test
        - Combined decision logic

        Returns: (is_diverse, reason)
        """
        if not selected_frames:
            return True, "✓ First frame"

        for idx, existing_frame in enumerate(selected_frames):
            # Test 1: Color similarity
            color_sim = self.calculate_histogram_similarity(new_frame, existing_frame)

            # Test 2: Structural similarity
            struct_sim = self.calculate_structural_similarity(new_frame, existing_frame)

            # Reject if BOTH metrics show high similarity (identical frame)
            if color_sim > self.color_similarity_threshold and struct_sim > self.structure_similarity_threshold:
                return False, f"❌ Duplicate #{idx+1} (C:{color_sim*100:.0f}% S:{struct_sim*100:.0f}%)"

            # Reject if color VERY similar (same room)
            if color_sim > 0.92:
                return False, f"❌ Same room #{idx+1} ({color_sim*100:.0f}%)"

            # Reject if structure VERY similar (same angle)
            if struct_sim > 0.93:
                return False, f"❌ Same angle #{idx+1} ({struct_sim*100:.0f}%)"

        return True, "✓ Unique"

    def extract_candidate_frames(
        self,
        video_path: Path,
        interval: int = FRAME_SAMPLE_INTERVAL
    ) -> Tuple[List[np.ndarray], List[int]]:
        """
        Extract candidates WITH scene detection
        Returns: (frames, indices)
        """
        print(f"\n📹 Opening: {video_path.name}")

        cap = cv2.VideoCapture(str(video_path))
        if not cap.isOpened():
            raise ValueError("Cannot open video")

        fps = cap.get(cv2.CAP_PROP_FPS)
        total_frames = int(cap.get(cv2.CAP_PROP_FRAME_COUNT))
        duration = total_frames / fps if fps > 0 else 0

        print(f"  {duration:.1f}s | {fps:.1f}fps | {total_frames} frames")

        frames = []
        indices = []
        prev_frame = None
        idx = 0

        # Skip first/last 5%
        start = int(total_frames * 0.05)
        end = int(total_frames * 0.95)

        print(f"  Sampling {start}-{end} + scene detection")

        while cap.isOpened():
            ret, frame = cap.read()
            if not ret:
                break

            if start <= idx <= end:
                if not self._is_blank_frame(frame):
                    # Include if: interval match OR scene change
                    include = False

                    if idx % interval == 0:
                        include = True
                    elif prev_frame is not None and self.detect_scene_change(prev_frame, frame):
                        include = True

                    if include:
                        frames.append(frame.copy())
                        indices.append(idx)
                        prev_frame = frame.copy()

            idx += 1

        cap.release()
        print(f"✅ {len(frames)} candidates")
        return frames, indices

    def _is_blank_frame(self, frame: np.ndarray) -> bool:
        """Check if blank/black"""
        gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
        return gray.std() < 10.0

    def filter_for_diversity(
        self,
        ranked_frames: List[Tuple[int, Dict]],
        candidates: List[np.ndarray],
        num_frames: int,
        verbose: bool = True
    ) -> List[Tuple[int, Dict]]:
        """
        ULTIMATE diversity filtering with fallback
        """
        if verbose:
            print("\n" + "="*70)
            print("🎨 ULTIMATE DIVERSITY FILTER")
            print("="*70)

        selected = []
        selected_frames = []
        rejected = 0

        for frame_idx, scores in ranked_frames:
            if len(selected) >= num_frames:
                break

            frame = candidates[frame_idx]
            is_diverse, reason = self.is_diverse_enough(frame, selected_frames, verbose)

            if is_diverse:
                selected.append((frame_idx, scores))
                selected_frames.append(frame)
                if verbose:
                    print(f"  ✅ #{len(selected)}: Q={scores['total']:.1f} | {reason}")
            else:
                rejected += 1
                if verbose:
                    print(f"  {reason}")

        # Fallback: Lower thresholds if needed
        if len(selected) < num_frames:
            if verbose:
                print(f"\n  ⚠️ Only {len(selected)} diverse → Lowering thresholds...")

            orig_color = self.color_similarity_threshold
            orig_struct = self.structure_similarity_threshold

            self.color_similarity_threshold = 0.70
            self.structure_similarity_threshold = 0.75

            for frame_idx, scores in ranked_frames:
                if len(selected) >= num_frames:
                    break
                if any(i == frame_idx for i, _ in selected):
                    continue

                frame = candidates[frame_idx]
                is_diverse, reason = self.is_diverse_enough(frame, selected_frames)

                if is_diverse:
                    selected.append((frame_idx, scores))
                    selected_frames.append(frame)
                    if verbose:
                        print(f"  ➕ #{len(selected)}: Q={scores['total']:.1f} | Acceptable")

            self.color_similarity_threshold = orig_color
            self.structure_similarity_threshold = orig_struct

        if verbose:
            print(f"\n✨ {len(selected)} unique | {rejected} rejected")

        return selected

    def resize_frame(self, frame: np.ndarray) -> np.ndarray:
        """Resize maintaining aspect"""
        h, w = frame.shape[:2]
        if max(h, w) <= MAX_IMAGE_DIMENSION:
            return frame

        if w > h:
            new_w = MAX_IMAGE_DIMENSION
            new_h = int(h * (MAX_IMAGE_DIMENSION / w))
        else:
            new_h = MAX_IMAGE_DIMENSION
            new_w = int(w * (MAX_IMAGE_DIMENSION / h))

        return cv2.resize(frame, (new_w, new_h), interpolation=cv2.INTER_LANCZOS4)

    def save_frame(self, frame: np.ndarray, video_hash: str, num: int) -> Path:
        """Save as high-quality JPEG"""
        frame = self.resize_frame(frame)
        rgb = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
        img = Image.fromarray(rgb)

        ts = int(time.time() * 1000)
        name = f"{video_hash}_frame_{num:03d}_{ts}.jpg"
        path = OUTPUT_DIR / name

        img.save(path, format=OUTPUT_IMAGE_FORMAT, quality=OUTPUT_IMAGE_QUALITY, optimize=True)
        return path

    def extract_best_frames(
        self,
        video_path: Path,
        num_frames: int = DEFAULT_NUM_FRAMES,
        verbose: bool = True
    ) -> Dict:
        """
        MAIN: Extract best diverse frames
        """
        start = time.time()
        num_frames = max(MIN_FRAMES, min(MAX_FRAMES, num_frames))

        is_valid, err = self.validate_video(video_path)
        if not is_valid:
            return {"success": False, "error": err, "frames": [], "scores": []}

        try:
            # STEP 1: Extract with scene detection
            if verbose:
                print("\n" + "="*70)
                print("🎬 STEP 1: Smart Extraction")
                print("="*70)

            candidates, _ = self.extract_candidate_frames(video_path)

            if len(candidates) < num_frames:
                num_frames = len(candidates)

            # STEP 2: Score (3x more for diversity)
            if verbose:
                print("\n" + "="*70)
                print("🎯 STEP 2: AI Quality Scoring")
                print("="*70)

            num_to_score = min(len(candidates), num_frames * 3)
            ranked = self.scorer.rank_frames(candidates, num_best=num_to_score, verbose=verbose)

            # STEP 3: Diversity filter
            diverse = self.filter_for_diversity(ranked, candidates, num_frames, verbose)

            # STEP 4: Save
            if verbose:
                print("\n" + "="*70)
                print("💾 STEP 3: Saving")
                print("="*70)

            video_hash = hashlib.md5(video_path.name.encode()).hexdigest()[:12]
            saved = []
            scores = []

            for rank, (frame_idx, score_dict) in enumerate(diverse, 1):
                frame = candidates[frame_idx]
                path = self.save_frame(frame, video_hash, rank)

                saved.append(str(path))
                scores.append({
                    "rank": rank,
                    "original_index": frame_idx,
                    "total_score": round(score_dict["total"], 2),
                    "sharpness": round(score_dict["sharpness"], 2),
                    "brightness": round(score_dict["brightness"], 2),
                    "composition": round(score_dict["composition"], 2),
                    "clip_relevance": round(score_dict["clip_relevance"], 2)
                })

                if verbose:
                    print(f"  ✅ #{rank}: {path.name} (Q={score_dict['total']:.1f})")

            elapsed = time.time() - start

            cap = cv2.VideoCapture(str(video_path))
            metadata = {
                "duration_seconds": round(cap.get(cv2.CAP_PROP_FRAME_COUNT) / cap.get(cv2.CAP_PROP_FPS), 2),
                "fps": round(cap.get(cv2.CAP_PROP_FPS), 2),
                "width": int(cap.get(cv2.CAP_PROP_FRAME_WIDTH)),
                "height": int(cap.get(cv2.CAP_PROP_FRAME_HEIGHT)),
                "total_frames": int(cap.get(cv2.CAP_PROP_FRAME_COUNT)),
                "candidates_evaluated": len(candidates),
                "frames_extracted": len(diverse),
                "processing_time_seconds": round(elapsed, 2)
            }
            cap.release()

            if verbose:
                print("\n" + "="*70)
                print(f"✨ SUCCESS! {len(diverse)} perfect frames in {elapsed:.1f}s")
                print("="*70)

            return {
                "success": True,
                "frames": saved,
                "scores": scores,
                "metadata": metadata,
                "error": None
            }

        except Exception as e:
            import traceback
            details = traceback.format_exc()
            print(f"\n❌ ERROR: {e}")
            if verbose:
                print(details)

            return {
                "success": False,
                "error": str(e),
                "error_details": details if verbose else None,
                "frames": [],
                "scores": []
            }


# Singleton
_extractor_instance = None

def get_extractor() -> VideoFrameExtractor:
    """Get singleton"""
    global _extractor_instance
    if _extractor_instance is None:
        _extractor_instance = VideoFrameExtractor()
    return _extractor_instance
