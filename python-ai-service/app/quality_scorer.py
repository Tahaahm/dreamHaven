"""
Quality Scorer Module — CLIP Removed
=====================================
torch / transformers / CLIP completely removed.
Scoring uses pure OpenCV — no ML model needed.
All method signatures kept identical so nothing else breaks.
"""

import cv2
import numpy as np
from typing import Dict, List, Tuple


class FrameQualityScorer:

    def __init__(self):
        print("✓ FrameQualityScorer ready (OpenCV mode — no CLIP)")

    # ── Individual metrics ────────────────────────────────────────────────────

    def calculate_sharpness(self, frame: np.ndarray) -> float:
        """Sharpness via Laplacian variance (0–100)."""
        gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
        laplacian_var = cv2.Laplacian(gray, cv2.CV_64F).var()
        return min(100.0, float(laplacian_var) / 10.0)

    def calculate_brightness(self, frame: np.ndarray) -> float:
        """Brightness quality — penalises over/under exposure (0–100)."""
        hsv = cv2.cvtColor(frame, cv2.COLOR_BGR2HSV)
        mean_b = float(np.mean(hsv[:, :, 2]))
        lo, hi = 110, 160
        if lo <= mean_b <= hi:
            return 100.0
        elif mean_b < lo:
            return max(0.0, (mean_b / lo) * 100.0)
        else:
            return max(0.0, 100.0 - ((mean_b - hi) / (255 - hi)) * 50.0)

    def calculate_composition(self, frame: np.ndarray) -> float:
        """Composition via edge density + colour variety + contrast (0–100)."""
        gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)

        edges      = cv2.Canny(gray, 50, 150)
        edge_score = min(100.0, float(np.sum(edges > 0)) / edges.size * 1000.0)

        hsv         = cv2.cvtColor(frame, cv2.COLOR_BGR2HSV)
        color_score = min(100.0, float(np.std(hsv[:, :, 0])) / 0.5)

        contrast_score = min(100.0, float(gray.std()) / 0.7)

        return edge_score * 0.4 + color_score * 0.3 + contrast_score * 0.3

    def calculate_clip_relevance(self, frame: np.ndarray) -> float:
        """CLIP removed — returns neutral 50.0 for API compatibility."""
        return 50.0

    # ── Combined score ────────────────────────────────────────────────────────

    def score_frame(self, frame: np.ndarray, verbose: bool = False) -> Dict[str, float]:
        """Return dict with sharpness / brightness / composition / clip_relevance / total."""
        scores = {
            "sharpness":      self.calculate_sharpness(frame),
            "brightness":     self.calculate_brightness(frame),
            "composition":    self.calculate_composition(frame),
            "clip_relevance": self.calculate_clip_relevance(frame),
        }

        weights = {
            "sharpness":      0.35,
            "brightness":     0.25,
            "composition":    0.20,
            "clip_relevance": 0.20,
        }

        scores["total"] = sum(scores[k] * weights[k] for k in weights)

        if verbose:
            print(f"\n📊 Frame Quality Scores:")
            print(f"  Sharpness:      {scores['sharpness']:.1f}/100")
            print(f"  Brightness:     {scores['brightness']:.1f}/100")
            print(f"  Composition:    {scores['composition']:.1f}/100")
            print(f"  CLIP Relevance: {scores['clip_relevance']:.1f}/100 (stub)")
            print(f"  ═══════════════════════════════")
            print(f"  TOTAL:          {scores['total']:.1f}/100")

        return scores

    # ── Batch ranking ─────────────────────────────────────────────────────────

    def rank_frames(
        self,
        frames:   List[np.ndarray],
        num_best: int  = 10,
        verbose:  bool = False,
    ) -> List[Tuple[int, Dict[str, float]]]:
        """Score all frames and return top num_best sorted by total score."""
        print(f"\n🔍 Scoring {len(frames)} frames...")
        scored = []

        for idx, frame in enumerate(frames):
            if verbose and (idx + 1) % 10 == 0:
                print(f"  Progress: {idx + 1}/{len(frames)}")
            scored.append((idx, self.score_frame(frame, verbose=False)))

        ranked = sorted(scored, key=lambda x: x[1]["total"], reverse=True)[:num_best]

        if verbose:
            print(f"\n✅ Top {num_best} frames selected:")
            for rank, (idx, sc) in enumerate(ranked, 1):
                print(f"  #{rank} - Frame {idx}: {sc['total']:.1f}/100")

        return ranked

    def cleanup(self):
        """No resources to free (no ML model loaded)."""
        pass


# ── Singleton ──────────────────────────────────────────────────────────────────

_scorer_instance = None


def get_scorer() -> FrameQualityScorer:
    """Get or create singleton scorer instance."""
    global _scorer_instance
    if _scorer_instance is None:
        _scorer_instance = FrameQualityScorer()
    return _scorer_instance
