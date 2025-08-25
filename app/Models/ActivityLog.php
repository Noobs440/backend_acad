<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'subject_type',
        'subject_id',
        'event',
        'description',
        'old_values',
        'new_values',
        'url',
        'method',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    // L'utilisateur (acteur) qui a fait l'action
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // La ressource ciblée (polymorphique)
    public function subject(): MorphTo
    {
        return $this->morphTo(null, 'subject_type', 'subject_id');
    }
}
