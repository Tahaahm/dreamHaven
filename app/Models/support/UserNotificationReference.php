<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// User Related Models
class UserNotificationReference extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id', 'notification_id', 'notification_date', 'notification_status',
        'title', 'message', 'type'
    ];

    protected function casts(): array
    {
        return [
            'notification_date' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

class UserAppointment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id', 'appointment_id', 'appointment_title', 'with_whom',
        'appointment_date', 'description', 'status', 'notes'
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

class UserFavoriteProperty extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id', 'property_id', 'favorited_at', 'notes'
    ];

    protected function casts(): array
    {
        return [
            'favorited_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}

// Agent Related Models
class AgentSpecialization extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'agent_id', 'property_type', 'service_area'
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}

class AgentUploadedProperty extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'agent_id', 'property_id', 'upload_date', 'property_status'
    ];

    protected function casts(): array
    {
        return [
            'upload_date' => 'date',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}

class AgentSocialPlatform extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'agent_id', 'platform_name', 'account_handle', 'profile_link', 'sort_order'
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}

class AgentClientReview extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'agent_id', 'client_name', 'client_photo', 'rating_score', 'review_text',
        'review_date', 'service_type', 'is_verified', 'is_featured'
    ];

    protected function casts(): array
    {
        return [
            'review_date' => 'date',
            'rating_score' => 'integer',
            'is_verified' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}

class AgentNotification extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'agent_id', 'notification_id', 'title', 'message', 'type', 'is_read', 'read_at'
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}

// Real Estate Office Related Models
class OfficePropertyType extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'office_id', 'type', 'specialization'
    ];

    public function office(): BelongsTo
    {
        return $this->belongsTo(RealEstateOffice::class, 'office_id');
    }
}

class OfficePropertyListing extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'office_id', 'property_id', 'status'
    ];

    public function office(): BelongsTo
    {
        return $this->belongsTo(RealEstateOffice::class, 'office_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}

class OfficeProjectPortfolio extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'office_id', 'project_name', 'project_description', 'project_image',
        'start_date', 'completion_date', 'project_type', 'status', 'sort_order'
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'completion_date' => 'date',
            'sort_order' => 'integer',
        ];
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(RealEstateOffice::class, 'office_id');
    }
}

class OfficeCompanyAgent extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'office_id', 'agent_id', 'agent_name', 'role', 'is_active'
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(RealEstateOffice::class, 'office_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}

class OfficeSocialMedia extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'office_id', 'platform', 'username', 'profile_url', 'sort_order'
    ];

    public function office(): BelongsTo
    {
        return $this->belongsTo(RealEstateOffice::class, 'office_id');
    }
}

class OfficeCustomerReview extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'office_id', 'reviewer_name', 'reviewer_avatar', 'star_rating', 'review_content',
        'review_date', 'property_type', 'is_verified', 'is_featured'
    ];

    protected function casts(): array
    {
        return [
            'review_date' => 'date',
            'star_rating' => 'integer',
            'is_verified' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(RealEstateOffice::class, 'office_id');
    }
}

class OfficeNotificationReference extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'office_id', 'notification_id', 'notification_date', 'notification_status',
        'title', 'message', 'type'
    ];

    protected function casts(): array
    {
        return [
            'notification_date' => 'datetime',
        ];
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(RealEstateOffice::class, 'office_id');
    }
}

// Service Provider Related Models
class ServiceProviderGallery extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'service_provider_id', 'image_url', 'description', 'project_title', 'sort_order'
    ];

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }
}

class ServiceProviderOffering extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'service_provider_id', 'service_title', 'service_description', 'price_range',
        'active', 'sort_order'
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}

class ServiceProviderReview extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'service_provider_id', 'reviewer_name', 'reviewer_avatar', 'star_rating',
        'review_content', 'review_date', 'service_type', 'is_verified', 'is_featured'
    ];

    protected function casts(): array
    {
        return [
            'review_date' => 'date',
            'star_rating' => 'integer',
            'is_verified' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }
}
