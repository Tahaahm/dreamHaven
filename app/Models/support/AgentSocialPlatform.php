<?php

namespace App\Models\Support;

use App\Models\Agent;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class AgentSocialPlatform extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'agent_id',
        'platform_name',
        'account_handle',
        'profile_link',
        'sort_order'
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}
