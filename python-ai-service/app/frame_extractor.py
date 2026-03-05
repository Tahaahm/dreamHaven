"""
Frame Extractor Module — Real Estate Optimized (v7.1 - Relaxed OpenCV)
======================================================================

WHAT CHANGED FROM v7.0:
  ✅ FIXED: Lowered hard-reject thresholds so videos reliably return 8-10 frames.
  ✅ FIXED: save_frame() uses rank to prevent millisecond filename collisions.
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
    OUTPUT_DIR,
)

# ─── Scoring Constants ────────────────────────────────────────────────────────

# HARD REJECT — LOWERED to allow more frames (preventing arrays < 10 items)
MIN_BRIGHTNESS_MEAN     = 15.0    # Was 22.0 (allows slightly darker rooms)
MAX_BRIGHTNESS_MEAN     = 250.0   # Was 247.0 (allows slightly blown-out windows)
MIN_SHARPNESS_LAPLACIAN = 25.0    # Was 40.0 (allows softer panning shots)
MIN_EDGE_DENSITY        = 0.010   # Was 0.018 (allows cleaner walls/solid areas)
MIN_STD_DEV             = 3.0     # Was 5.0

# SIMILARITY THRESHOLDS
EXTERIOR_VS_EXTERIOR_SIM = 0.38   # Very strict: same gate slightly different = DUP
GENERAL_SIM_THRESHOLD    = 0.45   # Normal: interior vs anything
SIMILARITY_FALLBACK      = 0.50   # Pass 2/3 relaxed threshold

# EXTERIOR CONTROL
MAX_EXTERIOR_FRAMES      = 2      # At most 2 exterior frames
EXTERIOR_SCORE_BOOST     = 12.0   # Bonus so best exterior ranks first
EXTERIOR_CONFIRM_THRESH  = 0.22   # ext_score above this = exterior frame

# UTILITY PENALTY — scoring reduction (not rejection)
VERTICAL_DOMINANCE_PENALTY = 20.0
DARK_FIXTURE_PENALTY       = 15.0
LOW_SATURATION_PENALTY     = 8.0

# TEMPORAL COVERAGE
COVERAGE_BUCKETS       = 5
SCENE_CHANGE_THRESHOLD = 22.0


# ─── VideoFrameExtractor ──────────────────────────────────────────────────────

class VideoFrameExtractor:

    # ──────────────────────────────────────────────────────────────────────────
    # Validation
    # ──────────────────────────────────────────────────────────────────────────

    def validate_video(self, video_path: Path) -> Tuple[bool, Optional[str]]:
        if not video_path.exists() or not video_path.is_file():
            return False, "Video file not found"
        cap = cv2.VideoCapture(str(video_path))
        if not cap.isOpened():
            cap.release()
            return False, "Cannot open video file — may be corrupt or unsupported codec"
        fps   = cap.get(cv2.CAP_PROP_FPS)
        total = int(cap.get(cv2.CAP_PROP_FRAME_COUNT))
        cap.release()
        if fps <= 0:
            return False, "Invalid video — FPS is zero"
        duration = total / fps
        if duration > MAX_VIDEO_DURATION_SECONDS:
            return False, f"Video too long ({duration:.0f}s). Max: {MAX_VIDEO_DURATION_SECONDS}s"
        return True, None

    # ──────────────────────────────────────────────────────────────────────────
    # Hard reject
    # ──────────────────────────────────────────────────────────────────────────

    def _hard_reject(self, frame: np.ndarray) -> Tuple[bool, str]:
        gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)

        if float(gray.std()) < MIN_STD_DEV:
            return True, "blank"

        mean_b = float(gray.mean())
        if mean_b < MIN_BRIGHTNESS_MEAN:
            return True, "pitch_black"
        if mean_b > MAX_BRIGHTNESS_MEAN:
            return True, "overexposed"

        lap_var = float(cv2.Laplacian(gray, cv2.CV_64F).var())
        if lap_var < MIN_SHARPNESS_LAPLACIAN:
            return True, "extreme_blur"

        edges = cv2.Canny(gray, 30, 100)
        edge_density = edges.sum() / (255.0 * gray.size)
        if edge_density < MIN_EDGE_DENSITY:
            return True, "no_content"

        return False, ""

    # ──────────────────────────────────────────────────────────────────────────
    # Exterior classifier
    # ──────────────────────────────────────────────────────────────────────────

    def _exterior_score(self, frame: np.ndarray) -> float:
        h, w  = frame.shape[:2]
        hsv   = cv2.cvtColor(frame, cv2.COLOR_BGR2HSV)

        upper = hsv[: h // 3, :, :]
        area  = float(upper.shape[0] * upper.shape[1]) + 1e-6

        sky_mask    = (
            (upper[:, :, 0] >= 85) & (upper[:, :, 0] <= 135) &
            (upper[:, :, 1] > 20)  & (upper[:, :, 2] > 55)
        )
        warm_mask   = (
            (upper[:, :, 0] >= 10) & (upper[:, :, 0] <= 45) &
            (upper[:, :, 1] > 45)  & (upper[:, :, 2] > 75)
        )
        cloudy_mask = (upper[:, :, 1] < 40) & (upper[:, :, 2] > 180)

        sky_r   = float(sky_mask.sum())   / area
        warm_r  = float(warm_mask.sum())  / area
        cloud_r = float(cloudy_mask.sum()) / area

        bright = float(hsv[:, :, 2].mean()) / 255.0
        sat    = float(hsv[:, :, 1].mean()) / 255.0
        dark_r = float((hsv[:, :, 2] < 40).sum()) / (h * w + 1e-6)

        score = (
            (sky_r + warm_r + cloud_r) * 0.45 +
            bright * 0.20 +
            sat    * 0.20 +
            (1.0 - dark_r) * 0.15
        )
        return float(np.clip(score, 0.0, 1.0))

    # ──────────────────────────────────────────────────────────────────────────
    # Utility penalty
    # ──────────────────────────────────────────────────────────────────────────

    def _utility_penalty(self, frame: np.ndarray) -> float:
        gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
        hsv  = cv2.cvtColor(frame, cv2.COLOR_BGR2HSV)
        h    = frame.shape[0]
        pen  = 0.0

        sx = cv2.Sobel(gray, cv2.CV_64F, 1, 0, ksize=3)
        sy = cv2.Sobel(gray, cv2.CV_64F, 0, 1, ksize=3)
        xe = float(np.abs(sx).mean())
        ye = float(np.abs(sy).mean())
        if ye > 0 and (xe / (ye + 1e-6)) > 3.0:
            pen += VERTICAL_DOMINANCE_PENALTY

        lower = gray[h // 2:, :]
        dark_ratio   = float((lower < 55).sum())  / (lower.size + 1e-6)
        bright_ratio = float((lower > 210).sum()) / (lower.size + 1e-6)
        if dark_ratio > 0.60 and bright_ratio < 0.06:
            pen += DARK_FIXTURE_PENALTY

        if float(hsv[:, :, 1].mean()) < 20:
            pen += LOW_SATURATION_PENALTY

        return min(pen, 30.0)

    # ──────────────────────────────────────────────────────────────────────────
    # Fast OpenCV frame scoring
    # ──────────────────────────────────────────────────────────────────────────

    def _score_one(self, i: int, frame: np.ndarray) -> Tuple[int, Dict]:
        gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
        hsv  = cv2.cvtColor(frame, cv2.COLOR_BGR2HSV)
        h, w = gray.shape

        lap_var   = float(cv2.Laplacian(gray, cv2.CV_64F).var())
        sharpness = min(100.0, lap_var / 5.0)

        mean_b     = float(gray.mean())
        brightness = 100.0 - abs(mean_b - 128.0) / 1.28

        edges  = cv2.Canny(gray, 50, 150)
        third  = h // 3
        t_top  = float(edges[:third,        :].mean())
        t_mid  = float(edges[third:2*third, :].mean())
        t_bot  = float(edges[2*third:,      :].mean())
        edge_total = t_top + t_mid + t_bot + 1e-6
        mid_ratio  = t_mid / edge_total
        composition = min(100.0, 40.0 + mid_ratio * 80.0)

        saturation = min(100.0, float(hsv[:, :, 1].mean()) / 2.55)

        base_score = (
            sharpness   * 0.35 +
            brightness  * 0.25 +
            composition * 0.20 +
            saturation  * 0.20
        )

        ext     = self._exterior_score(frame)
        penalty = self._utility_penalty(frame)
        is_ext  = ext > EXTERIOR_CONFIRM_THRESH
        boost   = EXTERIOR_SCORE_BOOST * ext if is_ext else 0.0

        total = max(0.0, base_score + boost - penalty)

        return i, {
            "total":          round(total, 2),
            "sharpness":      round(sharpness, 2),
            "brightness":     round(brightness, 2),
            "composition":    round(composition, 2),
            "saturation":     round(saturation, 2),
            "clip_relevance": 0.0,
            "exterior":       round(ext * 100, 2),
            "is_exterior":    is_ext,
            "penalty":        round(penalty, 2),
        }

    # ──────────────────────────────────────────────────────────────────────────
    # Score all candidates
    # ──────────────────────────────────────────────────────────────────────────

    def score_frames(
        self,
        candidates: List[np.ndarray],
        num_best:   int,
        verbose:    bool = True,
    ) -> List[Tuple[int, Dict]]:
        if verbose:
            print(f"  Scoring {len(candidates)} frames with OpenCV (no ML model)…")

        t0      = time.time()
        results = []

        for i, frame in enumerate(candidates):
            try:
                results.append(self._score_one(i, frame))
            except Exception as e:
                print(f"  ⚠️  Frame {i} scoring failed: {e}")

        results.sort(key=lambda x: x[1]["total"], reverse=True)

        elapsed = time.time() - t0
        if verbose:
            print(f"  ✅ Scored in {elapsed:.3f}s  ({elapsed/max(len(candidates),1)*1000:.1f}ms/frame)")

        return results[:num_best]

    # ──────────────────────────────────────────────────────────────────────────
    # Scene-change detector
    # ──────────────────────────────────────────────────────────────────────────

    def _is_scene_change(
        self, prev: Optional[np.ndarray], curr: np.ndarray
    ) -> bool:
        if prev is None:
            return False
        return float(cv2.absdiff(prev, curr).mean()) > SCENE_CHANGE_THRESHOLD

    # ──────────────────────────────────────────────────────────────────────────
    # Candidate extraction
    # ──────────────────────────────────────────────────────────────────────────

    def extract_candidate_frames(
        self,
        video_path: Path,
        interval:   int = FRAME_SAMPLE_INTERVAL,
    ) -> Tuple[List[np.ndarray], List[int]]:
        print(f"\n📹 Opening: {video_path.name}")
        cap = cv2.VideoCapture(str(video_path))
        if not cap.isOpened():
            raise ValueError(f"Cannot open video: {video_path}")

        fps   = cap.get(cv2.CAP_PROP_FPS)
        total = int(cap.get(cv2.CAP_PROP_FRAME_COUNT))
        dur   = total / fps if fps > 0 else 0
        print(f"  Duration: {dur:.1f}s | FPS: {fps:.1f} | Total frames: {total}")
        print(f"  Sampling every {interval} frames (~{interval/max(fps,1):.1f}s intervals)")

        start = max(0, int(total * 0.03))
        end   = min(total - 1, int(total * 0.97))

        frames:    List[np.ndarray] = []
        indices:   List[int]        = []
        rejected:  Dict[str, int]   = {}
        prev_gray: Optional[np.ndarray] = None
        idx = 0

        while cap.isOpened():
            ret, frame = cap.read()
            if not ret:
                break

            if start <= idx <= end:
                curr_gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)

                take = (idx % interval == 0) or self._is_scene_change(prev_gray, curr_gray)
                if take:
                    rej, reason = self._hard_reject(frame)
                    if rej:
                        rejected[reason] = rejected.get(reason, 0) + 1
                    else:
                        frames.append(self.resize_frame(frame))
                        indices.append(idx)

                prev_gray = curr_gray

            idx += 1

        cap.release()
        print(f"  ✅ {len(frames)} usable candidates | Rejected: {rejected}")
        return frames, indices

    # ──────────────────────────────────────────────────────────────────────────
    # Temporal bucket seeds
    # ──────────────────────────────────────────────────────────────────────────

    def _bucket_seeds(
        self, ranked: List[Tuple[int, Dict]], total: int
    ) -> List[Tuple[int, Dict]]:
        bsize   = max(1, total // COVERAGE_BUCKETS)
        buckets: Dict[int, Optional[Tuple[int, Dict]]] = {
            b: None for b in range(COVERAGE_BUCKETS)
        }
        for fi, sc in ranked:
            b = min(fi // bsize, COVERAGE_BUCKETS - 1)
            if buckets[b] is None:
                buckets[b] = (fi, sc)
        return [v for v in buckets.values() if v is not None]

    # ──────────────────────────────────────────────────────────────────────────
    # Similarity (SSIM + histogram + pHash)
    # ──────────────────────────────────────────────────────────────────────────

    def _similarity(
        self, f1: np.ndarray, f2: np.ndarray, size: int = 96
    ) -> float:
        t1 = cv2.resize(f1, (size, size))
        t2 = cv2.resize(f2, (size, size))

        h1 = cv2.calcHist(
            [cv2.cvtColor(t1, cv2.COLOR_BGR2HSV)], [0, 1], None,
            [32, 32], [0, 180, 0, 256]
        )
        h2 = cv2.calcHist(
            [cv2.cvtColor(t2, cv2.COLOR_BGR2HSV)], [0, 1], None,
            [32, 32], [0, 180, 0, 256]
        )
        cv2.normalize(h1, h1, 0, 1, cv2.NORM_MINMAX)
        cv2.normalize(h2, h2, 0, 1, cv2.NORM_MINMAX)
        hist_sim = max(0.0, float(cv2.compareHist(h1, h2, cv2.HISTCMP_CORREL)))

        g1 = cv2.cvtColor(t1, cv2.COLOR_BGR2GRAY).astype(np.float32)
        g2 = cv2.cvtColor(t2, cv2.COLOR_BGR2GRAY).astype(np.float32)
        mu1, mu2 = g1.mean(), g2.mean()
        s1,  s2  = g1.std(),  g2.std()
        cov      = float(((g1 - mu1) * (g2 - mu2)).mean())
        C1, C2   = 6.5025, 58.5225
        ssim     = float(np.clip(
            (2 * mu1 * mu2 + C1) * (2 * cov + C2) /
            ((mu1**2 + mu2**2 + C1) * (s1**2 + s2**2 + C2)),
            0.0, 1.0
        ))

        p1 = (cv2.resize(cv2.cvtColor(t1, cv2.COLOR_BGR2GRAY), (8, 8)) > 128).flatten()
        p2 = (cv2.resize(cv2.cvtColor(t2, cv2.COLOR_BGR2GRAY), (8, 8)) > 128).flatten()
        phash = 1.0 - float(np.count_nonzero(p1 != p2)) / 64.0

        return 0.20 * hist_sim + 0.48 * ssim + 0.32 * phash

    # ──────────────────────────────────────────────────────────────────────────
    # Diversity filter
    # ──────────────────────────────────────────────────────────────────────────

    def filter_for_diversity(
        self,
        ranked:     List[Tuple[int, Dict]],
        candidates: List[np.ndarray],
        num_frames: int,
        verbose:    bool = True,
    ) -> List[Tuple[int, Dict]]:
        if verbose:
            print("\n" + "=" * 60)
            print("🎨 DIVERSITY FILTER")
            print(f"   ext↔ext threshold  : {EXTERIOR_VS_EXTERIOR_SIM}")
            print(f"   general threshold  : {GENERAL_SIM_THRESHOLD}")
            print(f"   max exterior frames: {MAX_EXTERIOR_FRAMES}")
            print("=" * 60)

        seeds     = self._bucket_seeds(ranked, len(candidates))
        seed_idxs = {fi for fi, _ in seeds}
        remainder = [(fi, sc) for fi, sc in ranked if fi not in seed_idxs]
        ordered   = seeds + remainder

        selected:   List[Tuple[int, Dict]] = []
        sel_frames: List[np.ndarray]       = []
        sel_is_ext: List[bool]             = []
        ext_count = 0
        skipped   = 0

        def _try_add(fi: int, sc: Dict, gen_thresh: float, ext_thresh: float) -> bool:
            nonlocal ext_count, skipped

            if len(selected) >= num_frames:
                return False

            is_ext = sc.get("is_exterior", False)

            if is_ext and ext_count >= MAX_EXTERIOR_FRAMES:
                if verbose:
                    print(f"  🚫 Ext cap — score={sc['total']:.0f}")
                return False

            frame = candidates[fi]
            for existing, existing_is_ext in zip(sel_frames, sel_is_ext):
                thresh = ext_thresh if (is_ext and existing_is_ext) else gen_thresh
                sim    = self._similarity(frame, existing)
                if sim > thresh:
                    skipped += 1
                    if verbose:
                        kind = "ext↔ext" if (is_ext and existing_is_ext) else "general"
                        print(
                            f"  ❌ Dup ({kind} sim={sim:.2f}>{thresh:.2f}) "
                            f"score={sc['total']:.0f}"
                        )
                    return False

            selected.append((fi, sc))
            sel_frames.append(frame)
            sel_is_ext.append(is_ext)
            if is_ext:
                ext_count += 1

            tag = "🏠 EXT" if is_ext else "🛋️  INT"
            if verbose:
                print(
                    f"  ✅ #{len(selected):02d}  score={sc['total']:.1f}  "
                    f"sharp={sc.get('sharpness', 0):.0f}  "
                    f"ext={sc.get('exterior', 0):.0f}  "
                    f"pen={sc.get('penalty', 0):.0f}  {tag}"
                )
            return True

        for fi, sc in ordered:
            _try_add(fi, sc, GENERAL_SIM_THRESHOLD, EXTERIOR_VS_EXTERIOR_SIM)
            if len(selected) >= num_frames:
                break

        if len(selected) < num_frames:
            if verbose:
                print(
                    f"\n  ⚠️  Only {len(selected)}/{num_frames} selected — "
                    f"relaxing threshold to {SIMILARITY_FALLBACK}…"
                )
            used = {fi for fi, _ in selected}
            for fi, sc in ordered:
                if fi in used:
                    continue
                _try_add(fi, sc, SIMILARITY_FALLBACK, SIMILARITY_FALLBACK)
                if len(selected) >= num_frames:
                    break

        if len(selected) < num_frames:
            if verbose:
                print(
                    f"\n  ⚠️  Still only {len(selected)}/{num_frames} — "
                    f"removing exterior cap…"
                )
            used = {fi for fi, _ in selected}
            for fi, sc in ordered:
                if fi in used or len(selected) >= num_frames:
                    continue
                frame  = candidates[fi]
                is_ext = sc.get("is_exterior", False)
                fits   = all(
                    self._similarity(frame, e) <= SIMILARITY_FALLBACK
                    for e in sel_frames
                )
                if fits:
                    selected.append((fi, sc))
                    sel_frames.append(frame)
                    sel_is_ext.append(is_ext)
                    if is_ext:
                        ext_count += 1
                    if verbose:
                        tag = "🏠 EXT" if is_ext else "🛋️  INT"
                        print(
                            f"  ➕ Emergency #{len(selected):02d}  {tag}  "
                            f"score={sc['total']:.1f}"
                        )

        int_count = len(selected) - ext_count
        if verbose:
            print(
                f"\n✨ Selected {len(selected)} frames: "
                f"{ext_count} exterior + {int_count} interior "
                f"({skipped} duplicates removed)"
            )

        return selected

    # ──────────────────────────────────────────────────────────────────────────
    # I/O helpers
    # ──────────────────────────────────────────────────────────────────────────

    def resize_frame(self, frame: np.ndarray) -> np.ndarray:
        h, w = frame.shape[:2]
        if max(h, w) <= MAX_IMAGE_DIMENSION:
            return frame
        if w >= h:
            nw = MAX_IMAGE_DIMENSION
            nh = int(h * MAX_IMAGE_DIMENSION / w)
        else:
            nh = MAX_IMAGE_DIMENSION
            nw = int(w * MAX_IMAGE_DIMENSION / h)
        return cv2.resize(frame, (nw, nh), interpolation=cv2.INTER_LANCZOS4)

    def save_frame(
        self, frame: np.ndarray, video_hash: str, rank: int
    ) -> Path:
        """✅ FIXED: Incorporates rank to ensure totally unique filenames."""
        img = Image.fromarray(cv2.cvtColor(frame, cv2.COLOR_BGR2RGB))
        ts  = int(time.time() * 1000)
        # Added r{rank:02d} to the filename to avoid collisions when processing very fast
        out = OUTPUT_DIR / f"{video_hash}_r{rank:02d}_{ts}.{OUTPUT_IMAGE_FORMAT.lower()}"
        img.save(
            out,
            format=OUTPUT_IMAGE_FORMAT,
            quality=OUTPUT_IMAGE_QUALITY,
            optimize=True
        )
        return out

    # ──────────────────────────────────────────────────────────────────────────
    # Main entry point
    # ──────────────────────────────────────────────────────────────────────────

    def extract_best_frames(
        self,
        video_path: Path,
        num_frames: int  = DEFAULT_NUM_FRAMES,
        verbose:    bool = True,
    ) -> Dict:
        t0         = time.time()
        num_frames = max(MIN_FRAMES, min(MAX_FRAMES, num_frames))

        ok, err = self.validate_video(video_path)
        if not ok:
            return {"success": False, "error": err, "frames": [], "scores": [], "metadata": {}}

        try:
            if verbose:
                print("\n" + "=" * 60)
                print("🎬 STEP 1: Candidate Extraction")
                print("=" * 60)

            candidates, cand_indices = self.extract_candidate_frames(video_path)

            if not candidates:
                return {
                    "success": False,
                    "error":   "No usable frames found in video",
                    "frames":  [], "scores": [], "metadata": {}
                }

            target = min(num_frames, len(candidates))
            if target < num_frames and verbose:
                print(f"⚠️  Only {len(candidates)} candidates — will return {target} frames")

            if verbose:
                print("\n" + "=" * 60)
                print("🎯 STEP 2: Quality Scoring (OpenCV — no ML model)")
                print("=" * 60)

            ranked = self.score_frames(
                candidates,
                num_best=min(len(candidates), target * 6),
                verbose=verbose,
            )

            final = self.filter_for_diversity(ranked, candidates, target, verbose)

            if verbose:
                print("\n" + "=" * 60)
                print("💾 STEP 3: Saving Frames")
                print("=" * 60)

            vh           = hashlib.md5(video_path.name.encode()).hexdigest()[:12]
            saved_frames = []
            frame_scores = []

            for rank, (fi, sc) in enumerate(final, 1):
                path   = self.save_frame(candidates[fi], vh, rank)
                is_ext = sc.get("is_exterior", False)

                saved_frames.append(str(path))
                frame_scores.append({
                    "rank":           rank,
                    "original_index": cand_indices[fi],
                    "total_score":    round(sc["total"], 2),
                    "sharpness":      round(sc.get("sharpness",      0), 2),
                    "brightness":     round(sc.get("brightness",     0), 2),
                    "composition":    round(sc.get("composition",    0), 2),
                    "saturation":     round(sc.get("saturation",     0), 2),
                    "clip_relevance": 0.0,
                    "exterior_score": round(sc.get("exterior",       0), 2),
                    "penalty":        round(sc.get("penalty",        0), 2),
                    "is_exterior":    is_ext,
                })

                if verbose:
                    tag = "🏠 EXT" if is_ext else "🛋️  INT"
                    print(
                        f"  ✅ #{rank:02d}: {path.name}  {tag}  "
                        f"score={sc['total']:.1f}  "
                        f"sharp={sc.get('sharpness', 0):.0f}"
                    )

            cap = cv2.VideoCapture(str(video_path))
            fv  = cap.get(cv2.CAP_PROP_FPS)
            fc  = int(cap.get(cv2.CAP_PROP_FRAME_COUNT))
            fw  = int(cap.get(cv2.CAP_PROP_FRAME_WIDTH))
            fh  = int(cap.get(cv2.CAP_PROP_FRAME_HEIGHT))
            cap.release()

            elapsed = time.time() - t0
            metadata = {
                "duration_seconds":        round(fc / fv, 2) if fv else 0,
                "fps":                     round(fv, 2),
                "width":                   fw,
                "height":                  fh,
                "total_frames":            fc,
                "candidates_evaluated":    len(candidates),
                "frames_extracted":        len(final),
                "exterior_frames":         sum(1 for s in frame_scores if     s["is_exterior"]),
                "interior_frames":         sum(1 for s in frame_scores if not s["is_exterior"]),
                "processing_time_seconds": round(elapsed, 2),
                "scoring_mode":            "opencv-fast",
            }

            if verbose:
                print(
                    f"\n✨ Done: {len(final)}/{num_frames} frames "
                    f"in {elapsed:.1f}s  🚀"
                )

            return {
                "success":  True,
                "frames":   saved_frames,
                "scores":   frame_scores,
                "metadata": metadata,
                "error":    None,
            }

        except Exception as exc:
            import traceback
            tb = traceback.format_exc()
            print(f"\n❌ {exc}\n{tb}")
            return {
                "success":       False,
                "error":         str(exc),
                "error_details": tb,
                "frames":        [],
                "scores":        [],
                "metadata":      {},
            }


# ── Singleton ─────────────────────────────────────────────────────────────────

_instance: Optional[VideoFrameExtractor] = None


def get_extractor() -> VideoFrameExtractor:
    global _instance
    if _instance is None:
        _instance = VideoFrameExtractor()
    return _instance
