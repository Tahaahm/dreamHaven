<?php

namespace App\Models\Support;

use App\Models\Agent;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class AgentClientReview extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'agent_id',
        'client_name',
        'client_photo',
        'rating_score',
        'review_text',
        'review_date',
        'service_type',
        'is_verified',
        'is_featured'
    ];

    protected function casts(): array
    {
        return [
            'review_date' => 'date',
            'rating_score' => 'integer',
            'is_verified' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}
