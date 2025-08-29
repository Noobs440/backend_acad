<?php

namespace App\Http\Controllers\Ressources;

use App\Http\Controllers\Controller;
use App\Models\TblCollaborateur;
use App\Models\TblProjet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\CollaborateurAdded;

class TblCollaborateurController extends Controller
{
    public function index()
    {
        $collaborateurs = TblCollaborateur::all();
        return response()->json($collaborateurs);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom_collab'=>'required|max:255',
            'email_collab'=>'required|email|max:255',

            'tbl_projet_id' => 'required|exists:tbl_projets,id',

            'user_id' => 'required|exists:users,id',
        ]);
        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $collaborateur = TblCollaborateur::create([
            'nom_collab' => $request->nom_collab,
            "email_collab" => $request->email_collab,
            'tbl_projet_id' => $request->tbl_projet_id,
            'user_id' => $request->user_id,
        ]);
        return response()->json($collaborateur, 201);
    }

    public function show(string $id)
    {
        $collaborateur = TblCollaborateur::findOrFail($id);
        return response()->json($collaborateur);
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'nom_collab' => 'required|max:255',
            'email_collab' => 'required|email|max:255',
            'tbl_projet_id' => 'required|exists:tbl_projets,id',
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $collaborateur = TblCollaborateur::findOrFail($id);
        $collaborateur->nom_collab = $request->nom_collab;
        $collaborateur->email_collab = $request->email_collab;
        $collaborateur->tbl_projet_id = $request->tbl_projet_id;
        $collaborateur->user_id = $request->user_id;
        $collaborateur->save();

        return response()->json($collaborateur);
    }

    public function addToProject(Request $request, $projectId)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Vérifie que le projet existe
        $projet = TblProjet::find($projectId);
        if (!$projet) {
            return response()->json(['message' => 'Projet non trouvé'], 404);
        }


        // Crée ou récupère le collaborateur
        $collab = TblCollaborateur::firstOrCreate(
            ['email_collab' => $request->email],
            ['nom_collab' => $request->nom]
        );

        // Ajoute le lien avec le projet (many-to-many)
        $collab->projets()->syncWithoutDetaching([$projectId]);

        // Envoie un email
        Mail::to($collab->email_collab)->send(new \App\Mail\CollaborateurAdded($collab));

        // Envoie une notification (base de données) si le collaborateur existe comme utilisateur
        $user = \App\Models\User::where('email', $collab->email_collab)->first();
        if ($user) {
            $adder = $request->user(); // L'utilisateur qui effectue la requête (ajoute le collaborateur)
            $user->notify(new \App\Notifications\CollaborateurAdded($projet, $adder));
        }

        return response()->json([
            'message' => 'Collaborateur ajouté au projet et email envoyé !',
            'collaborateur' => $collab
        ], 201);
    }

    public function destroy(string $id)
    {
        $deleted = TblCollaborateur::destroy($id);
        if (!$deleted) {
            return response()->json(['message' => 'Collaborateur non trouvé'], 404);
        }
        return response()->noContent();
    }
}
