<?php

namespace App\Models;

<<<<<<< HEAD
use App\Models\Support\UserFavoriteProperty;
use App\Models\Support\UserNotificationReference;
use App\Services\User\UserAppointmentService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
=======
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
>>>>>>> myproject/main
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
<<<<<<< HEAD
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */


    // Explicitly define the primary key (usually not needed if it's 'id')
    // Explicitly define the primary key (usually not needed if it's 'id')
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        // Remove 'id' from fillable - UUIDs are auto-generated
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
        'is_active'
    ];
=======
use Illuminate\Support\Str;

class User extends Authenticatable implements MustVerifyEmailContract
{
    use HasFactory, Notifiable, HasApiTokens, MustVerifyEmailTrait;

    // Specify the primary key
    protected $primaryKey = 'user_id';

    // Indicate that the primary key is auto-incrementing
    public $incrementing = true;

    // Set the key type to int
    protected $keyType = 'int';

    // Field casts
    protected $casts = [
        'email_verified' => 'boolean',
        'active' => 'boolean',
        'preferences' => 'json',
    ];

    protected $fillable = [
        'name',
        'password',
        'email',
        'image',
        'phone',
        'address',
        'city',
        'state',
        'zip_code',
        'bio',
        'website',
        'facebook',
        'twitter',
        'instagram',
        'linkedin',
        'youtube',
        'email_verified',
        'email_verification_token',
        'preferences',
        'role',
        'active',
    ];
    
    
>>>>>>> myproject/main

    protected $hidden = [
        'password',
        'remember_token',
    ];

<<<<<<< HEAD
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'search_preferences' => 'array',
        'is_active' => 'boolean',
        'lat' => 'decimal:6',
        'lng' => 'decimal:6',
        'device_tokens' => 'array', // Add this line

    ];
    /**
     * The attributes that should be treated as dates.
     *
     * @var array<string>
     */
    protected $dates = [
        'deleted_at',
        'email_verified_at',
        'last_login_at',
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
     * Assuming Property has owner_id and owner_type columns for polymorphic relationship
     */
    public function ownedProperties(): MorphMany
    {
        return $this->morphMany(Property::class, 'owner');
    }

    /**
     * Alternative if Property has a direct user_id relationship
     * Uncomment this if your Property model has user_id instead of polymorphic relationship
     */
    // public function ownedProperties(): HasMany
    // {
    //     return $this->hasMany(Property::class, 'user_id');
    // }

    /**
     * Get user's sessions
     * Note: This assumes you have a sessions table with user_id
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
     * Get the route key for the model (useful for route model binding)
     */
    public function getRouteKeyName(): string
    {
        return 'id'; // or 'username' if you prefer
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
=======
    // Define relationship with properties
    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->email_verification_token)) {
                $user->email_verification_token = Str::random(60);
            }
        });
    }
}
>>>>>>> myproject/main
