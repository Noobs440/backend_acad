<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
        'project_id', 'user_id', 'visitor_name', 'visitor_email', 'content'
    ];

    // Relation vers projet
    public function project()
    {
        return $this->belongsTo(TblProjet::class, 'project_id');
    }

    // Relation vers utilisateur
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Accesseur pour le nom de l'auteur
    public function getAuthorNameAttribute()
    {
        if ($this->user) {
            return $this->user->name;
        }
        return $this->visitor_name ?? 'Visiteur anonyme';
    }
}
