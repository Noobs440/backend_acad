<?php

namespace App\Http\Controllers\Ressources;

use App\Http\Controllers\Controller;
use App\Models\TblProjet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\FileUploadService;

class TblProjetController extends Controller
{
    private $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    public function index()
    {
        $projets = TblProjet::with('user', 'niveau', 'categorie')
            ->where('soumis', true)
            ->get();

        $resultats = $projets->map(function ($projet) {
            return [
                'id' => $projet->id,
                'titre_projet' => $projet->titre_projet,
                'descript_projet' => $projet->descript_projet,
                'image' => $projet->image,
                'status' => $projet->status,
                'nom_utilisateur' => $projet->user->nom_user,
                'email' => $projet->user->email,
                'views' => $projet->views,
                'type' => $projet->type,
                'niveau' => $projet->niveau->code_niv,
                'nom_categorie' => $projet->categorie->nom_cat,
                'created_at' => $projet->created_at,
                'updated_at' => $projet->updated_at,
            ];
        });

        return response()->json($resultats);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'titre_projet' => 'required|unique:tbl_projets,titre_projet|max:255',
            'descript_projet' => 'required|max:255',
            'tbl_niveau_id' => 'required|exists:tbl_niveaux,id',
            'user_id' => 'required|exists:users,id',
            'tbl_categorie_id' => 'required|exists:tbl_categories,id',
            'image' => 'required|image|max:2048',
            'type' => ['required', 'in:Projet,Memoire,Article'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $imageUrl = $this->fileUploadService->uploadFile($request->file('image'), 'images/project');

        $projet = TblProjet::create([
            'titre_projet' => $request->titre_projet,
            'descript_projet' => $request->descript_projet,
            'tbl_niveau_id' => $request->tbl_niveau_id,
            'user_id' => $request->user_id,
            'tbl_categorie_id' => $request->tbl_categorie_id,
            'image' => $imageUrl,
            'type' => $request->type,
        ]);

        return response()->json($projet, 201);
    }

    public function show(string $id)
    {
        $projet = TblProjet::where('id', $id)->firstOrFail();
        return response()->json($projet);
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'titre_projet' => 'required|max:255',
            'descript_projet' => 'required|max:255',
            'tbl_niveau_id' => 'required',
            'user_id' => 'required',
            'tbl_categorie_id' => 'required',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $projet = TblProjet::where('id', $id)->firstOrFail();
        $projet->titre_projet = $request->titre_projet;
        $projet->descript_projet = $request->descript_projet;
        $projet->tbl_niveau_id = $request->tbl_niveau_id;
        $projet->user_id = $request->user_id;
        $projet->tbl_categorie_id = $request->tbl_categorie_id;

        if ($request->hasFile('image')) {
            if ($projet->image) {
                $this->fileUploadService->deleteFile($projet->image);
            }

            $imageUrl = $this->fileUploadService->uploadFile($request->file('image'), 'images/project');
            $projet->image = $imageUrl;
        }

        $projet->save();

        return response()->json($projet);
    }

    public function destroy(string $id)
    {
        $projet = TblProjet::findOrFail($id);

        // Supprimer l'image associée
        if ($projet->image) {
            $this->fileUploadService->deleteFile($projet->image);
        }

        // Supprimer les documents associés
        $projet->documents()->delete();

        // Supprimer le projet
        $projet->delete();

        return response()->noContent();
    }
}
