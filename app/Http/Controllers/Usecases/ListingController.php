<?php
namespace App\Http\Controllers\Usecases;
use App\Http\Controllers\Controller;
use App\Models\TblCategorie;
use App\Models\TblNiveau;
use App\Models\TblProjet;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


class ListingController extends Controller
{
    public function showAdminProjects($id)
    {
        // Vérifier que l'utilisateur existe
        $user = User::findOrFail($id);
        // Récupérer les projets où admin_id = $id
        $projets = TblProjet::where('admin_id', $id)->with(['niveau:id,code_niv', 'categorie:id,nom_cat', 'user:id,nom_user'])->get();

        $formattedProjets = $projets->map(function ($projet) {
            return [
                'id' => $projet->id,
                'user_id' => $projet->user->id,
                'titre' => $projet->titre_projet,
                'description' => $projet->descript_projet,
                'type' => $projet->type,
                'views' => $projet->views,
                'image' => $projet->image,
                'status' => $projet->status,
                'niveau' => $projet->niveau->code_niv,
                'categorie' => $projet->categorie->nom_cat,
                'nom_utilisateur' => $projet->user->nom_user,
                'created_at' => $projet->created_at,
            ];
        });
        return response()->json($formattedProjets);
    }

    /**
     * @OA\Get(
     *     path="/api/listing/categorie/projets/{id}",
     *     operationId="showProjects",
     *     tags={"GetElements"},
     *     summary="Get projects by category ID",
     *     description="Returns a list of projects associated with the given category ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Category ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/TblProjet"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     )
     * )
     */

     public function showCollaboratorProjects($userId)
    {
        // On suppose que le user est lié à TblCollaborateur (par email ou id)
        $user = \App\Models\User::findOrFail($userId);
        // Récupérer tous les collaborateurs liés à cet utilisateur (par email)
        $collaborateurs = \App\Models\TblCollaborateur::where('email_collab', $user->email)->get();
        $projets = collect();
        foreach ($collaborateurs as $collab) {
            $projets = $projets->merge($collab->projets()->with(['niveau:id,code_niv', 'categorie:id,nom_cat', 'user:id,nom_user'])->get());
        }
        // Formater les projets pour lecture seule
        $formattedProjets = $projets->unique('id')->map(function ($projet) {
            return [
                'id' => $projet->id,
                'user_id' => $projet->user->id,
                'titre' => $projet->titre_projet,
                'description' => $projet->descript_projet,
                'type' => $projet->type,
                'views' => $projet->views,
                'image' => $projet->image,
                'status' => $projet->status,
                'niveau' => $projet->niveau->code_niv,
                'categorie' => $projet->categorie->nom_cat,
                'nom_utilisateur' => $projet->user->nom_user,
                'created_at' => $projet->created_at,
                'readonly' => true // Pour le frontend : lecture seule
            ];
        });
        return response()->json($formattedProjets->values());
    }

    public function showProjects($id)
    {
        // Récupérer la catégorie par son ID
        $categorie = TblCategorie::findOrFail($id);

        // Récupérer les projets associés à cette catégorie
        $projets = $categorie->projets;

        // Retourner les projets
        return response()->json($projets);
    }

    /**
     * @OA\Get(
     *     path="/api/listing/projet/documents/{id}",
     *     operationId="showDocuments",
     *     tags={"GetElements"},
     *     summary="Get documents by project ID",
     *     description="Returns a list of documents associated with the given project ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Project ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/TblDocument"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Project not found"
     *     )
     * )
     */
    public function showDocuments($id)
    {
        // Récupérer le projet par son ID
        $projet = TblProjet::findOrFail($id);

        // Récupérer les documents associés à ce projet
        $documents = $projet->documents;

        // Retourner les documents
        return response()->json($documents);
    }
    public function showCollaborateurs($id)
    {
        // Récupérer le projet par son ID
        $projet = TblProjet::findOrFail($id);

        // Récupérer les collaborateurs associés à ce projet
        $collaborateurs = $projet->collaborateurs;

        // Retourner les collaborateurs
        return response()->json($collaborateurs);
    }

