<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Retourne les logs paginés, avec possibilité de filtrer
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->orderBy('created_at', 'desc');

        // Filtrer par utilisateur
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filtrer par modèle (ex: "User", "Projet")
        if ($request->has('model')) {
            $query->where('subject_type', 'LIKE', "%{$request->model}%");
        }

        // Filtrer par action (create/update/delete/etc.)
        if ($request->has('event')) {
            $query->where('event', $request->event);
        }

        // Pagination (par défaut 20)
        $logs = $query->paginate($request->get('per_page', 20));

        return response()->json($logs);
    }

    /**
     * Retourne le détail d’un log
     */
    public function show($id)
    {
        $log = ActivityLog::with('user', 'subject')->findOrFail($id);
        return response()->json($log);
    }
}
