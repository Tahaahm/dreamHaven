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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str; // ✅ Make sure this is here

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
        'is_active',

        'role',
        'verification_code',
        'is_verified',
    ];



    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'search_preferences' => 'array',
        'is_active' => 'boolean',
        'lat' => 'decimal:6',
        'lng' => 'decimal:6',
        'device_tokens' => 'array',
        'verification_code' => 'string',
    ];

    protected $dates = [
        'deleted_at',
        'email_verified_at',
        'last_login_at',
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
        return $this->hasMany(UserAppointmentService::class, 'user_id');
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
        return $query->whereNotNull('email_verified_at');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->username;
    }

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
}
