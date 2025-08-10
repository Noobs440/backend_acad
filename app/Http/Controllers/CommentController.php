<?php

namespace App\Http\Controllers;

use App\Models\TblProjet;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // Récupérer tous les commentaires d'un projet
public function index($projectId)
{
    $comments = Comment::with('user')
        ->where('project_id', $projectId)
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json([
        'received_project_id' => $projectId,
        'all_comments' => $comments
    ]);
}


    // Ajouter un commentaire (utilisateur ou visiteur)
    public function store(Request $request, TblProjet $project)
    {
        $rules = [
            'content' => 'required|string',
        ];

        if (!$request->user()) {
            $rules['visitor_name'] = 'required|string|max:100';
            $rules['visitor_email'] = 'nullable|email|max:255';
        }

        $validated = $request->validate($rules);

        $comment = new Comment();
        $comment->project_id = $project->id;

        if ($request->user()) {
            $comment->user_id = $request->user()->id;
            $comment->visitor_name = null;
            $comment->visitor_email = null;
        } else {
            $comment->user_id = null;
            $comment->visitor_name = $validated['visitor_name'];
            $comment->visitor_email = $validated['visitor_email'] ?? null;
        }

        $comment->content = $validated['content'];
        $comment->save();

        return response()->json($comment, 201);
    }

        public function userConversations($userId)
    {
        $projectIds = Comment::where('user_id', $userId)
                        ->distinct()
                        ->pluck('project_id');

        $projects = TblProjet::whereIn('id', $projectIds)->get();

        return response()->json($projects);
    }

public function userConversationsWithComments($userId)
{
    // 1. Récupérer les projets dont l'utilisateur est propriétaire
    $projects = TblProjet::where('user_id', $userId)->get();

    $conversations = [];

    foreach ($projects as $project) {
        // 2. Récupérer tous les commentaires liés au projet
        $comments = Comment::with('user')
            ->where('project_id', $project->id)
            ->orderBy('created_at', 'asc')
            ->get();

        // 3. Mapper les commentaires en messages (ajout user_id)
        $messages = $comments->map(function($c) {
            return [
                'id' => $c->id,
                'authorName' => $c->user ? $c->user->name : $c->visitor_name,
                'user_id' => $c->user_id,
                'content' => $c->content,
                'timestamp' => $c->created_at,
            ];
        });

        $conversations[] = [
            'projectId' => $project->id,
            'projectTitle' => $project->titre_projet,
            'messages' => $messages,
        ];
    }

    return response()->json($conversations);
}


}
