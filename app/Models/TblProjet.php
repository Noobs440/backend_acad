<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use App\Traits\Auditable;

class TblProjet extends Model
{
    use HasFactory, Searchable, Auditable;

    protected $fillable = [
        'titre_projet',
        'descript_projet',
        'tbl_niveau_id',
        'tbl_categorie_id',
        'user_id',
        'views',
        'image',
        'admin_id',
        'soumis',
        'rejection_reason',
        'status', 
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function niveau()
    {
        return $this->belongsTo(TblNiveau::class, 'tbl_niveau_id');
    }

    public function categorie()
    {
        return $this->belongsTo(TblCategorie::class, 'tbl_categorie_id');
    }


    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function documents()
    
    {
        return $this->hasMany(TblDocument::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'collaborateur_projets');
    }

    

    public function collaborateurs()
    {
        return $this->hasMany(TblCollaborateur::class);
    }

    public function toSearchableArray()
    {
        return [
            'titre_projet' => $this->titre_projet,
            'descript_projet' => $this->descript_projet,
        ];
    }
    
}
        //
      
