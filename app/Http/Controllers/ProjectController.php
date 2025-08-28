<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TblProjet;

class ProjectController extends Controller
{
    /**
     * Retourne les projets supervisés par le superviseur (via email)
     */
    public function getSupervisedProjectsByEmail(Request $request)
    {
        $email = $request->query('email');
        $superviseur = \App\Models\TblSuperviseur::where('email_sup', $email)->first();
        if (!$superviseur) {
            return response()->json(['message' => 'Superviseur introuvable.'], 404);
        }
        $projects = \App\Models\TblProjet::where('supervisor_id', $superviseur->id)->get();
        return response()->json($projects);
    }
    /**
     * Retourne les projets supervisés par l'utilisateur connecté (superviseur)
     */
    public function getSupervisedProjects(Request $request)
    {
        $user = $request->user();
        // Si le superviseur est lié à la table users
        $projects = \App\Models\TblProjet::where('supervisor_id', $user->id)->get();
        return response()->json($projects);
    }
    // ...autres méthodes...

    public function assignSupervisor(Request $request, $id)
    {
        $project = TblProjet::findOrFail($id);
        $supervisorId = $request->input('supervisor_id');
        $project->supervisor_id = $supervisorId;
        $project->save();

        return response()->json(['message' => 'Superviseur assigné avec succès.']);
    }

    public function getAllProjects()
{
    $projets = TblProjet::with('user', 'niveau', 'categorie')
        ->get();

    $resultats = $projets->map(function ($projet) {
        return [
            'id' => $projet->id,
            'titre_projet' => $projet->titre_projet,
            'descript_projet' => $projet->descript_projet,
            'image' => $projet->image,
            'status' => $projet->status,

            // Infos utilisateur
            'user_id' => $projet->user->id,
            'nom_utilisateur' => $projet->user->nom_user,
            'email' => $projet->user->email,

            // Infos niveau
            'tbl_niveau_id' => $projet->niveau->id,
            'niveau' => $projet->niveau->code_niv,

            // Infos catégorie
            'tbl_categorie_id' => $projet->categorie->id,
            'nom_categorie' => $projet->categorie->nom_cat,

            'views' => $projet->views,
            'type' => $projet->type,
            'created_at' => $projet->created_at,
            'updated_at' => $projet->updated_at,
            'admin_id' => $projet->admin_id,
        ];
    });

    return response()->json($resultats);
}
}
