<?php

namespace App\Models\Support;

use App\Models\Agent;
use App\Models\Property;
use App\Models\RealEstateOffice;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficeProjectPortfolio extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'office_id',
        'project_name',
        'project_description',
        'project_image',
        'start_date',
        'completion_date',
        'project_type',
        'status',
        'sort_order'
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
