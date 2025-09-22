<?php

namespace App\Models\Support;

use App\Models\ServiceProvider;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class ServiceProviderOffering extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'service_provider_id',
        'service_title',
        'service_description',
        'price_range',
        'active',
        'sort_order'
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