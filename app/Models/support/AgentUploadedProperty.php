<?php

namespace App\Models\Support;

use App\Models\Agent;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class AgentUploadedProperty extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'agent_id',
        'property_id',
        'upload_date',
        'property_status'
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
