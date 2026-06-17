<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helper\ApiResponse;
use App\Helper\ResponseDetails;
use App\Models\Property;
use App\Models\UserPropertyInteraction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Services\PropertyInteractionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

/**
 * UserBehaviorController
 *
 * Handles all new intelligence signal endpoints:
 *   POST /api/v1/behavior/scroll-depth
 *   POST /api/v1/behavior/time-on-listing
 *   POST /api/v1/behavior/return-to-listing
 *   POST /api/v1/behavior/photo-gallery-open
 *   POST /api/v1/behavior/contact-intent
 *   POST /api/v1/behavior/share-property
 *   POST /api/v1/behavior/map-pin-tap
 *   POST /api/v1/behavior/search-refinement
 *   POST /api/v1/user/log-interaction-batch   (existing, now improved)
 *
 * All endpoints are:
 *  - Auth optional (guests tracked by session_id)
 *  - Non-blocking (fire-and-forget from Flutter)
 *  - Idempotent safe (duplicates are ignored or aggregated)
 */
class UserBehaviorController extends Controller
{
    protected PropertyInteractionService $interactionService;

    public function __construct(PropertyInteractionService $interactionService)
    {
        $this->interactionService = $interactionService;
    }
    // ── Signal weights used in UserTasteProfile ──────────────────────────────
    // These mirror UserTasteProfile::SIGNAL_WEIGHTS but are documented here
    // so the Flutter team knows what each endpoint is worth.
    //
    //  scroll_depth   (80–100%) → 4.0×   (near-favorite level interest)
    //  scroll_depth   (50–79%)  → 2.0×
    //  scroll_depth   (<50%)    → 0.5×   (counted but low weight)
    //  time_on_listing (60s+)   → 4.0×
    //  time_on_listing (15–59s) → 2.0×
    //  time_on_listing (<5s)    → -1.0×  (NEGATIVE — soft dislike signal)
    //  return_to_listing        → 8.0×   (strongest non-purchase signal)
    //  photo_gallery_open       → 2.5×
    //  contact_intent           → 6.0×
    //  share_property           → 3.5×
    //  map_pin_tap              → 2.0×   (sharpens heat centroid)
    //  search_refinement        → 3.0×   (updates filter_signal on tighten)

    // ────────────────────────────────────────────────────────────────────────
    // 1. SCROLL DEPTH
    // ────────────────────────────────────────────────────────────────────────
    /**
     * POST /api/v1/behavior/scroll-depth
     *
     * Flutter sends this when user scrolls on a property detail page.
     * Called at 25%, 50%, 75%, 100% thresholds (Flutter decides when to fire).
     * We only store the highest depth per session per property.
     *
     * Body: { property_id, scroll_percent (0–100), property_type?, city?, price_usd? }
     */
    public function scrollDepth(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'property_id'   => 'required|string',
                'scroll_percent' => 'required|integer|min:0|max:100',
                'property_type' => 'nullable|string|max:50',
                'city'          => 'nullable|string|max:100',
                'price_usd'     => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error(
                    ResponseDetails::validationErrorMessage(),
                    $validator->errors(),
                    ResponseDetails::CODE_VALIDATION_ERROR
                );
            }

            $user      = Auth::user();
            $userId    = $user?->id;
            $sessionId = session()->getId();
            $pct       = (int) $request->scroll_percent;

            // Only store if this is a higher depth than what we already have
            // for this user+property in the last 24h
            $existing = UserPropertyInteraction::where(function ($q) use ($userId, $sessionId) {
                if ($userId) $q->where('user_id', $userId);
                else         $q->where('session_id', $sessionId);
            })
                ->where('property_id', $request->property_id)
                ->where('interaction_type', 'scroll_depth')
                ->where('created_at', '>=', now()->subHours(24))
                ->latest()
                ->first();

            $existingPct = 0;
            if ($existing) {
                $meta = is_array($existing->metadata)
                    ? $existing->metadata
                    : json_decode($existing->metadata, true);
                $existingPct = (int) ($meta['scroll_percent'] ?? 0);
            }

            // Only update if new depth is higher
            if ($pct <= $existingPct) {
                return ApiResponse::success('Signal acknowledged (no update needed)', null, 200);
            }

            // Derive weight from depth
            $weight = match (true) {
                $pct >= 80 => 4.0,
                $pct >= 50 => 2.0,
                default    => 0.5,
            };

