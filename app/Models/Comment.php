<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model {
    protected $fillable = [
        'user_id',
        'project_id',
        'parent_id',
        'content',
        'file_path',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function project() {
        return $this->belongsTo(TblProjet::class, 'project_id');
    }

    public function parent() {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies() {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function reactions() {
        return $this->hasMany(Reaction::class);
    }
}
