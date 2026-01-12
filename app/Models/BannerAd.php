<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BannerAd extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'image_url',
        'image_alt',
        'link_url',
        'link_opens_new_tab',
        'owner_type',
        'owner_id',
        'owner_name',
        'owner_email',
        'owner_phone',
        'owner_logo',
        'banner_type',
        'property_id',
        'property_price',
        'property_address',
        'banner_size',
        'custom_dimensions',
        'position',
        'target_locations',
        'target_property_types',
        'target_price_range',
        'target_pages',
        'start_date',
        'end_date',
        'is_active',
        'status',
        'is_featured',
        'is_boosted',
        'boost_start_date',
        'boost_end_date',
        'boost_amount',
        'display_priority',
        'views',
        'clicks',
        'ctr',
        'last_viewed_at',
        'last_clicked_at',
        'billing_type',
        'budget_total',
        'budget_spent',
        'cost_per_click',
        'cost_per_impression',
        'call_to_action',
        'additional_images',
        'terms_conditions',
        'show_contact_info',
        'social_links',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'admin_notes',
        'metadata',
        'created_by_ip',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'description' => 'array',
            'link_opens_new_tab' => 'boolean',
            'custom_dimensions' => 'array',
            'target_locations' => 'array',
            'target_property_types' => 'array',
            'target_price_range' => 'array',
            'target_pages' => 'array',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_boosted' => 'boolean',
            'boost_start_date' => 'datetime',
            'boost_end_date' => 'datetime',
            'boost_amount' => 'decimal:2',
            'views' => 'integer',
            'clicks' => 'integer',
            'ctr' => 'decimal:4',
            'last_viewed_at' => 'datetime',
            'last_clicked_at' => 'datetime',
            'budget_total' => 'decimal:2',
            'budget_spent' => 'decimal:2',
            'cost_per_click' => 'decimal:4',
            'cost_per_impression' => 'decimal:6',
            'call_to_action' => 'array',
            'additional_images' => 'array',
            'terms_conditions' => 'array',
            'show_contact_info' => 'boolean',
            'social_links' => 'array',
            'approved_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    // ========================
    // RELATIONSHIPS
    // ========================

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function owner()
    {
        return $this->morphTo();
    }

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    // ========================
    // ACCESSORS
    // ========================

    public function getTitleAttribute($value)
    {
        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            return $decoded['en'] ?? $decoded['ar'] ?? $decoded['ku'] ?? '';
        }
        return $value;
    }

    public function getDescriptionAttribute($value)
    {
        if (!$value) return null;
        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            return $decoded['en'] ?? $decoded['ar'] ?? $decoded['ku'] ?? '';
        }
        return $value;
    }

    public function getCallToActionAttribute($value)
    {
        if (!$value) return null;
        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            return $decoded['en'] ?? $decoded['ar'] ?? $decoded['ku'] ?? '';
        }
        return $value;
    }

    // ✅ NEW: Computed property for boosted status
    public function getIsBoostedNowAttribute()
    {
        return $this->is_boosted
            && $this->boost_start_date
            && $this->boost_start_date <= now()
            && (!$this->boost_end_date || $this->boost_end_date >= now());
    }

    // ========================
    // SCOPES
    // ========================

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

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeBoosted($query)
    {
        return $query->where('is_boosted', true)
            ->where('boost_start_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('boost_end_date')
                    ->orWhere('boost_end_date', '>=', now());
            });
    }

    public function scopeByPosition($query, $position)
    {
        return $query->where('position', $position);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('banner_type', $type);
    }

    // ✅ NEW: Missing scope methods
    public function scopeOrderByPriority($query)
    {
        return $query->orderByRaw('
            CASE
                WHEN is_boosted = 1
                    AND boost_start_date <= NOW()
                    AND (boost_end_date IS NULL OR boost_end_date >= NOW())
                THEN 1
                WHEN is_featured = 1 THEN 2
                ELSE 3
            END ASC,
            display_priority DESC,
            created_at DESC
        ');
    }

    public function scopeTargetingLocation($query, $location)
    {
        return $query->where(function ($q) use ($location) {
            $q->whereJsonContains('target_locations', $location)
                ->orWhereNull('target_locations')
                ->orWhereRaw('JSON_LENGTH(target_locations) = 0');
        });
    }

    public function scopeTargetingPropertyType($query, $propertyType)
    {
        return $query->where(function ($q) use ($propertyType) {
            $q->whereJsonContains('target_property_types', $propertyType)
                ->orWhereNull('target_property_types')
                ->orWhereRaw('JSON_LENGTH(target_property_types) = 0');
        });
    }

    public function scopeTargetingPriceRange($query, $price)
    {
        return $query->where(function ($q) use ($price) {
            $q->whereNull('target_price_range')
                ->orWhereRaw('JSON_LENGTH(target_price_range) = 0')
                ->orWhere(function ($subQuery) use ($price) {
                    $subQuery->whereRaw('? BETWEEN JSON_EXTRACT(target_price_range, "$.min") AND JSON_EXTRACT(target_price_range, "$.max")', [$price]);
                });
        });
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'draft')
            ->whereNull('approved_at');
    }

    // ========================
    // METHODS
    // ========================

    public function recordView()
    {
        $this->increment('views');
        $this->update(['last_viewed_at' => now()]);
        $this->updateCTR();
    }

    public function recordClick()
    {
        $this->increment('clicks');
        $this->update(['last_clicked_at' => now()]);
        $this->updateCTR();
        $this->updateBudgetSpent();
    }

    public function updateCTR()
    {
        if ($this->views > 0) {
            $this->update(['ctr' => ($this->clicks / $this->views) * 100]);
        }
    }

    public function updateBudgetSpent()
    {
        if ($this->billing_type === 'per_click' && $this->cost_per_click > 0) {
            $spent = $this->clicks * $this->cost_per_click;
            $this->update(['budget_spent' => $spent]);
        } elseif ($this->billing_type === 'per_impression' && $this->cost_per_impression > 0) {
            $spent = $this->views * $this->cost_per_impression;
            $this->update(['budget_spent' => $spent]);
        }
    }

    public function canDisplay()
    {
        // Check if banner is active
        if (!$this->isActive()) {
            return false;
        }

        // Check budget limits
        if ($this->budget_total > 0 && $this->budget_spent >= $this->budget_total) {
            return false;
        }

        return true;
    }

    public function isExpired()
    {
        return $this->end_date && $this->end_date < now();
    }

    public function isActive()
    {
        return $this->is_active
            && $this->status === 'active'
            && $this->start_date <= now()
            && (!$this->end_date || $this->end_date >= now());
    }

    public function approve($userId = null)
    {
        $this->update([
            'status' => 'active',
            'approved_by' => $userId,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);
    }

    public function reject($reason)
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_at' => null,
            'approved_by' => null,
        ]);
    }

    public function pause()
    {
        $this->update(['status' => 'paused']);
    }

    public function resume()
    {
        $this->update(['status' => 'active']);
    }

    // ✅ NEW: Performance metrics
    public function getPerformanceMetrics()
    {
        return [
            'views' => $this->views,
            'clicks' => $this->clicks,
            'ctr' => $this->ctr,
            'budget_spent' => $this->budget_spent,
            'budget_remaining' => $this->budget_total > 0
                ? $this->budget_total - $this->budget_spent
                : null,
            'cost_per_click' => $this->clicks > 0
                ? $this->budget_spent / $this->clicks
                : 0,
            'cost_per_view' => $this->views > 0
                ? $this->budget_spent / $this->views
                : 0,
        ];
    }

    // ✅ NEW: Targeting summary
    public function getTargetingSummary()
    {
        return [
            'locations' => $this->target_locations ?? [],
            'property_types' => $this->target_property_types ?? [],
            'price_range' => $this->target_price_range ?? null,
            'pages' => $this->target_pages ?? [],
            'position' => $this->position,
        ];
    }
}