            UserPropertyInteraction::updateOrCreate(
                [
                    'user_id'          => $userId,
                    'session_id'       => $userId ? null : $sessionId,
                    'property_id'      => $request->property_id,
                    'interaction_type' => 'scroll_depth',
                ],
                [
                    'metadata' => json_encode([
                        'scroll_percent' => $pct,
                        'weight'         => $weight,
                        'property_type'  => $request->property_type,
                        'city'           => $request->city,
                        'price_usd'      => $request->price_usd,
                        'updated_at'     => now()->toISOString(),
                    ]),
                    'created_at' => now(),
                ]
            );

            // Invalidate taste profile cache so next rec call gets fresh data
            if ($userId && $pct >= 50) {
                app(\App\Services\Intelligence\UserTasteProfile::class)
                    ->invalidate((string) $userId);
            }

            Log::info("scroll_depth: user={$userId} prop={$request->property_id} pct={$pct}% weight={$weight}");

            return ApiResponse::success('Scroll depth tracked', null, 200);
        } catch (\Throwable $e) {
            Log::warning('scroll_depth signal failed (non-fatal): ' . $e->getMessage());
            return ApiResponse::success('Signal acknowledged', null, 200);
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // 2. TIME ON LISTING
    // ────────────────────────────────────────────────────────────────────────
    /**
     * POST /api/v1/behavior/time-on-listing
     *
     * Flutter sends this on back-button press / page dispose.
     * seconds = time from page open to page close.
     *
     * Body: { property_id, seconds, property_type?, city?, price_usd? }
     */
    public function timeOnListing(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'property_id'   => 'required|string',
                'seconds'       => 'required|integer|min:0|max:3600',
                'property_type' => 'nullable|string|max:50',
                'city'          => 'nullable|string|max:100',
                'price_usd'     => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error(
                    ResponseDetails::validationErrorMessage(),
                    $validator->errors(),
                    ResponseDetails::CODE_VALIDATION_ERROR
                );
            }

            $user      = Auth::user();
            $userId    = $user?->id;
            $sessionId = session()->getId();
            $secs      = (int) $request->seconds;

            // Derive weight + sentiment
            [$weight, $sentiment] = match (true) {
                $secs >= 60 => [4.0,  'strong_interest'],
                $secs >= 15 => [2.0,  'mild_interest'],
                $secs < 5   => [-1.0, 'bounce'],          // NEGATIVE signal
                default     => [0.5,  'casual'],
            };

            UserPropertyInteraction::create([
                'user_id'          => $userId,
                'session_id'       => $userId ? null : $sessionId,
                'property_id'      => $request->property_id,
                'interaction_type' => 'time_on_listing',
                'metadata'         => json_encode([
                    'seconds'        => $secs,
                    'weight'         => $weight,
                    'sentiment'      => $sentiment,
                    'property_type'  => $request->property_type,
                    'city'           => $request->city,
                    'price_usd'      => $request->price_usd,
                ]),
                'created_at' => now(),
            ]);

            // Invalidate taste profile for meaningful signals
            if ($userId && abs($weight) >= 2.0) {
                app(\App\Services\Intelligence\UserTasteProfile::class)
                    ->invalidate((string) $userId);
            }

            Log::info("time_on_listing: user={$userId} prop={$request->property_id} secs={$secs} sentiment={$sentiment}");

            return ApiResponse::success('Time on listing tracked', null, 200);
        } catch (\Throwable $e) {
            Log::warning('time_on_listing signal failed (non-fatal): ' . $e->getMessage());
            return ApiResponse::success('Signal acknowledged', null, 200);
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // 3. RETURN TO LISTING
    // ────────────────────────────────────────────────────────────────────────
    /**
     * POST /api/v1/behavior/return-to-listing
     *
     * Flutter calls this in detail page initState() when it detects
     * the user has viewed this property before (check local cache first,
     * then call this endpoint). 8× weight — strongest non-purchase signal.
     *
     * Body: { property_id, visit_count, property_type?, city?, price_usd? }
     */
    public function returnToListing(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'property_id'   => 'required|string',
                'visit_count'   => 'required|integer|min:2',
                'property_type' => 'nullable|string|max:50',
                'city'          => 'nullable|string|max:100',
                'price_usd'     => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error(
                    ResponseDetails::validationErrorMessage(),
                    $validator->errors(),
                    ResponseDetails::CODE_VALIDATION_ERROR
                );
            }

            $user   = Auth::user();
            $userId = $user?->id;

            if (!$userId) {
                // Guests: still acknowledge but don't store (no user to profile)
                return ApiResponse::success('Signal acknowledged', null, 200);
            }

            // Only store once per property per 24h (prevent duplicate firing)
            $alreadyStoredToday = UserPropertyInteraction::where('user_id', $userId)
                ->where('property_id', $request->property_id)
                ->where('interaction_type', 'return_to_listing')
                ->where('created_at', '>=', now()->subHours(24))
                ->exists();

            if ($alreadyStoredToday) {
                return ApiResponse::success('Signal acknowledged (already tracked today)', null, 200);
            }

            UserPropertyInteraction::create([
                'user_id'          => $userId,
                'property_id'      => $request->property_id,
                'interaction_type' => 'return_to_listing',
                'metadata'         => json_encode([
                    'visit_count'    => $request->visit_count,
                    'weight'         => 8.0,
                    'property_type'  => $request->property_type,
                    'city'           => $request->city,
                    'price_usd'      => $request->price_usd,
                ]),
                'created_at' => now(),
            ]);

            // High-weight signal → always invalidate taste profile
            app(\App\Services\Intelligence\UserTasteProfile::class)
                ->invalidate((string) $userId);

            // Also invalidate SmartStrip cache so re_engagement strip fires
            app(\App\Services\SmartStripService::class)->invalidate((string) $userId);

            Log::info("return_to_listing: user={$userId} prop={$request->property_id} visits={$request->visit_count}");

            return ApiResponse::success('Return to listing tracked', null, 200);
        } catch (\Throwable $e) {
            Log::warning('return_to_listing signal failed (non-fatal): ' . $e->getMessage());
            return ApiResponse::success('Signal acknowledged', null, 200);
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // 4. PHOTO GALLERY OPEN
    // ────────────────────────────────────────────────────────────────────────
    /**
     * POST /api/v1/behavior/photo-gallery-open
     *
     * Flutter calls when user opens the carousel / DetailPhoto page.
     * 2.5× weight. People only browse photos on properties they genuinely like.
     *
     * Body: { property_id, photo_count?, property_type?, city?, price_usd? }
     */
    public function photoGalleryOpen(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'property_id'   => 'required|string',
                'photo_count'   => 'nullable|integer|min:1',
                'property_type' => 'nullable|string|max:50',
                'city'          => 'nullable|string|max:100',
                'price_usd'     => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error(
                    ResponseDetails::validationErrorMessage(),
                    $validator->errors(),
                    ResponseDetails::CODE_VALIDATION_ERROR
                );
            }

            $user      = Auth::user();
            $userId    = $user?->id;
            $sessionId = session()->getId();

            // Deduplicate: only once per property per hour
            $recentExists = UserPropertyInteraction::where(function ($q) use ($userId, $sessionId) {
                if ($userId) $q->where('user_id', $userId);
                else         $q->where('session_id', $sessionId);
            })
                ->where('property_id', $request->property_id)
                ->where('interaction_type', 'photo_gallery_open')
                ->where('created_at', '>=', now()->subHour())
                ->exists();

            if ($recentExists) {
                return ApiResponse::success('Signal acknowledged (deduped)', null, 200);
            }

            UserPropertyInteraction::create([
                'user_id'          => $userId,
                'session_id'       => $userId ? null : $sessionId,
                'property_id'      => $request->property_id,
                'interaction_type' => 'photo_gallery_open',
                'metadata'         => json_encode([
                    'weight'         => 2.5,
                    'photo_count'    => $request->photo_count,
                    'property_type'  => $request->property_type,
                    'city'           => $request->city,
                    'price_usd'      => $request->price_usd,
                ]),
                'created_at' => now(),
            ]);

            if ($userId) {
                app(\App\Services\Intelligence\UserTasteProfile::class)
                    ->invalidate((string) $userId);
            }

            return ApiResponse::success('Gallery open tracked', null, 200);
        } catch (\Throwable $e) {
            Log::warning('photo_gallery_open signal failed (non-fatal): ' . $e->getMessage());
            return ApiResponse::success('Signal acknowledged', null, 200);
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // 5. CONTACT INTENT
    // ────────────────────────────────────────────────────────────────────────
    /**
     * POST /api/v1/behavior/contact-intent
     *
     * Flutter calls when user taps WhatsApp / Call / Message button.
     * 6× weight — highest intent signal short of booking.
     * Also triggers about_to_contact strip check.
     *
     * Body: { property_id, contact_method (whatsapp|call|message), property_type?, city?, price_usd? }
     */
    public function contactIntent(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'property_id'    => 'required|string',
                'contact_method' => 'required|in:whatsapp,call,message,chat',
                'property_type'  => 'nullable|string|max:50',
                'city'           => 'nullable|string|max:100',
                'price_usd'      => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error(
                    ResponseDetails::validationErrorMessage(),
                    $validator->errors(),
                    ResponseDetails::CODE_VALIDATION_ERROR
                );
            }

            $user      = Auth::user();
            $userId    = $user?->id;
            $sessionId = session()->getId();

            UserPropertyInteraction::create([
                'user_id'          => $userId,
                'session_id'       => $userId ? null : $sessionId,
                'property_id'      => $request->property_id,
                'interaction_type' => 'contact_intent',
                'metadata'         => json_encode([
                    'weight'          => 6.0,
                    'contact_method'  => $request->contact_method,
                    'property_type'   => $request->property_type,
                    'city'            => $request->city,
                    'price_usd'       => $request->price_usd,
                ]),
                'created_at' => now(),
            ]);

            if ($userId) {
                app(\App\Services\Intelligence\UserTasteProfile::class)
                    ->invalidate((string) $userId);
                // Force-refresh SmartStrip so about_to_contact fires immediately
                app(\App\Services\SmartStripService::class)->invalidate((string) $userId);
            }

            Log::info("contact_intent: user={$userId} prop={$request->property_id} method={$request->contact_method}");

            return ApiResponse::success('Contact intent tracked', null, 200);
        } catch (\Throwable $e) {
            Log::warning('contact_intent signal failed (non-fatal): ' . $e->getMessage());
            return ApiResponse::success('Signal acknowledged', null, 200);
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // 6. SHARE PROPERTY
    // ────────────────────────────────────────────────────────────────────────
    /**
     * POST /api/v1/behavior/share-property
     *
     * Flutter calls when user shares via WhatsApp / copy link / etc.
     * 3.5× weight. Sharing = showing someone = social purchase signal.
     *
     * Body: { property_id, share_method (whatsapp|copy_link|other), property_type?, city?, price_usd? }
     */
    public function shareProperty(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'property_id'  => 'required|string',
                'share_method' => 'nullable|in:whatsapp,copy_link,other',
                'property_type' => 'nullable|string|max:50',
                'city'         => 'nullable|string|max:100',
                'price_usd'    => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error(
                    ResponseDetails::validationErrorMessage(),
                    $validator->errors(),
                    ResponseDetails::CODE_VALIDATION_ERROR
                );
            }

            $user      = Auth::user();
            $userId    = $user?->id;
            $sessionId = session()->getId();

            UserPropertyInteraction::create([
                'user_id'          => $userId,
                'session_id'       => $userId ? null : $sessionId,
                'property_id'      => $request->property_id,
                'interaction_type' => 'share_property',
                'metadata'         => json_encode([
                    'weight'        => 3.5,
                    'share_method'  => $request->share_method ?? 'other',
                    'property_type' => $request->property_type,
                    'city'          => $request->city,
                    'price_usd'     => $request->price_usd,
                ]),
                'created_at' => now(),
            ]);

            if ($userId) {
                app(\App\Services\Intelligence\UserTasteProfile::class)
                    ->invalidate((string) $userId);
            }

            return ApiResponse::success('Share tracked', null, 200);
        } catch (\Throwable $e) {
            Log::warning('share_property signal failed (non-fatal): ' . $e->getMessage());
            return ApiResponse::success('Signal acknowledged', null, 200);
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // 7. MAP PIN TAP
    // ────────────────────────────────────────────────────────────────────────
    /**
     * POST /api/v1/behavior/map-pin-tap
     *
     * Flutter calls when user taps a property pin on the heat map.
     * 2× weight but most importantly sharpens the heat centroid to
     * neighbourhood level (not just city level).
     *
     * Body: { property_id, lat, lng, property_type?, city?, price_usd? }
     */
    public function mapPinTap(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'property_id'   => 'required|string',
                'lat'           => 'required|numeric|between:-90,90',
                'lng'           => 'required|numeric|between:-180,180',
                'property_type' => 'nullable|string|max:50',
                'city'          => 'nullable|string|max:100',
                'price_usd'     => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error(
                    ResponseDetails::validationErrorMessage(),
                    $validator->errors(),
                    ResponseDetails::CODE_VALIDATION_ERROR
                );
            }

            $user      = Auth::user();
            $userId    = $user?->id;
            $sessionId = session()->getId();

            UserPropertyInteraction::create([
                'user_id'          => $userId,
                'session_id'       => $userId ? null : $sessionId,
                'property_id'      => $request->property_id,
                'interaction_type' => 'map_pin_tap',
                'metadata'         => json_encode([
                    'weight'        => 2.0,
                    'lat'           => (float) $request->lat,
                    'lng'           => (float) $request->lng,
                    'property_type' => $request->property_type,
                    'city'          => $request->city,
                    'price_usd'     => $request->price_usd,
                ]),
                'created_at' => now(),
            ]);

            // Geo signal → always invalidate to refresh heat centroid
            if ($userId) {
                app(\App\Services\Intelligence\UserTasteProfile::class)
                    ->invalidate((string) $userId);
            }

            return ApiResponse::success('Map pin tap tracked', null, 200);
        } catch (\Throwable $e) {
            Log::warning('map_pin_tap signal failed (non-fatal): ' . $e->getMessage());
            return ApiResponse::success('Signal acknowledged', null, 200);
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // 8. SEARCH REFINEMENT
    // ────────────────────────────────────────────────────────────────────────
    /**
     * POST /api/v1/behavior/search-refinement
     *
     * Flutter calls when user tightens filters mid-session
     * (e.g. max_price goes from 120k → 100k, or city is added).
     * Each refinement updates the filter_signal row, just like
     * a normal filter_applied event but marks it as a refinement.
     *
     * Body: { previous_filters{}, new_filters{}, results_count? }
     */
    public function searchRefinement(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'previous_filters' => 'required|array',
                'new_filters'      => 'required|array',
                'results_count'    => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error(
                    ResponseDetails::validationErrorMessage(),
                    $validator->errors(),
                    ResponseDetails::CODE_VALIDATION_ERROR
                );
            }

            $user   = Auth::user();
            $userId = $user?->id;

            if (!$userId) {
                return ApiResponse::success('Signal acknowledged (guest)', null, 200);
            }

            $prev    = $request->previous_filters;
            $new     = $request->new_filters;

            // Detect which direction filters moved
            $tightened = $this->detectTightening($prev, $new);

            // Update filter_signal row (same as filter_applied)
            UserPropertyInteraction::updateOrCreate(
                [
                    'user_id'          => $userId,
                    'property_id'      => 'filter_signal',
                    'interaction_type' => 'filter_applied',
                ],
                [
                    'metadata' => json_encode(array_merge($new, [
                        'is_refinement'   => true,
                        'tightened_fields' => $tightened,
                        'results_count'   => $request->results_count,
                        'updated_at'      => now()->toISOString(),
                    ])),
                    'created_at' => now(),
                ]
            );

            // Store raw refinement event for analytics
            UserPropertyInteraction::create([
                'user_id'          => $userId,
                'property_id'      => 'filter_signal',
                'interaction_type' => 'search_refinement',
                'metadata'         => json_encode([
                    'weight'          => 3.0,
                    'previous'        => $prev,
                    'new'             => $new,
                    'tightened'       => $tightened,
                    'results_count'   => $request->results_count,
                ]),
                'created_at' => now(),
            ]);

            app(\App\Services\Intelligence\UserTasteProfile::class)
                ->invalidate((string) $userId);
            app(\App\Services\SmartStripService::class)->invalidate((string) $userId);

            Log::info("search_refinement: user={$userId} tightened=" . implode(',', $tightened));

            return ApiResponse::success('Search refinement tracked', null, 200);
        } catch (\Throwable $e) {
            Log::warning('search_refinement signal failed (non-fatal): ' . $e->getMessage());
            return ApiResponse::success('Signal acknowledged', null, 200);
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // 9. BATCH INTERACTION LOG (existing endpoint, now improved)
    // ────────────────────────────────────────────────────────────────────────
    /**
     * POST /api/v1/user/log-interaction-batch
     *
     * Existing endpoint used by PropertyBloc for filter interaction logging.
     * Now also handles the new signal types if they arrive in a batch.
     *
     * Body: { interactions: [ { filters{}, results_count, ts } ] }
     */
    public function logInteractionBatch(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'interactions'   => 'required|array|min:1|max:100',
                'interactions.*' => 'required|array',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error(
                    ResponseDetails::validationErrorMessage(),
                    $validator->errors(),
                    ResponseDetails::CODE_VALIDATION_ERROR
                );
            }

            $user   = Auth::user();
            $userId = $user?->id;

            if (!$userId) {
                return ApiResponse::success('Batch acknowledged (guest)', null, 200);
            }

            $now   = now();
            $count = 0;

            foreach ($request->interactions as $interaction) {
                $filters      = $interaction['filters'] ?? [];
                $resultsCount = $interaction['results_count'] ?? null;

                if (empty($filters)) continue;

                // Upsert the filter_signal row (latest wins)
                UserPropertyInteraction::updateOrCreate(
                    [
                        'user_id'          => $userId,
                        'property_id'      => 'filter_signal',
                        'interaction_type' => 'filter_applied',
                    ],
                    [
                        'metadata' => json_encode(array_merge($filters, [
                            'results_count' => $resultsCount,
                            'updated_at'    => $now->toISOString(),
                        ])),
                        'created_at' => $now,
                    ]
                );
                $count++;
            }

            if ($count > 0) {
                app(\App\Services\Intelligence\UserTasteProfile::class)
                    ->invalidate((string) $userId);
            }

            return ApiResponse::success('Batch logged', ['count' => $count], 200);
        } catch (\Throwable $e) {
            Log::warning('log-interaction-batch failed (non-fatal): ' . $e->getMessage());
            return ApiResponse::success('Batch acknowledged', null, 200);
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Detect which filter fields became more restrictive.
     * Returns array of field names that tightened.
     */
    private function detectTightening(array $prev, array $new): array
    {
        $tightened = [];

        // Price ceiling dropped
        $prevMax = (float) ($prev['max_price'] ?? $prev['max_price_usd'] ?? 0);
        $newMax  = (float) ($new['max_price']  ?? $new['max_price_usd']  ?? 0);
        if ($prevMax > 0 && $newMax > 0 && $newMax < $prevMax) {
            $tightened[] = 'max_price';
        }

        // Price floor raised
        $prevMin = (float) ($prev['min_price'] ?? $prev['min_price_usd'] ?? 0);
        $newMin  = (float) ($new['min_price']  ?? $new['min_price_usd']  ?? 0);
        if ($newMin > $prevMin) {
            $tightened[] = 'min_price';
        }

        // City added
        if (empty($prev['city']) && !empty($new['city'])) {
            $tightened[] = 'city';
        }

        // Property type added
        $prevType = strtolower($prev['property_type'] ?? 'all');
        $newType  = strtolower($new['property_type']  ?? 'all');
        if (in_array($prevType, ['', 'all']) && !in_array($newType, ['', 'all'])) {
            $tightened[] = 'property_type';
        }

        // Listing type added
        $prevListing = strtolower($prev['listing_type'] ?? 'all');
        $newListing  = strtolower($new['listing_type']  ?? 'all');
        if (in_array($prevListing, ['', 'all']) && !in_array($newListing, ['', 'all'])) {
            $tightened[] = 'listing_type';
        }

        // Bedrooms increased
        $prevBeds = (int) ($prev['bedrooms'] ?? 0);
        $newBeds  = (int) ($new['bedrooms']  ?? 0);
        if ($newBeds > $prevBeds) {
            $tightened[] = 'bedrooms';
        }

        return $tightened;
    }
    public function trackSearch(Request $request): JsonResponse
    {
        $user     = auth('sanctum')->user();
        $userId   = $user ? $user->id : 'guest_' . session()->getId();
        $query    = trim($request->input('query', ''));
        $results  = (int) $request->input('results_count', 0);
        $filters  = $request->input('active_filters', []);
        $language = $request->input('language', 'en');
        $propIds  = $request->input('property_ids', []);

        if (empty($query) && empty($filters)) {
            return response()->json(['success' => true]);
        }





















        // Capture for closure — don't pass $request into terminating()
        $authUserId = $user?->id;
        $now        = now();

        app()->terminating(function () use (
            $authUserId,
            $userId,
            $query,
            $results,
            $filters,
            $language,
            $propIds,
            $now
        ) {
            try {
                if ($authUserId) {
                    // (a) append-only log
                    UserPropertyInteraction::create([
                        'user_id'          => $authUserId,
                        'property_id'      => 'search_signal',
                        'interaction_type' => 'search_query',
                        'metadata'         => json_encode([
                            'query'          => $query,
                            'results_count'  => $results,
                            'active_filters' => $filters,
                            'language'       => $language,
                            'timestamp'      => $now->toISOString(),
                        ]),
                        'created_at' => $now,
                    ]);

                    // (b) latest upsert for rec engine
                    UserPropertyInteraction::updateOrCreate(
                        [
                            'user_id'          => $authUserId,
                            'property_id'      => 'search_signal_latest',
                            'interaction_type' => 'search_query_latest',
                        ],
                        [
                            'metadata'   => json_encode([
                                'query'          => $query,
                                'results_count'  => $results,
                                'active_filters' => $filters,
                                'language'       => $language,
                                'updated_at'     => $now->toISOString(),
                            ]),
                            'created_at' => $now,
                        ]
                    );

                    Cache::forget("personalized_recs_{$authUserId}");
                }

                if (!empty($propIds)) {
                    $this->interactionService->trackSearchImpressions(
                        userId: $userId,
                        propertyIds: $propIds,
                        searchQuery: $query,
                        activeFilters: $filters
                    );
                }
            } catch (\Throwable $e) {
                Log::warning('track-search failed', ['error' => $e->getMessage()]);
            }
        });

        return response()->json(['success' => true]);
    }
    public function storeCalculatorSignal(Request $request): JsonResponse
    {
        $user = auth('sanctum')->user();
        if (!$user) return response()->json(['success' => true]);

        $payload = [
            'userId'       => $user->id,
            'targetPrice'  => (float) $request->input('target_price_usd', 0),
            'savedSoFar'   => (float) $request->input('saved_so_far_usd', 0),
            'monthly'      => (float) $request->input('monthly_usd', 0),
            'targetYears'  => (int)   $request->input('target_years', 0),
            'mode'         => $request->input('mode', 'how_long'),
        ];

        // Respond immediately, write after
        app()->terminating(function () use ($payload) {
            try {
                $this->interactionService->storeCalculatorSignal(
                    userId: $payload['userId'],
                    targetPriceUsd: $payload['targetPrice'],
                    savedSoFarUsd: $payload['savedSoFar'],
                    monthlyUsd: $payload['monthly'],
                    targetYears: $payload['targetYears'],
                    mode: $payload['mode'],
                );
            } catch (\Throwable $e) {
                Log::warning('calculator-signal failed', ['error' => $e->getMessage()]);
            }
        });

        return response()->json(['success' => true]);
    }
    public function trackSearchClick(Request $request): JsonResponse
    {
        $user = auth('sanctum')->user();
        if (!$user) return response()->json(['success' => true]);

        $validator = Validator::make($request->all(), [
            'property_id'     => 'required|string',
            'search_query'    => 'nullable|string|max:500',
            'result_position' => 'nullable|integer|min:0',
            'active_filters'  => 'nullable|array',
            'search_session'  => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => true]);
        }












        $propertyId    = $request->input('property_id');
        $searchQuery   = trim($request->input('search_query', ''));
        $position      = (int) $request->input('result_position', 0);
        $activeFilters = $request->input('active_filters', []);
        $searchSession = $request->input('search_session');
        $userId        = $user->id;
        $userAgent     = $request->header('User-Agent');

        // ── Only DB read stays synchronous (it's a lightweight EXISTS check) ──
        $exists = Cache::remember(
            "prop_active_{$propertyId}",
            300, // cache for 5 min — property active status rarely changes
            fn() => Property::where('id', $propertyId)
                ->where('is_active', true)
                ->where('published', true)
                ->exists()
        );

        if (!$exists) {
            return response()->json(['success' => true]);
        }

        // Both writes go after response
        app()->terminating(function () use (
            $userId,
            $propertyId,
            $searchQuery,
            $position,
            $activeFilters,
            $searchSession,
            $userAgent
        ) {
            try {
                $this->interactionService->trackSearchClick(
                    userId: $userId,
                    propertyId: $propertyId,
                    searchQuery: $searchQuery,
                    resultPosition: $position,
                    activeFilters: array_merge($activeFilters, [
                        'search_session' => $searchSession,
                    ])
                );

                $this->interactionService->trackView($userId, $propertyId, [
                    'source'          => 'search_click',
                    'search_query'    => $searchQuery,
                    'result_position' => $position,
                    'user_agent'      => $userAgent,
                ]);
            } catch (\Throwable $e) {
                Log::warning('track-search-click failed', ['error' => $e->getMessage()]);
            }
        });

        return response()->json(['success' => true, 'property_id' => $propertyId]);
    }
    public function trackFilter(Request $request): JsonResponse
    {
        $user = auth('sanctum')->user();
        if (!$user) return response()->json(['success' => true]);

        $filters      = $request->input('filters', []);
        $resultsCount = (int) $request->input('results_count', 0);

        if (empty($filters)) return response()->json(['success' => true]);

        $userId  = $user->id;
        $signals = array_filter([
            'listing_type'  => $filters['listing_type']  ?? null,
            'property_type' => $filters['property_type'] ?? null,
            'city'          => $filters['city']          ?? null,
            'min_price_usd' => isset($filters['min_price']) ? (float) $filters['min_price'] : null,
            'max_price_usd' => isset($filters['max_price']) ? (float) $filters['max_price'] : null,
            'bedrooms'      => isset($filters['bedrooms'])  ? (int) $filters['bedrooms']    : null,
            'bathrooms'     => isset($filters['bathrooms']) ? (int) $filters['bathrooms']   : null,
            'furnished'     => $filters['furnished']  ?? null,
            'has_pool'      => $filters['has_pool']    ?? null,
            'has_parking'   => $filters['has_parking'] ?? null,
            'has_gym'       => $filters['has_gym']     ?? null,
            'has_garden'    => $filters['has_garden']  ?? null,
            'has_balcony'   => $filters['has_balcony'] ?? null,
            'results_count' => $resultsCount,
            'updated_at'    => now()->toISOString(),
        ], fn($v) => $v !== null && $v !== '' && $v !== false);

        app()->terminating(function () use ($userId, $signals) {
            try {
                UserPropertyInteraction::updateOrCreate(
                    [
                        'user_id'          => $userId,
                        'property_id'      => 'filter_signal',
                        'interaction_type' => 'filter_applied',
                    ],
                    [
                        'metadata'   => json_encode($signals),
                        'created_at' => now(),
                    ]
                );






                Cache::forget("personalized_recs_{$userId}");
            } catch (\Throwable $e) {
                Log::warning('track-filter failed', ['error' => $e->getMessage()]);
            }
        });

        return response()->json(['success' => true]);
    }
    public function trackCompare(Request $request): JsonResponse
    {
        $user = auth('sanctum')->user();
        if (!$user) return response()->json(['success' => true]);


        $propertyIds = $request->input('property_ids', []);
        $properties  = $request->input('properties', []);

        if (count($propertyIds) < 2) return response()->json(['success' => true]);

        $userId         = $user->id;
        $compareSession = (string) Str::uuid();
        $now            = now();


        // Build propMap from request data only — no DB call
        $propMap = [];
        foreach ($properties as $p) {
            if (!empty($p['id'])) $propMap[$p['id']] = $p;
        }

        app()->terminating(function () use (
            $userId,
            $propertyIds,
            $propMap,
            $compareSession,
            $now
        ) {
            try {
                // If Flutter didn't send property details, load from DB here
                // (inside terminating = after response, so it's fine)
                if (empty($propMap)) {
                    $dbProps = Property::whereIn('id', $propertyIds)
                        ->select('id', 'type', 'price', 'address_details', 'listing_type')
                        ->get();
                    foreach ($dbProps as $p) {
                        $propMap[$p->id] = [
                            'id'           => $p->id,
                            'type'         => $p->type['category']              ?? null,
                            'price_usd'    => $p->price['usd']                  ?? null,
                            'city'         => $p->address_details['city']['en'] ?? null,
                            'listing_type' => $p->listing_type,
                        ];
                    }
                }

                $insertRows = [];
                foreach ($propertyIds as $pid) {
                    $meta = $propMap[$pid] ?? [];
                    $insertRows[] = [
                        'user_id'          => $userId,
                        'property_id'      => $pid,
                        'interaction_type' => 'compare',
                        'metadata'         => json_encode([
                            'compare_session' => $compareSession,
                            'compared_with'   => array_values(
                                array_filter($propertyIds, fn($id) => $id !== $pid)
                            ),
                            'property_type' => $meta['type']         ?? null,
                            'price_usd'     => $meta['price_usd']    ?? null,
                            'city'          => $meta['city']         ?? null,
                            'listing_type'  => $meta['listing_type'] ?? null,
                            'timestamp'     => $now->toISOString(),
                        ]),
                        'created_at' => $now,
                    ];
                }


                UserPropertyInteraction::insert($insertRows);
                Cache::forget("personalized_recs_{$userId}");
                $this->interactionService->bustPopularityCache();
            } catch (\Throwable $e) {
                Log::warning('track-compare failed', ['error' => $e->getMessage()]);
            }
        });

        return response()->json(['success' => true]);
    }
}
