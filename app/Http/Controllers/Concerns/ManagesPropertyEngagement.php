<?php

namespace App\Http\Controllers\Concerns;

use App\Helper\ApiResponse;
use App\Models\Property;
use App\Models\UserPropertyInteraction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Favorites and lightweight engagement-tracking endpoints (view/WhatsApp/share
 * pings from the property detail screen). Extracted from PropertyController
 * as-is — no behavior changed, only relocated into its own file so the main
 * controller isn't one giant file. Every route still resolves to
 * PropertyController@methodName exactly as before; PHP traits are compiled
 * into the class that uses them, so this is transparent to routing.
 *
 * Depends on $this->transformPropertyData() and $this->bustFeaturedCache()
 * (defined on PropertyController / other Concerns it uses) and
 * $this->interactionService (injected in PropertyController's constructor).
 */
trait ManagesPropertyEngagement
{
    public function getFavoriteProperties(Request $request)
    {
        try {
            $user = auth('sanctum')->user();

            if (!$user) {
                return ApiResponse::error('Authentication required', null, 401);
            }

            // ✅ Use user_property_interactions table (which EXISTS on your server)
            // interaction_type = 'favorite' means they favorited it
            $favoritePropertyIds = \App\Models\UserPropertyInteraction::where('user_id', $user->id)
                ->where('interaction_type', 'favorite')
                ->orderByDesc('created_at')
                ->pluck('property_id')
                ->unique()
                ->values();

            Log::info('💛 FAVORITES: user=' . $user->id . ' ids=' . $favoritePropertyIds->count());

            if ($favoritePropertyIds->isEmpty()) {
                return ApiResponse::success(
                    'No favorite properties found',
                    [],
                    200
                );
            }

            $properties = Property::whereIn('id', $favoritePropertyIds)
                ->where('is_active', true)
                ->orderByDesc('created_at')
                ->get();

            $transformedData = $properties->map(function ($property) {
                return $this->transformPropertyData($property);
            });

            return ApiResponse::success(
                'Favorite properties retrieved',
                $transformedData,
                200
            );
        } catch (\Exception $e) {
            Log::error('❌ Favorites error', ['message' => $e->getMessage()]);
            return ApiResponse::error('Failed to get favorites', $e->getMessage(), 500);
        }
    }

    public function addToFavorites($id)
    {
        try {
            $property = Property::find($id);
            if (!$property) {
                return ApiResponse::error('Property not found', ['id' => $id], 404);
            }

            $user = auth('sanctum')->user();
            if (!$user) {
                return ApiResponse::error('Authentication required', null, 401);
            }

            $exists = \App\Models\UserPropertyInteraction::where('user_id', $user->id)
                ->where('property_id', $property->id)
                ->where('interaction_type', 'favorite')
                ->exists();

            if ($exists) {
                return ApiResponse::success(
                    'Property already in favorites',
                    ['id' => $property->id, 'favorites_count' => $property->favorites_count],
                    200
                );
            }

            \App\Models\UserPropertyInteraction::create([
                'user_id'          => $user->id,
                'property_id'      => $property->id,
                'interaction_type' => 'favorite',
                'metadata'         => json_encode([
                    'timestamp' => now()->toDateTimeString(),
                    'source'    => request()->header('X-Source', 'app'),
                ]),
            ]);

            $property->increment('favorites_count');

            $analytics = $property->favorites_analytics ?? [];
            $analytics['last_30_days'] = ($analytics['last_30_days'] ?? 0) + 1;
            $property->favorites_analytics = $analytics;
            $property->save();

            $this->bustFeaturedCache();

            return ApiResponse::success(
                'Property added to favorites',
                ['id' => $property->id, 'favorites_count' => $property->fresh()->favorites_count],
                200
            );
        } catch (\Exception $e) {
            Log::error('Add to favorites error', [
                'message'     => $e->getMessage(),
                'property_id' => $id
            ]);
            return ApiResponse::error('Failed to add to favorites', $e->getMessage(), 500);
        }
    }

    public function removeFromFavorites($id)
    {
        try {
            $property = Property::find($id);
            if (!$property) {
                return ApiResponse::error('Property not found', ['id' => $id], 404);
            }

            $user = auth('sanctum')->user();
            if (!$user) {
                return ApiResponse::error('Authentication required', null, 401);
            }

            $deleted = \App\Models\UserPropertyInteraction::where('user_id', $user->id)
                ->where('property_id', $property->id)
                ->where('interaction_type', 'favorite')
                ->delete();

            if (!$deleted) {
                return ApiResponse::success(
                    'Property was not in favorites',
                    ['id' => $property->id, 'favorites_count' => $property->favorites_count],
                    200
                );
            }

            if ($property->favorites_count > 0) {
                $property->decrement('favorites_count');
            }

            $this->bustFeaturedCache();

            return ApiResponse::success(
                'Property removed from favorites',
                ['id' => $property->id, 'favorites_count' => $property->fresh()->favorites_count],
                200
            );
        } catch (\Exception $e) {
            Log::error('Remove from favorites error', [
                'message'     => $e->getMessage(),
                'property_id' => $id
            ]);
            return ApiResponse::error('Failed to remove from favorites', $e->getMessage(), 500);
        }
    }

    public function trackView($id)
    {
        try {
            $property = Property::find($id);
            if (!$property) return ApiResponse::error('Not found', null, 404);

            $user = auth('sanctum')->user();
            if (!$user) return ApiResponse::success('OK', null, 200);

            $alreadyViewedToday = \App\Models\UserPropertyInteraction::where('user_id', $user->id)
                ->where('property_id', $property->id)
                ->where('interaction_type', 'view')
                ->whereDate('created_at', today())
                ->exists();

            if (!$alreadyViewedToday) {
                $property->increment('views');

                \App\Models\UserPropertyInteraction::create([
                    'user_id'          => $user->id,
                    'property_id'      => $property->id,
                    'interaction_type' => 'view',
                    'created_at'       => now(),
                ]);
            }

            return ApiResponse::success('View tracked', null, 200);
        } catch (\Exception $e) {
            Log::error('Track view error', ['message' => $e->getMessage()]);
            return ApiResponse::error('Failed', $e->getMessage(), 500);
        }
    }

    public function trackWhatsAppContact(Request $request, $id)
    {
        try {
            $property = Property::find($id);
            if (!$property) {
                return ApiResponse::error('Property not found', null, 404);
            }

            $user   = auth('sanctum')->user();
            $userId = $user ? $user->id : null;

            $meta = [
                'property_id'   => $id,
                'property_type' => $property->type['category'] ?? null,
                'listing_type'  => $property->listing_type,
                'price_usd'     => $property->price['usd'] ?? null,
                'city'          => $property->address_details['city']['en'] ?? null,
                'owner_id'      => $property->owner_id,
                'owner_type'    => $property->owner_type,
                'timestamp'     => now()->toISOString(),
                'source'        => $request->header('X-Source', 'app'),
            ];

            UserPropertyInteraction::create([
                'user_id'          => $userId,
                'session_id'       => $userId ? null : session()->getId(),
                'property_id'      => $id,
                'interaction_type' => 'contact_whatsapp',
                'metadata'         => $meta,
                'created_at'       => now(),
            ]);

            // Also fire the existing contact_intent signal for the taste profile
            if ($userId) {
                $this->interactionService->trackContactIntent(
                    userId: $userId,
                    propertyId: $id,
                    method: 'whatsapp',
                    propertyType: $property->type['category'] ?? null,
                    city: $property->address_details['city']['en'] ?? null,
                    priceUsd: isset($property->price['usd']) ? (float) $property->price['usd'] : null,
                );
            }

            return ApiResponse::success('WhatsApp contact tracked', null, 200);
        } catch (\Exception $e) {
            Log::error('trackWhatsAppContact error', ['message' => $e->getMessage()]);
            return ApiResponse::error('Failed to track contact', $e->getMessage(), 500);
        }
    }

    public function trackShareIntent(Request $request, $id)
    {
        try {
            $property = Property::find($id);
            if (!$property) {
                return ApiResponse::error('Property not found', null, 404);
            }

            $user   = auth('sanctum')->user();
            $userId = $user ? $user->id : null;

            $shareMethod = $request->input('share_method', 'other'); // 'whatsapp' | 'copy_link' | 'other'

            $meta = [
                'property_id'   => $id,
                'share_method'  => $shareMethod,
                'property_type' => $property->type['category'] ?? null,
                'listing_type'  => $property->listing_type,
                'price_usd'     => $property->price['usd'] ?? null,
                'city'          => $property->address_details['city']['en'] ?? null,
                'owner_id'      => $property->owner_id,
                'owner_type'    => $property->owner_type,
                'timestamp'     => now()->toISOString(),
                'source'        => $request->header('X-Source', 'app'),
            ];

            UserPropertyInteraction::create([
                'user_id'          => $userId,
                'session_id'       => $userId ? null : session()->getId(),
                'property_id'      => $id,
                'interaction_type' => 'share',
                'metadata'         => $meta,
                'created_at'       => now(),
            ]);

            return ApiResponse::success('Share tracked', null, 200);
        } catch (\Exception $e) {
            Log::error('trackShareIntent error', ['message' => $e->getMessage()]);
            return ApiResponse::error('Failed to track share', $e->getMessage(), 500);
        }
    }
}
