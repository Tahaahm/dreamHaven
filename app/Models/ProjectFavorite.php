<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProjectFavorite extends Model
{
    use HasFactory;

    protected $keyType    = 'string';
    public    $incrementing = false;

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    protected $fillable = [
        'project_id',
        'user_id',
        'favorite_type',
        'notes',
        'interested_features',
        'tags',
        'notify_price_change',
        'notify_status_change',
        'notify_new_units',
        'notify_promotions',
        'priority',
        'list_name',
        'sort_order',
        'view_count',
        'last_viewed_at',
        'is_archived',
    ];

    protected $casts = [
        'id'                  => 'string',
        'interested_features' => 'array',
        'tags'                => 'array',
        'notify_price_change' => 'boolean',
        'notify_status_change'=> 'boolean',
        'notify_new_units'    => 'boolean',
        'notify_promotions'   => 'boolean',
        'is_archived'         => 'boolean',
        'last_viewed_at'      => 'datetime',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('favorite_type', $type);
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', '>=', 4);
    }

    public function scopeByList($query, string $listName)
    {
        return $query->where('list_name', $listName);
    }

    public function scopeForUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ==========================================
    // HELPERS
    // ==========================================

    public function archive(): void
    {
        $this->update(['is_archived' => true]);
    }

    public function unarchive(): void
    {
        $this->update(['is_archived' => false]);
    }

    public function incrementViewCount(): void
    {
        $this->update([
            'view_count'     => $this->view_count + 1,
            'last_viewed_at' => now(),
        ]);
    }

    public function getPriorityLabelAttribute(): string
    {
        return match(true) {
            $this->priority >= 5 => 'High',
            $this->priority >= 3 => 'Medium',
            default              => 'Low',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match(true) {
            $this->priority >= 5 => 'text-red-600 bg-red-50',
            $this->priority >= 3 => 'text-orange-600 bg-orange-50',
            default              => 'text-gray-600 bg-gray-50',
        };
    }
}