    /**
     * @OA\Get(
     *     path="/api/listing/niveau/projets/{id}",
     *     operationId="showLevelProjects",
     *     tags={"GetElements"},
     *     summary="Get projects by level ID",
     *     description="Returns a list of projects associated with the given level ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Level ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/TblProjet"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Level not found"
     *     )
     * )
     */
    public function showLevelProjects($id)
    {
        // Récupérer le niveau par son ID
        $niveau = TblNiveau::findOrFail($id);

        // Récupérer les projets associés à ce niveau
        $projets = $niveau->projets;

        // Retourner les projets
        return response()->json($projets);
    }

    /**
     * @OA\Get(
     *     path="/api/listing/user/projets/{id}",
     *     operationId="showUserProjects",
     *     tags={"GetElements"},
     *     summary="Get projects by user ID",
     *     description="Returns a list of projects associated with the given user ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="User ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/TblProjet"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function showUserProjects($id)
{
    // Récupérer l'utilisateur par son ID
    $user = User::findOrFail($id);

    // Récupérer les projets associés à cet utilisateur avec leurs niveaux et catégories
    $projets = $user->projets()->with(['niveau:id,code_niv', 'categorie:id,nom_cat'])->get();

    // Formater les projets pour inclure uniquement les noms des niveaux et catégories
    $formattedProjets = $projets->map(function ($projet) {
        return [
                'id'=>$projet->id,
                'user_id'=>$projet->user->id,
               'titre' => $projet->titre_projet,
                'description' => $projet->descript_projet,
                'type' => $projet->type,
                'views' => $projet->views,
                'image' => $projet->image,
                'status' => $projet->status,
                'niveau' => $projet->niveau->code_niv,
                'categorie' => $projet->categorie->nom_cat,
                'nom_utilisateur' =>$projet->user->nom_user,
                'created_at' => $projet->created_at,
        ];
    });

    // Retourner les projets formatés
    return response()->json($formattedProjets);
}

public function showUserApprovedProjects($id)
{
    // Récupérer l'utilisateur par son ID
    $user = User::findOrFail($id);

    // Récupérer les projets associés à cet utilisateur avec leurs niveaux et catégories
    $projets = $user->projets()->where('status','Approved')->with(['niveau:id,code_niv', 'categorie:id,nom_cat'])->get();

    // Formater les projets pour inclure uniquement les noms des niveaux et catégories
    $formattedProjets = $projets->map(function ($projet) {
        return [
                'id'=>$projet->id,
                'user_id'=>$projet->user->id,
               'titre' => $projet->titre_projet,
                'description' => $projet->descript_projet,
                'type' => $projet->type,
                'views' => $projet->views,
                'image' => $projet->image,
                'status' => $projet->status,
                'niveau' => $projet->niveau->code_niv,
                'categorie' => $projet->categorie->nom_cat,
                'nom_utilisateur' =>$projet->user->nom_user,
                'created_at' => $projet->created_at,
        ];
    });

    // Retourner les projets formatés
    return response()->json($formattedProjets);
}



    /**
     * @OA\Get(
     *     path="/api/listing/user/documents/{id}",
     *     operationId="showUserDocuments",
     *     tags={"GetElements"},
     *     summary="Get documents by user ID",
     *     description="Returns a list of documents associated with the given user ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="User ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/TblDocument"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function showUserDocuments($id)
    {
        // Récupérer l'utilisateur par son ID
        $user = User::findOrFail($id);

        // Récupérer les documents associés à cet utilisateur
        $documents = $user->documents;

        // Retourner les documents
        return response()->json($documents);
    }

    public function countProjectsByStatus()
    {
        // Récupérer les comptes de projets par statut où soumis est true
        $projectCounts = TblProjet::select('status', DB::raw('count(*) as total'))
                                    ->where('soumis', true)
                                    ->groupBy('status')
                                    ->get();

        // Transformer les résultats pour inclure les statuts et les comptes correspondants
        $resultats = $projectCounts->mapWithKeys(function($project) {
            return [$project->status => $project->total];
        });

        // Retourner les résultats sous forme de tableau
        return response()->json([$resultats]);
    }


public function getProjectTypes()
{
    $types = ['Projet', 'Memoire', 'Article'];
    return response()->json($types);
}

}
