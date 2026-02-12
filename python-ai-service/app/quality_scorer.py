"""
Quality Scorer Module
Evaluates frame quality based on multiple metrics:
- Sharpness (Laplacian variance)
- Brightness/Exposure
- Composition
- Color balance
"""

import cv2
import numpy as np
from PIL import Image
from typing import Dict, List, Tuple
import torch
from transformers import CLIPProcessor, CLIPModel
from app.config import (
    CLIP_MODEL_NAME,
    DEVICE,
    SCORE_WEIGHTS,
    REAL_ESTATE_PROMPTS,
    MODEL_CACHE_DIR
)


class FrameQualityScorer:
    """
    Comprehensive frame quality assessment for real estate imagery
    """

    def __init__(self):
        """Initialize CLIP model and processor"""
        print(f"Loading CLIP model: {CLIP_MODEL_NAME} on {DEVICE}...")

        self.device = DEVICE
        self.model = CLIPModel.from_pretrained(
            CLIP_MODEL_NAME,
            cache_dir=str(MODEL_CACHE_DIR)
        ).to(self.device)

        self.processor = CLIPProcessor.from_pretrained(
            CLIP_MODEL_NAME,
            cache_dir=str(MODEL_CACHE_DIR)
        )

        self.model.eval()  # Set to evaluation mode
        print("✓ CLIP model loaded successfully")

    def calculate_sharpness(self, frame: np.ndarray) -> float:
        """
        Calculate sharpness using Laplacian variance
        Higher values = sharper image

        Args:
            frame: OpenCV image (BGR)

        Returns:
            Sharpness score (0-100)
        """
        gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
        laplacian_var = cv2.Laplacian(gray, cv2.CV_64F).var()

        # Normalize to 0-100 scale (empirically determined threshold)
        normalized = min(100, laplacian_var / 10)
        return normalized

    def calculate_brightness(self, frame: np.ndarray) -> float:
        """
        Calculate brightness and exposure quality
        Penalizes over/under-exposed images

        Args:
            frame: OpenCV image (BGR)

        Returns:
            Brightness score (0-100)
        """
        # Convert to HSV and get V (brightness) channel
        hsv = cv2.cvtColor(frame, cv2.COLOR_BGR2HSV)
        v_channel = hsv[:, :, 2]

        mean_brightness = np.mean(v_channel)

        # Optimal brightness is around 110-160 (out of 255)
        # Penalize too dark or too bright
        optimal_range = (110, 160)

        if optimal_range[0] <= mean_brightness <= optimal_range[1]:
            score = 100
        elif mean_brightness < optimal_range[0]:
            # Too dark
            score = max(0, (mean_brightness / optimal_range[0]) * 100)
        else:
            # Too bright
            excess = mean_brightness - optimal_range[1]
            penalty = (excess / (255 - optimal_range[1])) * 50
            score = max(0, 100 - penalty)

        return score

    def calculate_composition(self, frame: np.ndarray) -> float:
        """
        Evaluate composition using:
        - Rule of thirds
        - Edge detection (interesting content)
        - Color variety

        Args:
            frame: OpenCV image (BGR)

        Returns:
            Composition score (0-100)
        """
        gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)

        # 1. Edge detection (content interest)
        edges = cv2.Canny(gray, 50, 150)
        edge_density = np.sum(edges > 0) / edges.size
        edge_score = min(100, edge_density * 1000)  # Normalize

        # 2. Color variety (HSV)
        hsv = cv2.cvtColor(frame, cv2.COLOR_BGR2HSV)
        h_channel = hsv[:, :, 0]
        color_variety = np.std(h_channel)
        color_score = min(100, color_variety / 0.5)

        # 3. Contrast
        contrast = gray.std()
        contrast_score = min(100, contrast / 0.7)

        # Combine scores
        composition_score = (
            edge_score * 0.4 +
            color_score * 0.3 +
            contrast_score * 0.3
        )

        return composition_score

    @torch.no_grad()
    def calculate_clip_relevance(self, frame: np.ndarray) -> float:
        """
        Calculate semantic relevance to real estate using CLIP

        Args:
            frame: OpenCV image (BGR)

        Returns:
            CLIP relevance score (0-100)
        """
        try:
            # Convert BGR to RGB
            rgb_frame = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
            pil_image = Image.fromarray(rgb_frame)

            # Process image and prompts
            inputs = self.processor(
                text=REAL_ESTATE_PROMPTS,
                images=pil_image,
                return_tensors="pt",
                padding=True
            ).to(self.device)

            # Get CLIP embeddings
            outputs = self.model(**inputs)
            logits_per_image = outputs.logits_per_image
            probs = logits_per_image.softmax(dim=1)

            # Get maximum probability across all prompts
            max_prob = probs.max().item()

            # Convert to 0-100 scale
            score = max_prob * 100

            return score

        except Exception as e:
            print(f"Warning: CLIP scoring failed: {e}")
            return 50.0  # Return neutral score on error

    def score_frame(self, frame: np.ndarray, verbose: bool = False) -> Dict[str, float]:
        """
        Calculate comprehensive quality score for a frame

        Args:
            frame: OpenCV image (BGR)
            verbose: Print detailed scores

        Returns:
            Dictionary with individual scores and total
        """
        scores = {
            "sharpness": self.calculate_sharpness(frame),
            "brightness": self.calculate_brightness(frame),
            "composition": self.calculate_composition(frame),
            "clip_relevance": self.calculate_clip_relevance(frame),
        }

        # Calculate weighted total
        total_score = sum(
            scores[metric] * SCORE_WEIGHTS[metric]
            for metric in SCORE_WEIGHTS.keys()
        )

        scores["total"] = total_score

        if verbose:
            print(f"\n📊 Frame Quality Scores:")
            print(f"  Sharpness:      {scores['sharpness']:.1f}/100")
            print(f"  Brightness:     {scores['brightness']:.1f}/100")
            print(f"  Composition:    {scores['composition']:.1f}/100")
            print(f"  CLIP Relevance: {scores['clip_relevance']:.1f}/100")
            print(f"  ═══════════════════════════")
            print(f"  TOTAL:          {scores['total']:.1f}/100")

        return scores

    def rank_frames(
        self,
        frames: List[np.ndarray],
        num_best: int = 10,
        verbose: bool = False
    ) -> List[Tuple[int, Dict[str, float]]]:
        """
        Rank multiple frames by quality

        Args:
            frames: List of OpenCV images
            num_best: Number of top frames to return
            verbose: Print progress

        Returns:
            List of (frame_index, scores_dict) tuples, sorted by score
        """
        scored_frames = []

        total_frames = len(frames)
        print(f"\n🔍 Scoring {total_frames} frames...")

        for idx, frame in enumerate(frames):
            if verbose and (idx + 1) % 10 == 0:
                print(f"  Progress: {idx + 1}/{total_frames}")

            scores = self.score_frame(frame, verbose=False)
            scored_frames.append((idx, scores))

        # Sort by total score (descending)
        ranked = sorted(scored_frames, key=lambda x: x[1]["total"], reverse=True)

        # Return top N
        top_frames = ranked[:num_best]

        if verbose:
            print(f"\n✅ Top {num_best} frames selected:")
            for rank, (idx, scores) in enumerate(top_frames, 1):
                print(f"  #{rank} - Frame {idx}: {scores['total']:.1f}/100")

        return top_frames

    def cleanup(self):
        """Free up resources"""
        del self.model
        del self.processor
        torch.cuda.empty_cache() if torch.cuda.is_available() else None
        print("✓ QualityScorer resources cleaned up")


# Singleton instance
_scorer_instance = None


def get_scorer() -> FrameQualityScorer:
    """Get or create singleton scorer instance"""
    global _scorer_instance
    if _scorer_instance is None:
        _scorer_instance = FrameQualityScorer()
    return _scorer_instance
