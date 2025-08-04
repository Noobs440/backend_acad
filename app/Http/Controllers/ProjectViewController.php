<?php
namespace App\Http\Controllers;

use App\Models\ProjectView;
use Illuminate\Http\Request;

class ProjectViewController extends Controller
{
    public function increment(Request $request, $projectId)
    {
        $userId = $request->user() ? $request->user()->id : null;
        ProjectView::create([
            'project_id' => $projectId,
            'user_id' => $userId,
        ]);
        return response()->json(['success' => true]);
    }
}
