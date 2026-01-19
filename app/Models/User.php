<?php

namespace App\Models;

use App\Models\Support\UserFavoriteProperty;
use App\Models\Support\UserNotificationReference;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'username',
        'email',
        'password',
        'phone',
        'place',
        'lat',
        'lng',
        'about_me',
        'photo_image',
        'language',
        'search_preferences',
        'device_tokens',
        'email_verified_at',
        'last_login_at',
        'google_id',
        'role',
        'verification_code',
        'is_verified',
        'remember_token',
        // ✅ NEW: Property interaction tracking fields
        'recently_viewed_properties',
        'last_activity_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
        'search_preferences' => 'array',
        'is_verified' => 'boolean',
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
        'device_tokens' => 'array',
        'verification_code' => 'string',
        // ✅ NEW: Property interaction tracking casts
        'recently_viewed_properties' => 'array',
        'last_activity_at' => 'datetime',
    ];

    protected $dates = [
        'deleted_at',
        'email_verified_at',
        'last_login_at',
        'last_activity_at', // ✅ NEW
    ];

    // ===== UUID Generation =====
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    // ===== RELATIONSHIPS =====
    public function notifications(): HasMany
    {
        return $this->hasMany(UserNotificationReference::class, 'user_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'user_id');
    }

    public function favoriteProperties(): HasMany
    {
        return $this->hasMany(UserFavoriteProperty::class, 'user_id');
    }

    public function ownedProperties(): MorphMany
    {
        return $this->morphMany(Property::class, 'owner');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class, 'user_id');
    }

    public function buyerTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'buyer_user_id');
    }

    public function sellerTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'seller_user_id');
    }

    public function systemNotifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    // ✅ NEW: Property interaction tracking relationships
    public function propertyInteractions(): HasMany
    {
        return $this->hasMany(UserPropertyInteraction::class, 'user_id');
    }

    public function recentPropertyViews(): HasMany
    {
        return $this->hasMany(UserPropertyInteraction::class, 'user_id')
            ->where('interaction_type', 'view')
            ->orderBy('created_at', 'desc')
            ->limit(50);
    }

    // ===== HELPER METHODS =====
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new \Illuminate\Auth\Notifications\VerifyEmail);
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeEmailVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->username;
    }

    /**
     * Get all FCM tokens for this user
     */
    public function getFCMTokens(): array
    {
        $deviceTokens = $this->device_tokens ?? [];
        $fcmTokens = [];

        foreach ($deviceTokens as $device) {
            if (!empty($device['fcm_token'])) {
                $fcmTokens[] = $device['fcm_token'];
            }
        }

        return $fcmTokens;
    }

    /**
     * Check if user is verified
     */
    public function isVerified(): bool
    {
        return $this->is_verified === true;
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    // ===== ✅ NEW: PROPERTY INTERACTION HELPER METHODS =====

    /**
     * Get recently viewed property IDs
     */
    public function getRecentlyViewedPropertyIds(): array
    {
        return $this->recently_viewed_properties ?? [];
    }

    /**
     * Check if user has recently viewed a specific property
     */
    public function hasRecentlyViewed(string $propertyId): bool
    {
        $recentlyViewed = $this->recently_viewed_properties ?? [];
        return in_array($propertyId, $recentlyViewed);
    }

    /**
     * Get count of recently viewed properties
     */
    public function getRecentlyViewedCount(): int
    {
        return count($this->recently_viewed_properties ?? []);
    }

    /**
     * Add a property to recently viewed (manual method)
     * Note: Usually handled by PropertyInteractionService
     */
    public function addToRecentlyViewed(string $propertyId): void
    {
        $recentlyViewed = $this->recently_viewed_properties ?? [];

        // Remove if already exists
        $recentlyViewed = array_filter($recentlyViewed, fn($id) => $id !== $propertyId);

        // Add to beginning
        array_unshift($recentlyViewed, $propertyId);

        // Keep only last 50
        $recentlyViewed = array_slice($recentlyViewed, 0, 50);

        $this->update([
            'recently_viewed_properties' => $recentlyViewed,
            'last_activity_at' => now(),
        ]);
    }

    /**
     * Clear recently viewed properties
     */
    public function clearRecentlyViewed(): void
    {
        $this->update([
            'recently_viewed_properties' => [],
        ]);
    }

    /**
     * Update last activity timestamp
     */
    public function updateLastActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }

    /**
     * Check if user has been active recently (within last N days)
     */
    public function hasRecentActivity(int $days = 7): bool
    {
        if (!$this->last_activity_at) {
            return false;
        }

        return $this->last_activity_at->greaterThanOrEqualTo(now()->subDays($days));
    }

    /**
     * Get user's property viewing statistics
     */
    public function getViewingStatistics(): array
    {
        return [
            'total_views' => $this->propertyInteractions()
                ->where('interaction_type', 'view')
                ->count(),
            'unique_properties_viewed' => $this->propertyInteractions()
                ->where('interaction_type', 'view')
                ->distinct('property_id')
                ->count('property_id'),
            'views_last_7_days' => $this->propertyInteractions()
                ->where('interaction_type', 'view')
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
            'views_last_30_days' => $this->propertyInteractions()
                ->where('interaction_type', 'view')
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
            'recently_viewed_count' => $this->getRecentlyViewedCount(),
            'last_activity' => $this->last_activity_at?->diffForHumans(),
        ];
    }

    /**
     * Scope: Users with recent activity
     */
    public function scopeActiveWithinDays($query, int $days = 7)
    {
        return $query->where('last_activity_at', '>=', now()->subDays($days));
    }

    /**
     * Scope: Users who have viewed properties recently
     */
    public function scopeWithRecentViews($query)
    {
        return $query->whereNotNull('recently_viewed_properties')
            ->whereRaw('JSON_LENGTH(recently_viewed_properties) > 0');
    }
}
