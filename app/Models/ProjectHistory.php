<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'projet_id',
        'user_id',
        'field',
        'old_value',
        'new_value',
    ];

    public function projet()
    {
        return $this->belongsTo(TblProjet::class, 'projet_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
