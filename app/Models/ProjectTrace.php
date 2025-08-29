<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTrace extends Model
{
    protected $table = 'project_traces';
    protected $fillable = [
        'projet_id',
        'user_id',
        'type_modification',
        'infos_avant',
        'infos_apres',
        'date_modification',
    ];
    protected $casts = [
        'infos_avant' => 'array',
        'infos_apres' => 'array',
        'date_modification' => 'datetime',
    ];

    public function projet(): BelongsTo
    {
        return $this->belongsTo(TblProjet::class, 'projet_id');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
