<?php

namespace App\Models\Support;

use App\Models\Agent;
use App\Models\RealEstateOffice;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class ServiceProviderGallery extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'service_provider_id',
        'image_url',
        'description',
        'project_title',
        'sort_order'
    ];

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }
}
