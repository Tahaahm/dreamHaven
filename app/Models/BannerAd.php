<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BannerAd extends Model
{
    use HasFactory;

    protected $table = 'banner_ads';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        // Basic Banner Info
        'title',
        'description',
        'image_url',
        'image_alt',
        'link_url',
        'link_opens_new_tab',

        // Banner Owner Info
        'owner_type',
        'owner_id',
        'owner_name',
        'owner_email',
        'owner_phone',
        'owner_logo',

        // Banner Type & Targeting
        'banner_type',

        // Property Connection
        'property_id',
        'property_price',
        'property_address',

        // Display & Positioning
        'banner_size',
        'custom_dimensions',
        'position',

        // Targeting Options
        'target_locations',
        'target_property_types',
        'target_price_range',
        'target_pages',

        // Scheduling
        'start_date',
        'end_date',
        'is_active',
        'status',

        // Premium Features
        'is_featured',
        'is_boosted',
        'boost_start_date',
        'boost_end_date',
        'boost_amount',
        'display_priority',

        // Budget & Billing
        'billing_type',
        'budget_total',
        'budget_spent',
        'cost_per_click',
        'cost_per_impression',

        // Additional Content
        'call_to_action',
        'additional_images',
        'terms_conditions',
        'show_contact_info',
        'social_links',

        // Approval & Moderation
        'approved_by',
        'approved_at',
        'rejection_reason',
        'admin_notes',

        // Metadata
        'metadata',
        'created_by_ip',
        'user_agent',
    ];

    protected $casts = [
        'id' => 'string',
        'link_opens_new_tab' => 'boolean',
        'property_price' => 'decimal:2',
        'custom_dimensions' => 'json',
        'target_locations' => 'json',
        'target_property_types' => 'json',
        'target_price_range' => 'json',
        'target_pages' => 'json',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_boosted' => 'boolean',
        'boost_start_date' => 'datetime',
        'boost_end_date' => 'datetime',
        'boost_amount' => 'decimal:2',
        'display_priority' => 'integer',
        'views' => 'integer',
        'clicks' => 'integer',
        'ctr' => 'decimal:4',
        'last_viewed_at' => 'datetime',
        'last_clicked_at' => 'datetime',
        'budget_total' => 'decimal:2',
        'budget_spent' => 'decimal:2',
        'cost_per_click' => 'decimal:4',
        'cost_per_impression' => 'decimal:6',
        'additional_images' => 'json',
        'show_contact_info' => 'boolean',
        'social_links' => 'json',
        'approved_at' => 'datetime',
        'metadata' => 'json',
    ];

    protected $dates = [
        'start_date',
        'end_date',
        'boost_start_date',
        'boost_end_date',
        'last_viewed_at',
        'last_clicked_at',
        'approved_at',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });

        // Auto-calculate CTR when views/clicks change
        static::updating(function ($model) {
            if ($model->isDirty(['views', 'clicks'])) {
                $model->ctr = $model->views > 0 ? ($model->clicks / $model->views) : 0;
            }
        });
    }

    // RELATIONSHIPS

    /**
     * Get the property associated with this banner
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    /**
     * Get the user who approved this banner
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the owner (dynamic relationship based on owner_type)
     */
    public function owner()
    {
        switch ($this->owner_type) {
            case 'agent':
                return $this->belongsTo(Agent::class, 'owner_id');
            case 'real_estate':
                return $this->belongsTo(RealEstate::class, 'owner_id');
            default:
                return null;
        }
    }

    // SCOPES

    /**
     * Scope for active banners
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }

    /**
     * Scope for featured banners
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for boosted banners
     */
    public function scopeBoosted($query)
    {
        return $query->where('is_boosted', true)
            ->where('boost_start_date', '<=', now())
            ->where('boost_end_date', '>=', now());
    }

    /**
     * Scope for banners by position
     */
    public function scopeByPosition($query, $position)
    {
        return $query->where('position', $position);
    }

    /**
     * Scope for banners by owner
     */
    public function scopeByOwner($query, $ownerType, $ownerId)
    {
        return $query->where('owner_type', $ownerType)
            ->where('owner_id', $ownerId);
    }

    /**
     * Scope for banners by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('banner_type', $type);
    }

    /**
     * Scope for banners targeting specific location
     */
    public function scopeTargetingLocation($query, $location)
    {
        return $query->whereJsonContains('target_locations', $location)
            ->orWhereNull('target_locations');
    }

    /**
     * Scope for banners targeting specific property type
     */
    public function scopeTargetingPropertyType($query, $propertyType)
    {
        return $query->whereJsonContains('target_property_types', $propertyType)
            ->orWhereNull('target_property_types');
    }

    /**
     * Scope for banners within price range
     */
    public function scopeTargetingPriceRange($query, $price)
    {
        return $query->where(function ($q) use ($price) {
            $q->whereNull('target_price_range')
                ->orWhere(function ($subQ) use ($price) {
                    $subQ->whereRaw("JSON_EXTRACT(target_price_range, '$.min') <= ?", [$price])
                        ->whereRaw("JSON_EXTRACT(target_price_range, '$.max') >= ?", [$price]);
                });
        });
    }

    /**
     * Scope for ordering by display priority
     */
    public function scopeOrderByPriority($query)
    {
        return $query->orderBy('is_featured', 'desc')
            ->orderBy('is_boosted', 'desc')
            ->orderBy('display_priority', 'desc')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Scope for pending approval
     */
    public function scopePendingApproval($query)
    {
        return $query->where('status', 'draft')
            ->whereNull('approved_at');
    }

    // ACCESSORS & MUTATORS

    /**
     * Get the banner dimensions
     */
    public function getDimensionsAttribute()
    {
        if ($this->banner_size === 'custom' && $this->custom_dimensions) {
            return $this->custom_dimensions;
        }

        $standardSizes = [
            'banner' => ['width' => 728, 'height' => 90],
            'leaderboard' => ['width' => 970, 'height' => 250],
            'rectangle' => ['width' => 300, 'height' => 250],
            'sidebar' => ['width' => 300, 'height' => 600],
            'mobile' => ['width' => 320, 'height' => 100],
        ];

        return $standardSizes[$this->banner_size] ?? ['width' => 728, 'height' => 90];
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute()
    {
        return $this->property_price ? '$' . number_format($this->property_price, 0) : null;
    }

    /**
     * Check if banner is currently boosted
     */
    public function getIsBoostedNowAttribute()
    {
        if (!$this->is_boosted) return false;

        $now = now();
        return $this->boost_start_date <= $now && $this->boost_end_date >= $now;
    }

    /**
     * Get remaining budget
     */
    public function getRemainingBudgetAttribute()
    {
        return $this->budget_total ? ($this->budget_total - $this->budget_spent) : null;
    }

    /**
     * Check if banner is expired
     */
    public function getIsExpiredAttribute()
    {
        return $this->end_date && $this->end_date < now();
    }

    // METHODS

    /**
     * Record a view for this banner
     */
    public function recordView()
    {
        $this->increment('views');
        $this->update(['last_viewed_at' => now()]);

        // Update CTR
        $this->update(['ctr' => $this->clicks / max($this->views, 1)]);
    }

    /**
     * Record a click for this banner
     */
    public function recordClick()
    {
        $this->increment('clicks');
        $this->update(['last_clicked_at' => now()]);

        // Update CTR
        $this->update(['ctr' => $this->clicks / max($this->views, 1)]);

        // Deduct from budget if pay-per-click
        if ($this->billing_type === 'per_click' && $this->cost_per_click) {
            $this->increment('budget_spent', $this->cost_per_click);
        }
    }

    /**
     * Approve the banner
     */
    public function approve($approverId = null)
    {
        $this->update([
            'status' => 'active',
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);
    }

    /**
     * Reject the banner
     */
    public function reject($reason = null)
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Pause the banner
     */
    public function pause()
    {
        $this->update(['status' => 'paused']);
    }

    /**
     * Resume the banner
     */
    public function resume()
    {
        $this->update(['status' => 'active']);
    }

    /**
     * Check if banner can be displayed
     */
    public function canDisplay()
    {
        // Check if active and within date range
        if (!$this->is_active || $this->status !== 'active') {
            return false;
        }

        // Check start date
        if ($this->start_date > now()) {
            return false;
        }

        // Check end date
        if ($this->end_date && $this->end_date < now()) {
            return false;
        }

        // Check budget (if applicable)
        if ($this->budget_total && $this->budget_spent >= $this->budget_total) {
            return false;
        }

        return true;
    }

    /**
     * Get banner performance metrics
     */
    public function getPerformanceMetrics()
    {
        return [
            'views' => $this->views,
            'clicks' => $this->clicks,
            'ctr' => round($this->ctr * 100, 2) . '%',
            'budget_spent' => $this->budget_spent,
            'remaining_budget' => $this->remaining_budget,
            'cost_per_click' => $this->cost_per_click,
            'average_cpc' => $this->clicks > 0 ? ($this->budget_spent / $this->clicks) : 0,
        ];
    }

    /**
     * Get targeting summary
     */
    public function getTargetingSummary()
    {
        return [
            'locations' => $this->target_locations,
            'property_types' => $this->target_property_types,
            'price_range' => $this->target_price_range,
            'pages' => $this->target_pages,
        ];
    }
}
