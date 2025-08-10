<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\TblProjet;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    // Ajouter un commentaire depuis un utilisateur connecté
    public function store(Request $request, TblProjet $project)
    {
        // Valider la requête
        $request->validate([
            'content' => 'required|string',
        ]);

        // Création du commentaire
        $comment = new Comment();
        $comment->project_id = $project->id;
        $comment->user_id = $request->user()->id;  // Utilisateur connecté
        $comment->visitor_name = null;
        $comment->visitor_email = null;
        $comment->content = $request->input('content');
        $comment->save();

        return response()->json($comment, 201);
    }
}
