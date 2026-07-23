<?php

namespace App\Services;

use App\Services\Concerns\BuildsPersonalizedRecommendations;
use App\Services\Concerns\ComputesPropertyPopularity;
use App\Services\Concerns\CuratesFeaturedProperties;
use App\Services\Concerns\TracksUserSignals;

/**
 * Central service for recording user/property interactions and turning
 * them into recommendations. Same class name, same namespace, same public
 * API as before — every controller that type-hints or resolves
 * PropertyInteractionService is unaffected. The implementation is now
 * organized into focused traits under Services/Concerns/ instead of one
 * 1,054-line file:
 *
 *  - TracksUserSignals               view/search-click/search-impression/
 *                                     calculator/contact-intent tracking,
 *                                     plus the signal-reading helpers
 *  - ComputesPropertyPopularity       the CTR/velocity-weighted popularity
 *                                     score used by "popular" + as an input
 *                                     to "featured"
 *  - CuratesFeaturedProperties        the two-layer (boosted + contextual)
 *                                     featured-properties engine
 *  - BuildsPersonalizedRecommendations "recommended for you", backed by
 *                                     UserTasteProfile
 *
 * One method from the original file, a private getBudgetMatchedRecommendations(),
 * was confirmed dead (defined but never called from anywhere) and was
 * dropped rather than carried over — it never executed either way, so this
 * has no effect on behavior.
 */
class PropertyInteractionService
{
    use TracksUserSignals;
    use ComputesPropertyPopularity;
    use CuratesFeaturedProperties;
    use BuildsPersonalizedRecommendations;
}
