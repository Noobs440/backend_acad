<?php

namespace App\Http\Controllers\Usecases;

use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;
use App\Notifications\ProjectSubmittedNotification;
use App\Models\User;
use App\Models\TblProjet;
use Illuminate\Http\Request;

class SoumissionController extends Controller
{
    public function submitProject($id)
    {
        $project = TblProjet::find($id);

        if (!$project) {
            return response()->json(['message' => 'Projet introuvable.'], 404);
        }

        // Vérifie qu'il y a au moins un document
        if ($project->documents()->count() < 1) {
            return response()->json(['message' => 'Le projet doit avoir au moins un document pour être soumis.'], 400);
        }

        // Vérifie qu'un admin est assigné
        if (!$project->admin_id) {
            return response()->json(['message' => 'Veuillez assigner un admin avant de soumettre le projet.'], 400);
        }

        $project->soumis = true;
        $project->status = 'Pending';
        $project->save();

        Log::info('Soumission projet', [
            'projet_id' => $project->id,
            'admin_id' => $project->admin_id,
            'soumis' => $project->soumis,
            'status' => $project->status
        ]);

        // Notifie l'admin assigné uniquement
        $admin = $project->admin;
        if ($admin) {
            $admin->notify(new ProjectSubmittedNotification($project));
        }

        return response()->json(['message' => 'Projet soumis avec succès.']);
    }


}
