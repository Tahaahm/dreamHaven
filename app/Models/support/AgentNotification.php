<?php

namespace App\Models\Support;

use App\Models\Agent;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentNotification extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'agent_id',
        'notification_id',
        'title',
        'message',
        'type',
        'is_read',
        'read_at'
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}
