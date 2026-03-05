"""
Frame Extractor Module - Real Estate Optimized (v6 - Final Fix)
===============================================================

THE CORE FIX for "same gate repeated 4 times":

  Problem: exterior shots of the same gate at slightly different times/angles
           have different colour histograms (lighting changes) but same structure.
           A single combined threshold couldn't distinguish "same gate, pan left"
           from "genuinely different exterior angle".

  Solution: TWO separate similarity thresholds:
    - EXTERIOR_VS_EXTERIOR_THRESHOLD = 0.38  (very strict: 38%)
      → two exterior frames must be very different to both be kept
    - GENERAL_THRESHOLD = 0.45               (normal: 45%)
      → interior vs interior, or interior vs exterior

  Plus: MAX_EXTERIOR_FRAMES = 2
    → absolute hard cap — at most 2 exterior frames in the final set
    → forces the algorithm to find interior shots for the remaining 8 slots

  Result: 2 best exterior shots + 8 diverse interior/entrance shots = 10 total
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
from app.quality_scorer import get_scorer


# ─── Constants ────────────────────────────────────────────────────────────────

# HARD REJECT — only truly unusable frames (lenient — real estate footage varies)
MIN_BRIGHTNESS_MEAN     = 22.0   # Only pitch-black
MAX_BRIGHTNESS_MEAN     = 247.0  # Only blown-out white
MIN_SHARPNESS_LAPLACIAN = 40.0   # Only extreme motion blur
MIN_EDGE_DENSITY        = 0.018  # Only flat solid frames (no content at all)
MIN_STD_DEV             = 5.0    # Only perfectly blank frames

# SIMILARITY THRESHOLDS — tightened to stop panning duplicates
EXTERIOR_VS_EXTERIOR_SIM = 0.38  # Very strict: same gate from slightly diff angle = DUP
GENERAL_SIM_THRESHOLD    = 0.45  # Normal: interior vs anything
SIMILARITY_FALLBACK      = 0.50  # Prevent Pass 2/3 from letting duplicate gates through

# EXTERIOR CONTROL — prevents gate domination
MAX_EXTERIOR_FRAMES      = 2     # At most 2 exterior frames out of 10
                                 # Raise to 3 for videos with multiple distinct exteriors
EXTERIOR_SCORE_BOOST     = 12.0  # Bonus so best exterior ranks first
EXTERIOR_CONFIRM_THRESH  = 0.22  # ext_score above this = exterior frame

# UTILITY PENALTY — scoring reduction (not rejection) for bad shots
VERTICAL_DOMINANCE_PENALTY = 20.0  # Gate bar / door-frame close-up
DARK_FIXTURE_PENALTY       = 15.0  # Dark frame with single bright object
LOW_SATURATION_PENALTY     = 8.0   # Grey/monochrome (bare concrete, corridor)

# TEMPORAL COVERAGE
COVERAGE_BUCKETS       = 5
SCENE_CHANGE_THRESHOLD = 22.0

# ─────────────────────────────────────────────────────────────────────────────


class VideoFrameExtractor:

    def __init__(self):
        self.scorer = get_scorer()

    # ──────────────────────────────────────────────────────────────────────────
    # Validation
    # ──────────────────────────────────────────────────────────────────────────

    def validate_video(self, video_path: Path) -> Tuple[bool, Optional[str]]:
        if not video_path.exists() or not video_path.is_file():
            return False, "Video file not found"
        cap = cv2.VideoCapture(str(video_path))
        if not cap.isOpened():
            return False, "Cannot open video file"
        fps   = cap.get(cv2.CAP_PROP_FPS)
        total = int(cap.get(cv2.CAP_PROP_FRAME_COUNT))
        cap.release()
        if fps == 0:
            return False, "Invalid video – FPS is zero"
        if (total / fps) > MAX_VIDEO_DURATION_SECONDS:
            return False, f"Video too long (max {MAX_VIDEO_DURATION_SECONDS}s)"
        return True, None

    # ──────────────────────────────────────────────────────────────────────────
    # Hard-reject  (lenient — only truly unusable)
    # ──────────────────────────────────────────────────────────────────────────

    def _hard_reject(self, frame: np.ndarray) -> Tuple[bool, str]:
        gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)

        if float(gray.std()) < MIN_STD_DEV:
            return True, "blank"

        b = float(gray.mean())
        if b < MIN_BRIGHTNESS_MEAN:
            return True, "pitch_black"
        if b > MAX_BRIGHTNESS_MEAN:
            return True, "overexposed"

        if float(cv2.Laplacian(gray, cv2.CV_64F).var()) < MIN_SHARPNESS_LAPLACIAN:
            return True, "extreme_blur"

        edges = cv2.Canny(gray, 30, 100)
        if (edges.sum() / (255.0 * gray.size)) < MIN_EDGE_DENSITY:
            return True, "no_content"

        return False, ""

    # ──────────────────────────────────────────────────────────────────────────
    # Exterior classifier (UPDATED for Cloudy Skies)
    # ──────────────────────────────────────────────────────────────────────────

    def _exterior_score(self, frame: np.ndarray) -> float:
        """Returns 0–1 confidence this is an outdoor/exterior shot."""
        h, w  = frame.shape[:2]
        hsv   = cv2.cvtColor(frame, cv2.COLOR_BGR2HSV)

        # Look at the top third of the frame (better chance of catching the sky)
        upper = hsv[: h // 3, :, :]
        area  = float(upper.shape[0] * upper.shape[1]) + 1e-6

        sky_mask    = ((upper[:,:,0]>=85)&(upper[:,:,0]<=135)&(upper[:,:,1]>20)&(upper[:,:,2]>55))
        warm_mask   = ((upper[:,:,0]>=10)&(upper[:,:,0]<=45) &(upper[:,:,1]>45)&(upper[:,:,2]>75))

        # Catch cloudy/overcast skies (very bright, low saturation)
        cloudy_mask = ((upper[:,:,1] < 40) & (upper[:,:,2] > 180))

        sky_r   = float(sky_mask.sum())  / area
        warm_r  = float(warm_mask.sum()) / area
        cloud_r = float(cloudy_mask.sum()) / area

        bright  = float(hsv[:,:,2].mean()) / 255.0
        sat     = float(hsv[:,:,1].mean()) / 255.0
        dark_r  = float((hsv[:,:,2] < 40).sum()) / (h * w + 1e-6)

        return float(np.clip(
            (sky_r + warm_r + cloud_r) * 0.45 + bright * 0.20 + sat * 0.20 + (1.0 - dark_r) * 0.15,
            0.0, 1.0,
        ))

    # ──────────────────────────────────────────────────────────────────────────
    # Utility penalty
    # ──────────────────────────────────────────────────────────────────────────

    def _utility_penalty(self, frame: np.ndarray) -> float:
        """Scoring penalty (0–30) for unhelpful shots. Does NOT reject."""
        gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
        hsv  = cv2.cvtColor(frame, cv2.COLOR_BGR2HSV)
        h    = frame.shape[0]
        pen  = 0.0

        # Vertical-line dominance → gate-bar / door-frame close-up
        sx = cv2.Sobel(gray, cv2.CV_64F, 1, 0, ksize=3)
        sy = cv2.Sobel(gray, cv2.CV_64F, 0, 1, ksize=3)
        xe, ye = float(np.abs(sx).mean()), float(np.abs(sy).mean())
        if ye > 0 and (xe / (ye + 1e-6)) > 3.0:
            pen += VERTICAL_DOMINANCE_PENALTY

        # Dark frame with small bright fixture (door light / toilet)
        lower = gray[h // 2:, :]
        if float((lower < 55).sum()) / (lower.size + 1e-6) > 0.60 and \
           float((lower > 210).sum()) / (lower.size + 1e-6) < 0.06:
            pen += DARK_FIXTURE_PENALTY

        # Very low saturation (bare concrete / grey corridor)
        if float(hsv[:,:,1].mean()) < 20:
            pen += LOW_SATURATION_PENALTY

        return min(pen, 30.0)

    # ──────────────────────────────────────────────────────────────────────────
    # Scene-change detector
    # ──────────────────────────────────────────────────────────────────────────

    def _is_scene_change(self, prev: Optional[np.ndarray], curr: np.ndarray) -> bool:
        if prev is None:
            return False
        return float(cv2.absdiff(prev, curr).mean()) > SCENE_CHANGE_THRESHOLD

    # ──────────────────────────────────────────────────────────────────────────
    # Candidate extraction (UPDATED for Memory Limits)
    # ──────────────────────────────────────────────────────────────────────────

    def extract_candidate_frames(
        self, video_path: Path, interval: int = FRAME_SAMPLE_INTERVAL
    ) -> Tuple[List[np.ndarray], List[int]]:
        print(f"\n📹 {video_path.name}")
        cap = cv2.VideoCapture(str(video_path))
        if not cap.isOpened():
            raise ValueError("Cannot open video file")

        fps   = cap.get(cv2.CAP_PROP_FPS)
        total = int(cap.get(cv2.CAP_PROP_FRAME_COUNT))
        dur   = total / fps if fps > 0 else 0
        print(f"  {dur:.1f}s | {fps:.1f}fps | {total} frames")

        start = max(0, int(total * 0.03))
        end   = min(total - 1, int(total * 0.97))

        frames, indices = [], []
        rejected: Dict[str, int] = {}
        prev_gray = None
        idx       = 0

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
                        # Resize immediately to prevent OS RAM Exhaustion
                        resized_frame = self.resize_frame(frame)
                        frames.append(resized_frame)
                        indices.append(idx)
                prev_gray = curr_gray
            idx += 1

        cap.release()
        print(f"✅ {len(frames)} kept | rejected: {rejected}")
        return frames, indices

    # ──────────────────────────────────────────────────────────────────────────
    # Sequential scoring (UPDATED to fix ML deadlock)
    # ──────────────────────────────────────────────────────────────────────────

    def _score_one(self, i: int, frame: np.ndarray) -> Tuple[int, Dict]:
        scores  = self.scorer.score_frame(frame)
        ext     = self._exterior_score(frame)
        penalty = self._utility_penalty(frame)

        scores["exterior"]    = round(ext * 100, 2)
        scores["is_exterior"] = ext > EXTERIOR_CONFIRM_THRESH
        scores["penalty"]     = round(penalty, 2)

        boost           = EXTERIOR_SCORE_BOOST * ext if ext > EXTERIOR_CONFIRM_THRESH else 0.0
        scores["total"] = round(max(0.0, scores["total"] + boost - penalty), 2)
        return i, scores

    def score_frames_parallel(
        self, candidates: List[np.ndarray], num_best: int, verbose: bool = True
    ) -> List[Tuple[int, Dict]]:
        if verbose:
            print(f"  Scoring {len(candidates)} frames (Sequential to prevent ML deadlock)…")
        results = []

        # Running sequentially guarantees the ML model won't deadlock.
        for i, f in enumerate(candidates):
            try:
                results.append(self._score_one(i, f))
            except Exception as e:
                print(f"  ⚠️  Frame {i}: {e}")

        results.sort(key=lambda x: x[1]["total"], reverse=True)
        return results[:num_best]

    # ──────────────────────────────────────────────────────────────────────────
    # Similarity  (SSIM + histogram + pHash)
    # ──────────────────────────────────────────────────────────────────────────

    def _similarity(self, f1: np.ndarray, f2: np.ndarray, size: int = 96) -> float:
        t1 = cv2.resize(f1, (size, size))
        t2 = cv2.resize(f2, (size, size))

        # Histogram — colour palette similarity
        h1 = cv2.calcHist([cv2.cvtColor(t1,cv2.COLOR_BGR2HSV)],[0,1],None,[32,32],[0,180,0,256])
        h2 = cv2.calcHist([cv2.cvtColor(t2,cv2.COLOR_BGR2HSV)],[0,1],None,[32,32],[0,180,0,256])
        cv2.normalize(h1,h1,0,1,cv2.NORM_MINMAX)
        cv2.normalize(h2,h2,0,1,cv2.NORM_MINMAX)
        hist = max(0.0, float(cv2.compareHist(h1,h2,cv2.HISTCMP_CORREL)))

        # SSIM — spatial structure
        g1 = cv2.cvtColor(t1,cv2.COLOR_BGR2GRAY).astype(np.float32)
        g2 = cv2.cvtColor(t2,cv2.COLOR_BGR2GRAY).astype(np.float32)
        mu1,mu2 = g1.mean(),g2.mean()
        s1,s2   = g1.std(),g2.std()
        cov     = float(((g1-mu1)*(g2-mu2)).mean())
        C1,C2   = 6.5025,58.5225
        ssim    = float(np.clip((2*mu1*mu2+C1)*(2*cov+C2)/((mu1**2+mu2**2+C1)*(s1**2+s2**2+C2)),0,1))

        # pHash — perceptual hash (catches pan/zoom variants)
        p1 = (cv2.resize(cv2.cvtColor(t1,cv2.COLOR_BGR2GRAY),(8,8)) > 128).flatten()
        p2 = (cv2.resize(cv2.cvtColor(t2,cv2.COLOR_BGR2GRAY),(8,8)) > 128).flatten()
        phash = 1.0 - float(np.count_nonzero(p1!=p2)) / 64.0

        # Weights: reduce histogram weight (lighting-sensitive), boost ssim+phash
        return 0.20 * hist + 0.48 * ssim + 0.32 * phash

    # ──────────────────────────────────────────────────────────────────────────
    # Temporal bucket seeds
    # ──────────────────────────────────────────────────────────────────────────

    def _bucket_seeds(self, ranked: List[Tuple[int,Dict]], total: int) -> List[Tuple[int,Dict]]:
        bsize   = max(1, total // COVERAGE_BUCKETS)
        buckets: Dict[int, Optional[Tuple[int,Dict]]] = {b: None for b in range(COVERAGE_BUCKETS)}
        for fi, sc in ranked:
            b = min(fi // bsize, COVERAGE_BUCKETS - 1)
            if buckets[b] is None:
                buckets[b] = (fi, sc)
        return [v for v in buckets.values() if v is not None]

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
        """
        Select `num_frames` maximally diverse frames.
        """
        if verbose:
            print("\n" + "="*60)
            print(f"🎨 DIVERSITY FILTER")
            print(f"   ext-vs-ext threshold : {EXTERIOR_VS_EXTERIOR_SIM}")
            print(f"   general threshold    : {GENERAL_SIM_THRESHOLD}")
            print(f"   max exterior frames  : {MAX_EXTERIOR_FRAMES}")
            print("="*60)

        seeds     = self._bucket_seeds(ranked, len(candidates))
        seed_idxs = {fi for fi, _ in seeds}
        remainder = [(fi, sc) for fi, sc in ranked if fi not in seed_idxs]
        ordered   = seeds + remainder  # temporal seeds first

        selected:    List[Tuple[int, Dict]] = []
        sel_frames:  List[np.ndarray]       = []
        sel_is_ext:  List[bool]             = []  # track type of each selected frame
        ext_count  = 0
        skipped    = 0

        def _try_add(fi: int, sc: Dict, gen_threshold: float, ext_threshold: float) -> bool:
            nonlocal ext_count, skipped

            if len(selected) >= num_frames:
                return False

            is_ext = sc.get("is_exterior", False)

            # Hard cap on exterior frames
            if is_ext and ext_count >= MAX_EXTERIOR_FRAMES:
                if verbose:
                    print(f"  🚫 Ext cap — {sc['total']:.0f}pts")
                return False

            # Check similarity against all already-selected frames
            frame = candidates[fi]
            for i, (existing, existing_is_ext) in enumerate(zip(sel_frames, sel_is_ext)):
                # Use stricter threshold when both frames are exterior
                thresh = ext_threshold if (is_ext and existing_is_ext) else gen_threshold
                sim    = self._similarity(frame, existing)
                if sim > thresh:
                    skipped += 1
                    if verbose:
                        t_type = "ext↔ext" if (is_ext and existing_is_ext) else "general"
                        print(f"  ❌ Dup ({t_type} sim={sim:.2f}>{thresh:.2f}) score={sc['total']:.0f}")
                    return False

            # Accept
            selected.append((fi, sc))
            sel_frames.append(frame)
            sel_is_ext.append(is_ext)
            if is_ext:
                ext_count += 1

            tag = "🏠 EXT" if is_ext else "🛋️  INT"
            if verbose:
                print(
                    f"  ✅ #{len(selected):02d}  score={sc['total']:.1f}  "
                    f"ext={sc.get('exterior',0):.0f}  pen={sc.get('penalty',0):.0f}  {tag}"
                )
            return True

        # Pass 1 — strict thresholds
        for fi, sc in ordered:
            _try_add(fi, sc, GENERAL_SIM_THRESHOLD, EXTERIOR_VS_EXTERIOR_SIM)
            if len(selected) >= num_frames:
                break

        # Pass 2 — relax if quota not met
        if len(selected) < num_frames:
            if verbose:
                print(f"\n  ⚠️  {len(selected)}/{num_frames} — relaxing to {SIMILARITY_FALLBACK}…")
            used = {fi for fi, _ in selected}
            for fi, sc in ordered:
                if fi in used:
                    continue
                _try_add(fi, sc, SIMILARITY_FALLBACK, SIMILARITY_FALLBACK)
                if len(selected) >= num_frames:
                    break

        # Pass 3 — last resort: remove ext cap entirely
        if len(selected) < num_frames:
            if verbose:
                print(f"\n  ⚠️  {len(selected)}/{num_frames} — removing ext cap…")
            used = {fi for fi, _ in selected}
            for fi, sc in ordered:
                if fi in used or len(selected) >= num_frames:
                    continue
                frame  = candidates[fi]
                is_ext = sc.get("is_exterior", False)
                # Still use fallback threshold even in emergency
                fits = all(
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
                        print(f"  ➕ Emergency #{len(selected):02d}  {tag}  score={sc['total']:.1f}")

        int_count = len(selected) - ext_count
        if verbose:
            print(f"\n✨ {len(selected)} frames: {ext_count} exterior + {int_count} interior  ({skipped} dups removed)")

        return selected

    # ──────────────────────────────────────────────────────────────────────────
    # I/O (UPDATED for clean saving)
    # ──────────────────────────────────────────────────────────────────────────

    def resize_frame(self, frame: np.ndarray) -> np.ndarray:
        h, w = frame.shape[:2]
        if max(h, w) <= MAX_IMAGE_DIMENSION:
            return frame
        if w >= h:
            nw, nh = MAX_IMAGE_DIMENSION, int(h * MAX_IMAGE_DIMENSION / w)
        else:
            nh, nw = MAX_IMAGE_DIMENSION, int(w * MAX_IMAGE_DIMENSION / h)
        return cv2.resize(frame, (nw, nh), interpolation=cv2.INTER_LANCZOS4)

    def save_frame(self, frame: np.ndarray, video_hash: str, rank: int) -> Path:
        # The frame array passed here is already resized during extraction
        img   = Image.fromarray(cv2.cvtColor(frame, cv2.COLOR_BGR2RGB))
        ts    = int(time.time() * 1000)
        out   = OUTPUT_DIR / f"{video_hash}_frame_{rank:03d}_{ts}.jpg"
        img.save(out, format=OUTPUT_IMAGE_FORMAT, quality=OUTPUT_IMAGE_QUALITY, optimize=True)
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
            return {"success": False, "error": err, "frames": [], "scores": []}

        try:
            if verbose:
                print("\n" + "="*60)
                print("🎬 STEP 1: Candidate Extraction")
                print("="*60)
            candidates, cand_indices = self.extract_candidate_frames(video_path)

            if not candidates:
                return {"success": False, "error": "No usable frames found", "frames": [], "scores": []}

            target = min(num_frames, len(candidates))
            if target < num_frames:
                print(f"⚠️  {len(candidates)} candidates — extracting {target}")

            if verbose:
                print("\n" + "="*60)
                print("🎯 STEP 2: Quality Scoring")
                print("="*60)
            ranked = self.score_frames_parallel(
                candidates, min(len(candidates), target * 6), verbose
            )

            final  = self.filter_for_diversity(ranked, candidates, target, verbose)

            if verbose:
                print("\n" + "="*60)
                print("💾 STEP 3: Saving")
                print("="*60)

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
                    "clip_relevance": round(sc.get("clip_relevance", 0), 2),
                    "exterior_score": round(sc.get("exterior",       0), 2),
                    "penalty":        round(sc.get("penalty",        0), 2),
                    "is_exterior":    is_ext,
                })
                if verbose:
                    tag = "🏠 EXT" if is_ext else "🛋️  INT"
                    print(f"  ✅ #{rank:02d}: {path.name}  {tag}  score={sc['total']:.1f}")

            elapsed = time.time() - t0
            cap = cv2.VideoCapture(str(video_path))
            fv, fc = cap.get(cv2.CAP_PROP_FPS), int(cap.get(cv2.CAP_PROP_FRAME_COUNT))
            meta = {
                "duration_seconds":        round(fc / fv, 2) if fv else 0,
                "fps":                     round(fv, 2),
                "width":                   int(cap.get(cv2.CAP_PROP_FRAME_WIDTH)),
                "height":                  int(cap.get(cv2.CAP_PROP_FRAME_HEIGHT)),
                "total_frames":            fc,
                "candidates_evaluated":    len(candidates),
                "frames_extracted":        len(final),
                "exterior_frames":         sum(1 for s in frame_scores if      s["is_exterior"]),
                "interior_frames":         sum(1 for s in frame_scores if not s["is_exterior"]),
                "processing_time_seconds": round(elapsed, 2),
            }
            cap.release()

            if verbose:
                print(f"\n✨ Done: {len(final)}/{num_frames} frames in {elapsed:.1f}s")

            return {"success": True, "frames": saved_frames, "scores": frame_scores, "metadata": meta, "error": None}

        except Exception as exc:
            import traceback
            tb = traceback.format_exc()
            print(f"\n❌ {exc}\n{tb}")
            return {"success": False, "error": str(exc), "error_details": tb, "frames": [], "scores": []}


# ── Singleton ─────────────────────────────────────────────────────────────────
_instance: Optional[VideoFrameExtractor] = None

def get_extractor() -> VideoFrameExtractor:
    global _instance
    if _instance is None:
        _instance = VideoFrameExtractor()
    return _instance
