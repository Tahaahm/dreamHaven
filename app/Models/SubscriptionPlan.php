<?php

// =====================================================
// MODEL: app/Models/SubscriptionPlan.php
// =====================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'description',
        'duration_months',
        'duration_label',
        'original_price_iqd',
        'discount_iqd',
        'final_price_iqd',
        'price_per_month_iqd',
        'total_amount_iqd',
        'original_price_usd',
        'discount_usd',
        'final_price_usd',
        'price_per_month_usd',
        'total_amount_usd',
        'discount_percentage',
        'savings_percentage',
        'max_properties',
        'price_per_property_iqd',
        'price_per_property_usd',
        'features',
        'conditions',
        'note',
        'active',
        'is_featured',
        'sort_order',
        'savings_vs_monthly_iqd',
        'savings_vs_monthly_usd',
    ];

    protected $casts = [
        'features' => 'array',
        'conditions' => 'array',
        'active' => 'boolean',
        'is_featured' => 'boolean',
        'original_price_iqd' => 'decimal:2',
        'discount_iqd' => 'decimal:2',
        'final_price_iqd' => 'decimal:2',
        'price_per_month_iqd' => 'decimal:2',
        'total_amount_iqd' => 'decimal:2',
        'original_price_usd' => 'decimal:2',
        'discount_usd' => 'decimal:2',
        'final_price_usd' => 'decimal:2',
        'price_per_month_usd' => 'decimal:2',
        'total_amount_usd' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'savings_percentage' => 'decimal:2',
        'price_per_property_iqd' => 'decimal:2',
        'price_per_property_usd' => 'decimal:2',
        'savings_vs_monthly_iqd' => 'decimal:2',
        'savings_vs_monthly_usd' => 'decimal:2',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('duration_months', 'asc');
    }

    // Accessors
    public function getIsDiscountedAttribute()
    {
        return $this->discount_percentage > 0;
    }

    // Constants for subscription types
    const TYPE_BANNER = 'banner';
    const TYPE_SERVICES = 'services';
    const TYPE_OFFICE = 'real_estate_office';
    const TYPE_AGENT = 'agent';

    public static function getTypes()
    {
        return [
            self::TYPE_BANNER,
            self::TYPE_SERVICES,
            self::TYPE_OFFICE,
            self::TYPE_AGENT,
        ];
    }
}