<?php
namespace App\Http\Controllers;

use App\Models\ProjectTrace;
use Illuminate\Http\Request;

class ProjectTraceController extends Controller
{
    // Récupérer toutes les traces d'un projet
    public function getTraces($projet_id)
    {
        $traces = ProjectTrace::with(['user', 'projet'])
            ->where('projet_id', $projet_id)
            ->orderByDesc('date_modification')
            ->get();
        return response()->json($traces);
    }
}
