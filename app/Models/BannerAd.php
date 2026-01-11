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

    // Relationships
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function owner()
    {
        return $this->morphTo();
    }

    // Accessors
    public function getTitleAttribute($value)
    {
        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            return $decoded['en'] ?? $decoded['ar'] ?? $decoded['ku'] ?? '';
        }
        return $value; // Fallback for old string data
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

    // Scopes
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
            ->where('boost_end_date', '>=', now());
    }

    public function scopeByPosition($query, $position)
    {
        return $query->where('position', $position);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('banner_type', $type);
    }

    // Methods
    public function incrementViews()
    {
        $this->increment('views');
        $this->update(['last_viewed_at' => now()]);
        $this->updateCTR();
    }

    public function incrementClicks()
    {
        $this->increment('clicks');
        $this->update(['last_clicked_at' => now()]);
        $this->updateCTR();
    }

    public function updateCTR()
    {
        if ($this->views > 0) {
            $this->update(['ctr' => $this->clicks / $this->views]);
        }
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
}
