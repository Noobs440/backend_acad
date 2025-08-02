<?php
namespace App\Http\Controllers;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller {
    public function index() {
        return Comment::with('user')->latest()->get();
    }
    public function store(Request $request) {
        $request->validate(['content' => 'required|string']);
        $comment = Comment::create([
            'user_id' => auth()->id(),
            'content' => $request->content
        ]);
        return response()->json($comment->load('user'), 201);
    }
    public function destroy($id) {
        $comment = Comment::findOrFail($id);
        if ($comment->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $comment->delete();
        return response()->json(['success' => true]);
    }
}
