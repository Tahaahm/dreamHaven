"""
Frame Extractor Module - Real Estate Optimized
Extracts diverse, high-quality frames with priority on exterior/front-of-house shots
"""

import cv2
import numpy as np
from pathlib import Path
from typing import List, Dict, Tuple, Optional
from PIL import Image
import hashlib
import time
from concurrent.futures import ThreadPoolExecutor, as_completed

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


# ─── Tuneable constants ──────────────────────────────────────────────────────
SCENE_CHANGE_THRESHOLD  = 30.0   # Mean abs-diff between frames to call a scene cut
SSIM_WEIGHT             = 0.5    # Weight for SSIM in combined similarity score
HIST_WEIGHT             = 0.5    # Weight for histogram in combined similarity score
SIMILARITY_HARD_LIMIT   = 0.88   # Combined similarity above this → reject frame
SIMILARITY_SOFT_LIMIT   = 0.75   # Fallback threshold when not enough frames found
EXTERIOR_SCORE_BOOST    = 15.0   # Bonus added to quality score for exterior shots
COVERAGE_BUCKETS        = 4      # Divide video into N temporal buckets for coverage
MAX_SCORING_WORKERS     = 4      # Threads for parallel frame scoring
# ─────────────────────────────────────────────────────────────────────────────


class VideoFrameExtractor:
    """
    Extract and rank frames from real estate video files.

    Strategy
    --------
    1. Scene-change detection   – sample at intervals AND cut on abrupt changes
    2. Exterior boosting        – heuristic detects sky / wide outdoor shots
    3. Coverage guarantee       – at least one frame from each temporal bucket
    4. Parallel quality scoring – ThreadPoolExecutor for speed
    5. Dual-metric diversity    – SSIM + histogram combined similarity check
    """

    def __init__(self):
        self.scorer = get_scorer()
        self.similarity_threshold = SIMILARITY_HARD_LIMIT

    # ──────────────────────────────────────────────────────────────────────────
    # Video validation
    # ──────────────────────────────────────────────────────────────────────────

    def validate_video(self, video_path: Path) -> Tuple[bool, Optional[str]]:
        if not video_path.exists():
            return False, "Video file not found"
        if not video_path.is_file():
            return False, "Path is not a file"

        cap = cv2.VideoCapture(str(video_path))
        if not cap.isOpened():
            return False, "Cannot open video file – may be corrupted or unsupported format"

        fps         = cap.get(cv2.CAP_PROP_FPS)
        frame_count = int(cap.get(cv2.CAP_PROP_FRAME_COUNT))

        if fps == 0:
            cap.release()
            return False, "Invalid video – FPS is zero"

        duration = frame_count / fps
        if duration > MAX_VIDEO_DURATION_SECONDS:
            cap.release()
            return False, (
                f"Video too long (max {MAX_VIDEO_DURATION_SECONDS}s, got {duration:.0f}s)"
            )

        cap.release()
        return True, None

    # ──────────────────────────────────────────────────────────────────────────
    # Exterior / front-of-house detection
    # ──────────────────────────────────────────────────────────────────────────

    def _exterior_score(self, frame: np.ndarray) -> float:
        """
        Heuristic score (0–1) indicating how likely a frame is an exterior shot.

        Signals used
        ~~~~~~~~~~~~
        • Sky-blue pixel ratio in the upper quarter
        • Overall brightness (exterior daylight is brighter)
        • Colour saturation (outdoor scenes are more saturated)
        • Low dark-pixel ratio (interiors are often darker)
        """
        h, w = frame.shape[:2]
        hsv   = cv2.cvtColor(frame, cv2.COLOR_BGR2HSV)

        # 1. Sky detection – upper 25 % of frame, blue/cyan hues
        upper = hsv[: h // 4, :, :]
        # Sky hue: 90–130 (blue), saturation > 30, value > 80
        sky_mask = (
            (upper[:, :, 0] >= 90) & (upper[:, :, 0] <= 130) &
            (upper[:, :, 1] > 30)  &
            (upper[:, :, 2] > 80)
        )
        sky_ratio = sky_mask.sum() / (upper.shape[0] * upper.shape[1] + 1e-6)

        # 2. Brightness
        brightness = hsv[:, :, 2].mean() / 255.0

        # 3. Saturation
        saturation = hsv[:, :, 1].mean() / 255.0

        # 4. Dark-pixel ratio (interiors have more shadows)
        dark_ratio = (hsv[:, :, 2] < 50).sum() / (h * w + 1e-6)

        # Weighted combination
        ext_score = (
            sky_ratio    * 0.40 +
            brightness   * 0.25 +
            saturation   * 0.20 +
            (1 - dark_ratio) * 0.15
        )
        return float(np.clip(ext_score, 0.0, 1.0))

    # ──────────────────────────────────────────────────────────────────────────
    # Scene-change detection
    # ──────────────────────────────────────────────────────────────────────────

    def _is_scene_change(
        self, prev_gray: np.ndarray, curr_gray: np.ndarray
    ) -> bool:
        """True when mean absolute pixel difference exceeds threshold."""
        if prev_gray is None:
            return False
        diff = cv2.absdiff(prev_gray, curr_gray)
        return float(diff.mean()) > SCENE_CHANGE_THRESHOLD

    # ──────────────────────────────────────────────────────────────────────────
    # Blank-frame filter
    # ──────────────────────────────────────────────────────────────────────────

    def _is_blank_frame(self, frame: np.ndarray, threshold: float = 10.0) -> bool:
        gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
        return float(gray.std()) < threshold

    # ──────────────────────────────────────────────────────────────────────────
    # Candidate extraction  (interval + scene cuts)
    # ──────────────────────────────────────────────────────────────────────────

    def extract_candidate_frames(
        self,
        video_path: Path,
        interval: int = FRAME_SAMPLE_INTERVAL,
    ) -> Tuple[List[np.ndarray], List[int]]:
        """
        Return (frames, frame_indices).

        Samples every `interval` frames AND captures frames at scene cuts so
        we never miss the moment the camera first shows the exterior.
        """
        print(f"\n📹 Opening video: {video_path.name}")
        cap = cv2.VideoCapture(str(video_path))
        if not cap.isOpened():
            raise ValueError("Cannot open video file")

        fps          = cap.get(cv2.CAP_PROP_FPS)
        total_frames = int(cap.get(cv2.CAP_PROP_FRAME_COUNT))
        duration     = total_frames / fps if fps > 0 else 0

        print(f"  Duration: {duration:.1f}s | FPS: {fps:.1f} | Total frames: {total_frames}")

        # Skip first 3 % and last 3 % (real estate videos often end with logo cards)
        start_skip = max(0, int(total_frames * 0.03))
        end_skip   = min(total_frames, int(total_frames * 0.97))

        print(f"  Sampling frames {start_skip}–{end_skip} (interval={interval}, scene-cuts enabled)")

        frames: List[np.ndarray] = []
        indices: List[int]       = []
        prev_gray                = None
        frame_idx                = 0

        while cap.isOpened():
            ret, frame = cap.read()
            if not ret:
                break

            if start_skip <= frame_idx <= end_skip:
                curr_gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)

                take_interval     = (frame_idx % interval == 0)
                take_scene_change = self._is_scene_change(prev_gray, curr_gray)

                if (take_interval or take_scene_change) and not self._is_blank_frame(frame):
                    frames.append(frame)
                    indices.append(frame_idx)

                prev_gray = curr_gray

            frame_idx += 1

        cap.release()
        print(f"✅ Extracted {len(frames)} candidate frames (interval + scene cuts)")
        return frames, indices

    # ──────────────────────────────────────────────────────────────────────────
    # Parallel quality scoring
    # ──────────────────────────────────────────────────────────────────────────

    def _score_single_frame(
        self, idx: int, frame: np.ndarray
    ) -> Tuple[int, Dict]:
        """Score one frame; called from a worker thread."""
        scores = self.scorer.score_frame(frame)
        ext    = self._exterior_score(frame)
        scores["exterior"]  = round(ext * 100, 2)
        scores["total"]     = round(scores["total"] + ext * EXTERIOR_SCORE_BOOST, 2)
        return idx, scores

    def score_frames_parallel(
        self,
        candidates: List[np.ndarray],
        num_best: int,
        verbose: bool = True,
    ) -> List[Tuple[int, Dict]]:
        """
        Score all candidates in parallel, return top `num_best` sorted by total score.
        """
        if verbose:
            print(f"  Scoring {len(candidates)} frames with {MAX_SCORING_WORKERS} workers…")

        results: List[Tuple[int, Dict]] = []

        with ThreadPoolExecutor(max_workers=MAX_SCORING_WORKERS) as pool:
            futures = {
                pool.submit(self._score_single_frame, i, f): i
                for i, f in enumerate(candidates)
            }
            for future in as_completed(futures):
                try:
                    results.append(future.result())
                except Exception as exc:
                    print(f"  ⚠️  Frame {futures[future]} scoring failed: {exc}")

        results.sort(key=lambda x: x[1]["total"], reverse=True)
        return results[:num_best]

    # ──────────────────────────────────────────────────────────────────────────
    # Dual-metric diversity (SSIM + histogram)
    # ──────────────────────────────────────────────────────────────────────────

    def _combined_similarity(
        self, f1: np.ndarray, f2: np.ndarray, size: int = 128
    ) -> float:
        """
        Return combined similarity score in [0, 1].
        Higher = more similar.
        """
        # Resize to small thumbnail for speed
        t1 = cv2.resize(f1, (size, size))
        t2 = cv2.resize(f2, (size, size))

        # --- Histogram similarity ---
        hsv1 = cv2.cvtColor(t1, cv2.COLOR_BGR2HSV)
        hsv2 = cv2.cvtColor(t2, cv2.COLOR_BGR2HSV)
        h1   = cv2.calcHist([hsv1], [0, 1], None, [50, 60], [0, 180, 0, 256])
        h2   = cv2.calcHist([hsv2], [0, 1], None, [50, 60], [0, 180, 0, 256])
        cv2.normalize(h1, h1, 0, 1, cv2.NORM_MINMAX)
        cv2.normalize(h2, h2, 0, 1, cv2.NORM_MINMAX)
        hist_sim = float(cv2.compareHist(h1, h2, cv2.HISTCMP_CORREL))
        hist_sim = max(0.0, hist_sim)   # CORREL can be negative

        # --- Structural similarity (grayscale) ---
        g1 = cv2.cvtColor(t1, cv2.COLOR_BGR2GRAY).astype(np.float32)
        g2 = cv2.cvtColor(t2, cv2.COLOR_BGR2GRAY).astype(np.float32)

        mu1, mu2   = g1.mean(), g2.mean()
        sig1, sig2 = g1.std(), g2.std()
        sig12      = float(((g1 - mu1) * (g2 - mu2)).mean())
        C1, C2     = 6.5025, 58.5225   # (0.01*255)^2, (0.03*255)^2
        ssim_val   = (
            (2 * mu1 * mu2 + C1) * (2 * sig12 + C2) /
            ((mu1**2 + mu2**2 + C1) * (sig1**2 + sig2**2 + C2))
        )
        ssim_val   = float(np.clip(ssim_val, 0.0, 1.0))

        return HIST_WEIGHT * hist_sim + SSIM_WEIGHT * ssim_val

    def is_diverse_enough(
        self, new_frame: np.ndarray, selected_frames: List[np.ndarray],
        threshold: Optional[float] = None
    ) -> bool:
        if not selected_frames:
            return True
        thr = threshold or self.similarity_threshold
        for existing in selected_frames:
            if self._combined_similarity(new_frame, existing) > thr:
                return False
        return True

    # ──────────────────────────────────────────────────────────────────────────
    # Coverage guarantee across temporal buckets
    # ──────────────────────────────────────────────────────────────────────────

    def _ensure_coverage(
        self,
        ranked: List[Tuple[int, Dict]],
        candidates: List[np.ndarray],
        total_candidates: int,
        num_frames: int,
    ) -> List[Tuple[int, Dict]]:
        """
        Re-order ranked list so the first `COVERAGE_BUCKETS` slots are the
        best frame from each temporal bucket, then fill the rest by quality.

        This guarantees at least one exterior/arrival shot is included even
        if the drive-up footage isn't the highest-scoring section.
        """
        bucket_size = max(1, total_candidates // COVERAGE_BUCKETS)
        bucket_reps: List[Optional[Tuple[int, Dict]]] = [None] * COVERAGE_BUCKETS

        for frame_idx, scores in ranked:
            bucket = min(frame_idx // bucket_size, COVERAGE_BUCKETS - 1)
            if bucket_reps[bucket] is None:
                bucket_reps[bucket] = (frame_idx, scores)

        # Start selection with bucket representatives (non-None only)
        seed_frames = [r for r in bucket_reps if r is not None]

        # Fill remaining slots from ranked (skip already chosen indices)
        chosen_indices = {fi for fi, _ in seed_frames}
        remainder = [
            (fi, sc) for fi, sc in ranked if fi not in chosen_indices
        ]

        return seed_frames + remainder

    # ──────────────────────────────────────────────────────────────────────────
    # Diversity filtering
    # ──────────────────────────────────────────────────────────────────────────

    def filter_for_diversity(
        self,
        ranked_frames: List[Tuple[int, Dict]],
        candidates: List[np.ndarray],
        num_frames: int,
        verbose: bool = True,
    ) -> List[Tuple[int, Dict]]:
        if verbose:
            print("\n" + "="*60)
            print("🎨 DIVERSITY FILTERING")
            print("="*60)

        # Ensure temporal coverage first
        ordered = self._ensure_coverage(
            ranked_frames, candidates, len(candidates), num_frames
        )

        selected: List[Tuple[int, Dict]] = []
        selected_frames: List[np.ndarray] = []
        skipped = 0

        for frame_idx, scores in ordered:
            if len(selected) >= num_frames:
                break
            frame = candidates[frame_idx]
            if self.is_diverse_enough(frame, selected_frames):
                selected.append((frame_idx, scores))
                selected_frames.append(frame)
                label = "EXTERIOR 🏠" if scores.get("exterior", 0) > 30 else "interior"
                if verbose:
                    print(
                        f"  ✅ Frame #{len(selected):02d}: "
                        f"total={scores['total']:.1f} | ext={scores.get('exterior',0):.0f} | {label}"
                    )
            else:
                skipped += 1
                if verbose:
                    print(f"  ❌ Skipped (too similar): total={scores['total']:.1f}")

        # Fallback: lower threshold
        if len(selected) < num_frames:
            if verbose:
                print(f"\n  ⚠️  {len(selected)}/{num_frames} found; retrying at {SIMILARITY_SOFT_LIMIT*100:.0f}% threshold…")
            used = {fi for fi, _ in selected}
            for frame_idx, scores in ordered:
                if len(selected) >= num_frames:
                    break
                if frame_idx in used:
                    continue
                frame = candidates[frame_idx]
                if self.is_diverse_enough(frame, selected_frames, SIMILARITY_SOFT_LIMIT):
                    selected.append((frame_idx, scores))
                    selected_frames.append(frame)
                    if verbose:
                        print(f"  ➕ Fallback Frame #{len(selected):02d}: total={scores['total']:.1f}")

        if verbose:
            print(f"\n✨ {len(selected)} unique frames selected ({skipped} duplicates removed)")
        return selected

    # ──────────────────────────────────────────────────────────────────────────
    # Frame I/O helpers
    # ──────────────────────────────────────────────────────────────────────────

    def resize_frame(self, frame: np.ndarray) -> np.ndarray:
        h, w = frame.shape[:2]
        if max(h, w) <= MAX_IMAGE_DIMENSION:
            return frame
        if w > h:
            nw, nh = MAX_IMAGE_DIMENSION, int(h * MAX_IMAGE_DIMENSION / w)
        else:
            nh, nw = MAX_IMAGE_DIMENSION, int(w * MAX_IMAGE_DIMENSION / h)
        return cv2.resize(frame, (nw, nh), interpolation=cv2.INTER_LANCZOS4)

    def save_frame(
        self, frame: np.ndarray, video_hash: str, frame_number: int
    ) -> Path:
        frame   = self.resize_frame(frame)
        rgb     = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
        img     = Image.fromarray(rgb)
        ts      = int(time.time() * 1000)
        fname   = f"{video_hash}_frame_{frame_number:03d}_{ts}.jpg"
        out     = OUTPUT_DIR / fname
        img.save(out, format=OUTPUT_IMAGE_FORMAT, quality=OUTPUT_IMAGE_QUALITY, optimize=True)
        return out

    # ──────────────────────────────────────────────────────────────────────────
    # Main public method
    # ──────────────────────────────────────────────────────────────────────────

    def extract_best_frames(
        self,
        video_path: Path,
        num_frames: int = DEFAULT_NUM_FRAMES,
        verbose: bool = True,
    ) -> Dict:
        """
        Extract the best N diverse frames from a real estate video.

        Returns
        -------
        dict with keys: success, frames, scores, metadata, error
        """
        start_time = time.time()
        num_frames = max(MIN_FRAMES, min(MAX_FRAMES, num_frames))

        is_valid, error_msg = self.validate_video(video_path)
        if not is_valid:
            return {"success": False, "error": error_msg, "frames": [], "scores": []}

        try:
            # ── Step 1: candidate extraction ──────────────────────────────────
            if verbose:
                print("\n" + "="*60)
                print("🎬 STEP 1: Extracting Candidate Frames (+ scene cuts)")
                print("="*60)
            candidates, indices = self.extract_candidate_frames(video_path)

            if len(candidates) < num_frames:
                print(f"⚠️  Only {len(candidates)} frames available; adjusting target.")
                num_frames = max(1, len(candidates))

            # ── Step 2: parallel scoring ──────────────────────────────────────
            if verbose:
                print("\n" + "="*60)
                print("🎯 STEP 2: Scoring Frames (parallel, exterior-boosted)")
                print("="*60)

            num_to_score  = min(len(candidates), num_frames * 4)
            ranked_frames = self.score_frames_parallel(candidates, num_to_score, verbose)

            # ── Step 3: diversity + coverage filter ───────────────────────────
            diverse_frames = self.filter_for_diversity(
                ranked_frames, candidates, num_frames, verbose
            )

            # ── Step 4: save ──────────────────────────────────────────────────
            if verbose:
                print("\n" + "="*60)
                print("💾 STEP 4: Saving Frames")
                print("="*60)

            video_hash   = hashlib.md5(video_path.name.encode()).hexdigest()[:12]
            saved_frames = []
            frame_scores = []

            for rank, (frame_idx, scores) in enumerate(diverse_frames, 1):
                out_path = self.save_frame(candidates[frame_idx], video_hash, rank)
                saved_frames.append(str(out_path))
                frame_scores.append({
                    "rank":           rank,
                    "original_index": frame_idx,
                    "total_score":    round(scores["total"], 2),
                    "sharpness":      round(scores.get("sharpness", 0), 2),
                    "brightness":     round(scores.get("brightness", 0), 2),
                    "composition":    round(scores.get("composition", 0), 2),
                    "clip_relevance": round(scores.get("clip_relevance", 0), 2),
                    "exterior_score": round(scores.get("exterior", 0), 2),
                    "is_exterior":    scores.get("exterior", 0) > 30,
                })
                if verbose:
                    ext_tag = "🏠 EXT" if scores.get("exterior", 0) > 30 else "🛋️  INT"
                    print(
                        f"  ✅ Frame #{rank}: {out_path.name} "
                        f"| score={scores['total']:.1f} | {ext_tag}"
                    )

            elapsed = time.time() - start_time

            # Collect metadata
            cap = cv2.VideoCapture(str(video_path))
            fps_meta    = cap.get(cv2.CAP_PROP_FPS)
            fc_meta     = int(cap.get(cv2.CAP_PROP_FRAME_COUNT))
            metadata = {
                "duration_seconds":     round(fc_meta / fps_meta, 2) if fps_meta else 0,
                "fps":                  round(fps_meta, 2),
                "width":                int(cap.get(cv2.CAP_PROP_FRAME_WIDTH)),
                "height":               int(cap.get(cv2.CAP_PROP_FRAME_HEIGHT)),
                "total_frames":         fc_meta,
                "candidates_evaluated": len(candidates),
                "frames_extracted":     len(diverse_frames),
                "exterior_frames":      sum(1 for s in frame_scores if s["is_exterior"]),
                "processing_time_seconds": round(elapsed, 2),
            }
            cap.release()

            if verbose:
                print("\n" + "="*60)
                print(
                    f"✨ Done! {len(diverse_frames)} frames in {elapsed:.1f}s "
                    f"({metadata['exterior_frames']} exterior shots)"
                )
                print("="*60)

            return {
                "success": True,
                "frames": saved_frames,
                "scores": frame_scores,
                "metadata": metadata,
                "error": None,
            }

        except Exception as exc:
            import traceback
            tb = traceback.format_exc()
            print(f"\n❌ ERROR: {exc}")
            if verbose:
                print(tb)
            return {
                "success": False,
                "error": str(exc),
                "error_details": tb if verbose else None,
                "frames": [],
                "scores": [],
            }


# ── Singleton ─────────────────────────────────────────────────────────────────
_extractor_instance: Optional[VideoFrameExtractor] = None


def get_extractor() -> VideoFrameExtractor:
    global _extractor_instance
    if _extractor_instance is None:
        _extractor_instance = VideoFrameExtractor()
    return _extractor_instance
