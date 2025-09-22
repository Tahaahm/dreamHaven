<?php

namespace App\Models\Support;

use App\Models\Agent;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class AgentSpecialization extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'agent_id',
        'property_type',
        'service_area'
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}
