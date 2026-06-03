<?php
namespace App\Http\Controllers\Ressources;
use App\Http\Controllers\Controller;
use App\Models\TblProjet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\FileUploadService;
use App\Models\ProjectHistory;

class TblProjetController extends Controller
{
    private $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Met à jour le statut d'un projet (admin)
     */
public function updateStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|string',
        'rejection_reason' => 'nullable|string',
    ]);

    $projet = TblProjet::findOrFail($id);
    $projet->status = $request->status;

    if ($request->status === 'Rejected' && $request->filled('rejection_reason')) {
        $projet->rejection_reason = $request->rejection_reason;
    } elseif ($request->status === 'Pending' && $request->filled('rejection_reason')) {
        // ← Remise en attente avec motif (restauration depuis Approved)
        $projet->rejection_reason = $request->rejection_reason;
    } elseif ($request->status === 'Approved') {
        // ← Approuvé : on efface le motif
        $projet->rejection_reason = null;
    }

    $projet->save();

    $user = $projet->user;
    if ($user) {
        $message = match($projet->status) {
            'Approved' => 'Votre projet a été approuvé.',
            'Rejected' => 'Votre projet a été rejeté.',
            'Pending'  => 'Votre projet a été remis en attente de révision.',
            default    => 'Le statut de votre projet a changé.',
        };
        $user->notify(new \App\Notifications\ProjectStatusChangeNotification(
            $projet, $projet->status, $message
        ));
    }

    return response()->json([
        'message' => 'Statut du projet mis à jour', 
        'projet' => $projet
    ]);
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
            'admin_id' => 'nullable|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $imageUrl = $this->fileUploadService->uploadFile($request->file('image'), 'images/project');

        // Si le projet est créé mais pas soumis, il doit être Not Submitted
        $isInitialSubmission = $request->has('soumis') ? filter_var($request->soumis, FILTER_VALIDATE_BOOLEAN) : false;
        // Sécurisation : si le champ status est absent ou incohérent, on force Not Submitted
        $status = $isInitialSubmission ? 'Pending' : 'Not Submitted';
        if ($request->has('status') && in_array($request->status, ['Pending','Approved','Rejected','Not Submitted'])) {
            $status = $request->status;
        }
        $projet = TblProjet::create([
            'titre_projet' => $request->titre_projet,
            'descript_projet' => $request->descript_projet,
            'tbl_niveau_id' => $request->tbl_niveau_id,
            'user_id' => $request->user_id,
            'tbl_categorie_id' => $request->tbl_categorie_id,
            'image' => $imageUrl,
            'type' => $request->type,
            'admin_id' => $request->admin_id,
            'status' => $status,
            'soumis' => $isInitialSubmission,
        ]);

        // Notification uniquement à l'admin choisi (déjà fait ci-dessus)
        if ($request->admin_id) {
            $admin = \App\Models\User::find($request->admin_id);
            if ($admin) {
                $admin->notify(new \App\Notifications\ProjectSubmittedNotification($projet));
            }
        }

        return response()->json($projet, 201);
    }

    public function show(string $id)
    {
        $projet = TblProjet::where('id', $id)->firstOrFail();
        // Inclure le motif de rejet dans la réponse
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
        $userId = $request->user_id ?? auth()->id();
        $fields = [
            'titre_projet',
            'descript_projet',
            'tbl_niveau_id',
            'user_id',
            'tbl_categorie_id',
        ];
        foreach ($fields as $field) {
            if ($request->has($field) && $projet->$field != $request->$field) {
                \App\Models\ProjectHistory::create([
                    'projet_id' => $projet->id,
                    'user_id' => $userId,
                    'field' => $field,
                    'old_value' => $projet->$field,
                    'new_value' => $request->$field,
                ]);
                $projet->$field = $request->$field;
            }
        }

        if ($request->hasFile('image')) {
            if ($projet->image) {
                $this->fileUploadService->deleteFile($projet->image);
            }
            $imageUrl = $this->fileUploadService->uploadFile($request->file('image'), 'images/project');
            if ($projet->image != $imageUrl) {
                \App\Models\ProjectHistory::create([
                    'projet_id' => $projet->id,
                    'user_id' => $userId,
                    'field' => 'image',
                    'old_value' => $projet->image,
                    'new_value' => $imageUrl,
                ]);
                $projet->image = $imageUrl;
            }
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


 public function history($id)
    {
        $histories = \App\Models\ProjectHistory::with('user')
            ->where('projet_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($histories);
    }


     public function rejectWithReason(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:3',
        ]);
        $projet = TblProjet::findOrFail($id);
        $projet->status = 'Rejected';
        $projet->rejection_reason = $request->rejection_reason;
        $projet->save();
        return response()->json(['message' => 'Projet rejeté', 'projet' => $projet]);
    }

    /**
     * Resoumettre un projet rejeté (utilisateur)
     */
public function resubmit($id)
{
    $projet = TblProjet::findOrFail($id);
    if ($projet->status !== 'Rejected') {
        return response()->json(['error' => 'Seuls les projets rejetés peuvent être resoumis.'], 400);
    }
    
    // On garde le motif du rejet précédent pour que l'admin le voie
    $projet->status = 'Pending';
    // NE PAS effacer rejection_reason ici
    $projet->save();
    
    return response()->json(['message' => 'Projet resoumis', 'projet' => $projet]);
}

    public function assignAdmin(Request $request, $id)
    {
        try {
            $request->validate([
                'admin_id' => 'required|exists:users,id',
            ]);
            $projet = TblProjet::find($id);
            if (!$projet) {
                return response()->json(['error' => "Projet introuvable"], 404);
            }
            $admin = \App\Models\User::where('id', $request->admin_id)
                ->whereIn('role', ['admin', 'adminsys'])
                ->first();
            if (!$admin) {
                return response()->json(['error' => "Admin introuvable ou n'est pas admin"], 400);
            }
            $projet->admin_id = $admin->id;
            if ($projet->status !== 'Pending') {
                $projet->status = 'Pending';
            }
            $projet->save();
            $admin->notify(new \App\Notifications\ProjectSubmittedNotification($projet));
            return response()->json(['message' => 'Admin assigné, projet soumis', 'projet' => $projet]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur serveur lors de l\'assignation de l\'admin', 'details' => $e->getMessage()], 500);
        }
    }
}
