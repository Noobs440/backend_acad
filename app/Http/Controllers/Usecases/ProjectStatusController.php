<?php

namespace App\Http\Controllers\Usecases;

use App\Http\Controllers\Controller;
use App\Models\TblProjet;
use Illuminate\Support\Facades\Mail;
use App\Notifications\ProjectStatusChangeNotification;

use Illuminate\Http\Request;

class ProjectStatusController extends Controller
{

    public function approvePendingProject($projectId)
    {
        $project = TblProjet::find($projectId);

        if ($project && $project->status === 'Pending') {
            $project->status = 'Approved';
            $project->save();

            // Envoi de la notification à l'utilisateur
            $message = "Votre projet \"{$project->titre_projet}\" a été approuvé.";
            $project->user->notify(new ProjectStatusChangeNotification($project, 'Approved', $message));

            return response()->json(['message' => 'Projet approuvé avec succès.']);
        }

        return response()->json(['message' => 'Projet introuvable ou déjà approuvé/rejeté.'], 404);
    }

    public function rejectPendingProject($projectId)
    {
        $project = TblProjet::find($projectId);

        if ($project && $project->status === 'Pending' ) {
            $motif = request()->input('motif') ?? request()->input('rejection_reason') ?? null;
            $project->status = 'Rejected';
            $project->rejection_reason = $motif;
            $project->save();

            // Envoi de la notification à l'utilisateur
            $message = "Votre projet \"{$project->titre_projet}\" a été rejeté.";
            $project->user->notify(new ProjectStatusChangeNotification($project, 'Rejected', $message));

            return response()->json(['message' => 'Projet rejeté avec succès.']);
        }

        return response()->json(['message' => 'Projet introuvable ou déjà approuvé/rejeté.'], 404);
    }

    public function pendingProject($projectId)
    {
        $project = TblProjet::find($projectId);

        if ($project && ($project->status === 'Approved' || $project->status === 'Rejected')) {
            $project->status = 'Pending';
            // Sauvegarder le motif fourni par l'admin lors de la restauration
            $motif = request()->input('motif') ?? request()->input('rejection_reason') ?? null;
            if ($motif) {
                $project->rejection_reason = $motif;
            }
            $project->save();

            // Envoi de la notification à l'utilisateur
            $message = "Votre projet \"{$project->titre_projet}\" a été remis en attente pour une nouvelle evaluation.";
            $project->user->notify(new ProjectStatusChangeNotification($project, 'Pending', $message));

            return response()->json(['message' => 'Projet restaurer avec succès.']);
        }

        return response()->json(['message' => 'Projet introuvable ou déjà en attente/rejeté.'], 404);
    }

    public function updateStatus(Request $request, $id)
    {
        // Valider la requête
        $request->validate([
            'status' => ['required', 'in:Not Submit,Pending,Approved,Rejected'],
        ]);

        // Trouver le projet par ID
        $project = TblProjet::findOrFail($id);

        // Mettre à jour le statut
        $oldStatus = $project->status;
        $newStatus = $request->input('status');
        $project->status = $newStatus;
        $project->save();

        // Envoyer une notification à l'utilisateur si le statut a changé
        if ($oldStatus !== $newStatus && $project->user) {
            $message = null;
            // Si motif de rejet fourni, l'ajouter au message
            if ($newStatus === 'Rejected' && $request->has('motif')) {
                $message = $request->input('motif');
            }
            $project->user->notify(new \App\Notifications\ProjectStatusChangeNotification($project, $newStatus, $message));
        }

        // Retourner une réponse JSON
        return response()->json([
            'message' => 'Statut du projet mis à jour avec succès',
            'project' => $project
        ], 200);
    }


}
