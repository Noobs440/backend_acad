<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['project_id'];

    public function project()
    {
        return $this->belongsTo(TblProjet::class, 'project_id');
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'conversation_user');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
