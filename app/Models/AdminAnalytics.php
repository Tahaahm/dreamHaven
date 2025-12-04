<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminAnalytics extends Model
{
    use HasFactory;

    protected $table = 'admin_analytics';

    protected $fillable = [
        'date',
        'total_users',
        'total_agents',
        'total_offices',
        'total_properties',
        'properties_for_sale',
        'properties_for_rent',
        'properties_sold',
        'properties_rented',
        'total_views',
        'unique_views',
        'returning_views',
        'average_time_on_listing',
        'bounce_rate',
        'favorites_count',
        'active_agents',
        'agents_with_properties',
        'offices_with_listings',
        'active_banners',
        'banners_clicked',
        'banners_impressions',
        'top_properties',
        'top_agents',
        'top_offices',
        'top_banners'
    ];

    protected $casts = [
        'top_properties' => 'array',
        'top_agents' => 'array',
        'top_offices' => 'array',
        'top_banners' => 'array',
    ];
}
