<?php

namespace App\Http\Controllers\Usecases;

use Illuminate\Http\Request;
use App\Models\TblProjet;
use App\Http\Controllers\Controller;
use App\Models\TblCategorie;
use App\Models\TblDocument;
use App\Models\TblCollaborateur;
use App\Models\User;
use Illuminate\Support\Facades\Log;


class RechercheController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/usecases/search",
     *     summary="Rechercher des projets et des catégories",
     *     tags={"Recherche"},
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Texte de recherche pour les projets et les catégories"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des projets et des catégories correspondants",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="projets", type="array", @OA\Items(ref="#/components/schemas/TblProjet")),
     *             @OA\Property(property="categories", type="array", @OA\Items(ref="#/components/schemas/TblCategorie"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Requête invalide"
     *     )
     * )
     */
    public function search(Request $request)
    {
        $query = $request->input('query');
        $results = collect();

        // Recherche des projets (titre, description, auteur, catégorie)
        $projectResults = \App\Models\TblProjet::where('titre_projet', 'like', "%$query%")
                        ->orWhere('descript_projet', 'like', "%$query%")
                        ->orWhereHas('user', function($q) use ($query) {
                                $q->where('nom_user', 'like', "%$query%")
                                    ->orWhere('email', 'like', "%$query%")
                                    ->orWhere('surname', 'like', "%$query%")
                                    ->orWhere('matricule', 'like', "%$query%");
                        })
                        ->orWhereHas('categorie', function($q) use ($query) {
                                $q->where('nom_cat', 'like', "%$query%")
                                    ->orWhere('descript_cat', 'like', "%$query%");
                        })
            ->with(['categorie', 'user.filiere', 'niveau'])
            ->get();

        $formattedProjects = $projectResults->filter(function ($project) {
            return $project->status === 'Approved';
        })->map(function ($project) {
            return [
                'id' => $project->id,
                'titre_projet' => $project->titre_projet,
                'nom_categorie' => optional($project->categorie)->nom_cat,
                'nom_utilisateur' => optional($project->user)->nom_user,
                'filiere' => optional($project->user->filiere)->nom_fil,
                'niveau' => optional($project->niveau)->code_niv,
                'description' => $project->descript_projet,
                'image' => $project->image,
                'date' => $project->created_at ? $project->created_at->format('Y-m-d') : null,
            ];
        });

        // Retourner les résultats au format JSON
        return response()->json(['results' => $formattedProjects->values()]);
    }


public function searchCategories(Request $request)
{
    $query = $request->input('query');

    // Recherche des catégories avec Eloquent
    $categoryResults = \App\Models\TblCategorie::where('nom_cat', 'like', "%$query%")
        ->orWhere('descript_cat', 'like', "%$query%")
        ->get();

    $formattedCategories = $categoryResults->map(function ($category) {
        $projectCount = $category->projets()->count();
        return [
            'nom_cat' => $category->nom_cat,
            'descript_cat' => $category->descript_cat,
            'icone' => $category->icone,
            'projets_count' => $projectCount,
        ];
    });

    return response()->json(['results' => $formattedCategories]);
}









    /**
     * @OA\Get(
     *     path="/api/usecases/search/Documents",
     *     summary="Rechercher des documents",
     *     tags={"Recherche"},
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Texte de recherche pour les documents"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des documents correspondants",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/TblDocument")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Requête invalide"
     *     )
     * )
     */
    public function searchDocuments(Request $request)
    {
        $query = $request->input('query');

        // Recherche des documents avec Eloquent
        $documentResults = \App\Models\TblDocument::where('nom_doc', 'like', "%$query%")
            ->orWhere('lien_doc', 'like', "%$query%")
            ->with(['projet', 'user'])
            ->get();

        $formattedDocuments = $documentResults->map(function ($document) {
            return [
                'nom_document' => $document->nom_doc,
                'lien_doc' => $document->lien_doc,
                'date_creation' => $document->created_at->format('Y-m-d'),
                'projet_associe' => optional($document->projet)->titre_projet,
                'auteur' => optional($document->user)->nom_user,
            ];
        });

        return response()->json(['results' => $formattedDocuments]);
    }




    public function search2(Request $request)
{
    $query = $request->input('query');
    $results = [];

    // Recherche des projets avec Eloquent
    $projectResults = \App\Models\TblProjet::where('titre_projet', 'like', "%$query%")
        ->orWhere('descript_projet', 'like', "%$query%")
        ->with(['categorie', 'user.filiere', 'niveau'])
        ->get();

    $projects = $projectResults->map(function ($project) {
        return [
            'id' => $project->id,
            'titre_projet' => $project->titre_projet,
            'nom_categorie' => optional($project->categorie)->nom_cat,
            'filiere' => optional($project->user->filiere)->nom_fil,
            'niveau' => optional($project->niveau)->code_niv,
            'description' => $project->descript_projet,
            'image' =>  $project->image,
            'date_creation' => $project->created_at->format('Y-m-d'),
        ];
    });

    $results['projets'] = $projects;

    return response()->json($results);
}

}
