<?php

namespace App\Models;

use App\Models\Support\UserFavoriteProperty;
use App\Models\Support\UserNotificationReference;
use App\Services\User\UserAppointmentService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The primary key type
     */
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'google_id',           // ← Added for Google OAuth
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
        'last_login_at',       // ← Added for tracking last login
        'is_active'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',     // ← Added cast
        'password' => 'hashed',
        'search_preferences' => 'array',
        'is_active' => 'boolean',
        'lat' => 'decimal:6',
        'lng' => 'decimal:6',
        'device_tokens' => 'array',
    ];

    /**
     * The attributes that should be treated as dates.
     *
     * @var array<string>
     */
    protected $dates = [
        'deleted_at',
        'email_verified_at',
        'last_login_at',       // ← Added to dates
    ];

    // ===== RELATIONSHIPS =====

    /**
     * Get user's notification references
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(UserNotificationReference::class, 'user_id');
    }

    /**
     * Get user's appointments
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(UserAppointmentService::class, 'user_id');
    }

    /**
     * Get user's favorite properties
     */
    public function favoriteProperties(): HasMany
    {
        return $this->hasMany(UserFavoriteProperty::class, 'user_id');
    }

    /**
     * Get properties owned by this user
     */
    public function ownedProperties(): MorphMany
    {
        return $this->morphMany(Property::class, 'owner');
    }

    /**
     * Get user's sessions
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class, 'user_id');
    }

    /**
     * Get transactions where user is buyer
     */
    public function buyerTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'buyer_user_id');
    }

    /**
     * Get transactions where user is seller
     */
    public function sellerTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'seller_user_id');
    }

    /**
     * Get system notifications for this user
     */
    public function systemNotifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    // ===== HELPER METHODS =====

    /**
     * Check if user has verified email
     */
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Mark email as verified
     */
    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    /**
     * Send email verification notification
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \Illuminate\Auth\Notifications\VerifyEmail);
    }

    /**
     * Get the route key for the model
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    /**
     * Scope to get only verified users
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * Get user's full name or username
     */
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

    // ===== GOOGLE OAUTH HELPER METHODS =====

    /**
     * Check if user has linked Google account
     */
    public function hasGoogleAccount(): bool
    {
        return !empty($this->google_id);
    }

    /**
     * Scope to get users with Google accounts
     */
    public function scopeWithGoogleAccount($query)
    {
        return $query->whereNotNull('google_id');
    }

    /**
     * Scope to get users without Google accounts
     */
    public function scopeWithoutGoogleAccount($query)
    {
        return $query->whereNull('google_id');
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin()
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * Check if user logged in recently (within last 24 hours)
     */
    public function hasRecentLogin(): bool
    {
        if (!$this->last_login_at) {
            return false;
        }

        return $this->last_login_at->diffInHours(now()) < 24;
    }
}
