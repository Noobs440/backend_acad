<?php
namespace App\Http\Controllers;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller {
    // Liste les commentaires d'un projet (threaded)
    public function index(Request $request)
    {
        $projectId = $request->query('project_id');
        $comments = Comment::with(['user', 'replies.user', 'reactions.user'])
            ->where('project_id', $projectId)
            ->whereNull('parent_id')
            ->latest()
            ->get();
        return response()->json($comments);
    }

    // Ajoute un commentaire ou une réponse
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'project_id' => 'required|exists:tbl_projets,id',
            'parent_id' => 'nullable|exists:comments,id',
            'file' => 'nullable|file|max:10240',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('comments', 'public');
        }

        $comment = Comment::create([
            'user_id' => auth()->id(),
            'project_id' => $request->project_id,
            'parent_id' => $request->parent_id,
            'content' => $request->content,
            'file_path' => $filePath,
        ]);
        return response()->json($comment->load(['user', 'replies.user', 'reactions.user']), 201);
    }

    // Ajoute une réaction à un commentaire
    public function react(Request $request, $commentId)
    {
        $request->validate([
            'type' => 'required|string',
        ]);
        $comment = Comment::findOrFail($commentId);
        $reaction = $comment->reactions()->updateOrCreate(
            ['user_id' => auth()->id()],
            ['type' => $request->type]
        );
        return response()->json($reaction->load('user'));
    }

    // Compte le nombre de commentaires d'un projet
    public function count(Request $request)
    {
        $projectId = $request->query('project_id');
        $count = Comment::where('project_id', $projectId)->count();
        return response()->json(['count' => $count]);
    }

    // Supprime un commentaire (et ses réponses)
    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);
        if ($comment->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $comment->delete();
        return response()->json(['success' => true]);
    }
}
