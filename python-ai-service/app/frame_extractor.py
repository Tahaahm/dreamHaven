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
    """Simple, fast frame extraction"""

    def __init__(self):
        """Initialize"""
        self.scorer = get_scorer()
        # Simple threshold - only block VERY similar frames
        self.similarity_threshold = 0.90  # Only block 90%+ similar

    def validate_video(self, video_path: Path) -> Tuple[bool, Optional[str]]:
        """Validate video"""
        if not video_path.exists():
            return False, "Video not found"
        if not video_path.is_file():
            return False, "Not a file"

        cap = cv2.VideoCapture(str(video_path))
        if not cap.isOpened():
            return False, "Cannot open video"

        fps = cap.get(cv2.CAP_PROP_FPS)
        frame_count = int(cap.get(cv2.CAP_PROP_FRAME_COUNT))

        if fps == 0:
            cap.release()
            return False, "Invalid FPS"

        duration = frame_count / fps
        if duration > MAX_VIDEO_DURATION_SECONDS:
            cap.release()
            return False, f"Too long (max {MAX_VIDEO_DURATION_SECONDS}s)"

        cap.release()
        return True, None

    def calculate_similarity(self, frame1: np.ndarray, frame2: np.ndarray) -> float:
        """
        SIMPLE histogram comparison - FAST!
        Returns: 0-1 (1 = identical)
        """
        # Small resize for speed
        h1 = cv2.resize(frame1, (128, 128))
        h2 = cv2.resize(frame2, (128, 128))

        # Simple HSV histogram
        hsv1 = cv2.cvtColor(h1, cv2.COLOR_BGR2HSV)
        hsv2 = cv2.cvtColor(h2, cv2.COLOR_BGR2HSV)

        hist1 = cv2.calcHist([hsv1], [0, 1], None, [30, 32], [0, 180, 0, 256])
        hist2 = cv2.calcHist([hsv2], [0, 1], None, [30, 32], [0, 180, 0, 256])

        cv2.normalize(hist1, hist1, 0, 1, cv2.NORM_MINMAX)
        cv2.normalize(hist2, hist2, 0, 1, cv2.NORM_MINMAX)

        return cv2.compareHist(hist1, hist2, cv2.HISTCMP_CORREL)

    def is_diverse(self, new_frame: np.ndarray, selected: List[np.ndarray]) -> bool:
        """
        Simple diversity check - only block VERY similar frames
        """
        if not selected:
            return True

        for existing in selected:
            sim = self.calculate_similarity(new_frame, existing)
            if sim > self.similarity_threshold:
                return False  # Too similar

        return True

    def extract_candidate_frames(self, video_path: Path, interval: int = FRAME_SAMPLE_INTERVAL) -> List[np.ndarray]:
        """Extract frames at intervals"""
        print(f"\n📹 Opening: {video_path.name}")

        cap = cv2.VideoCapture(str(video_path))
        if not cap.isOpened():
            raise ValueError("Cannot open video")

        fps = cap.get(cv2.CAP_PROP_FPS)
        total = int(cap.get(cv2.CAP_PROP_FRAME_COUNT))
        duration = total / fps if fps > 0 else 0

        print(f"  {duration:.1f}s | {fps:.1f}fps | {total} frames")

        frames = []
        idx = 0
        start = int(total * 0.05)
        end = int(total * 0.95)

        print(f"  Sampling {start}-{end}")

        while cap.isOpened():
            ret, frame = cap.read()
            if not ret:
                break

            if start <= idx <= end and idx % interval == 0:
                gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
                if gray.std() > 10:  # Not blank
                    frames.append(frame)

            idx += 1

        cap.release()
        print(f"✅ {len(frames)} candidates")
        return frames

    def filter_diverse(
        self,
        ranked: List[Tuple[int, Dict]],
        candidates: List[np.ndarray],
        num: int,
        verbose: bool = True
    ) -> List[Tuple[int, Dict]]:
        """Simple diversity filter"""
        if verbose:
            print("\n" + "="*60)
            print("🎨 DIVERSITY FILTER")
            print("="*60)

        selected = []
        selected_frames = []

        for frame_idx, scores in ranked:
            if len(selected) >= num:
                break

            frame = candidates[frame_idx]

            if self.is_diverse(frame, selected_frames):
                selected.append((frame_idx, scores))
                selected_frames.append(frame)
                if verbose:
                    print(f"  ✅ #{len(selected)}: Q={scores['total']:.1f}")
            elif verbose:
                print(f"  ❌ Skip: Q={scores['total']:.1f} (duplicate)")

        # If not enough, lower threshold
        if len(selected) < num:
            if verbose:
                print(f"\n  ⚠️ Only {len(selected)}, lowering threshold...")

            orig = self.similarity_threshold
            self.similarity_threshold = 0.85

            for frame_idx, scores in ranked:
                if len(selected) >= num:
                    break
                if any(i == frame_idx for i, _ in selected):
                    continue

                frame = candidates[frame_idx]
                if self.is_diverse(frame, selected_frames):
                    selected.append((frame_idx, scores))
                    selected_frames.append(frame)
                    if verbose:
                        print(f"  ➕ #{len(selected)}: Q={scores['total']:.1f}")

            self.similarity_threshold = orig

        if verbose:
            print(f"\n✨ Selected {len(selected)} frames")

        return selected

    def resize_frame(self, frame: np.ndarray) -> np.ndarray:
        """Resize frame"""
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
        """Save frame"""
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
        Extract best frames - SIMPLE & FAST
        """
        start = time.time()
        num_frames = max(MIN_FRAMES, min(MAX_FRAMES, num_frames))

        is_valid, err = self.validate_video(video_path)
        if not is_valid:
            return {"success": False, "error": err, "frames": [], "scores": []}

        try:
            # STEP 1: Extract candidates
            if verbose:
                print("\n" + "="*60)
                print("🎬 STEP 1: Extract Candidates")
                print("="*60)

            candidates = self.extract_candidate_frames(video_path)

            if len(candidates) < num_frames:
                num_frames = len(candidates)

            # STEP 2: Score (2x for diversity)
            if verbose:
                print("\n" + "="*60)
                print("🎯 STEP 2: AI Scoring")
                print("="*60)

            num_to_score = min(len(candidates), num_frames * 2)
            ranked = self.scorer.rank_frames(candidates, num_best=num_to_score, verbose=verbose)

            # STEP 3: Diversity filter
            diverse = self.filter_diverse(ranked, candidates, num_frames, verbose)

            # STEP 4: Save
            if verbose:
                print("\n" + "="*60)
                print("💾 STEP 3: Saving")
                print("="*60)

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
                    print(f"  ✅ #{rank}: {path.name}")

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
                print("\n" + "="*60)
                print(f"✨ SUCCESS! {len(diverse)} frames in {elapsed:.1f}s")
                print("="*60)

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
