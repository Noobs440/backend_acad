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
        } elseif ($request->status !== 'Rejected') {
            $projet->rejection_reason = null;
        }
        $projet->save();
        return response()->json(['message' => 'Statut du projet mis à jour', 'projet' => $projet]);
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

        $projet = TblProjet::create([
            'titre_projet' => $request->titre_projet,
            'descript_projet' => $request->descript_projet,
            'tbl_niveau_id' => $request->tbl_niveau_id,
            'user_id' => $request->user_id,
            'tbl_categorie_id' => $request->tbl_categorie_id,
            'image' => $imageUrl,
            'type' => $request->type,
            'admin_id' => $request->admin_id,
            'status' => 'Pending',
            'soumis' => true,
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
        $projet->status = 'Pending';
        $projet->rejection_reason = null;
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
                \Log::error("Projet introuvable pour l'assignation d'admin", ['projet_id' => $id]);
                return response()->json(['error' => "Projet introuvable"], 404);
            }
            $admin = \App\Models\User::where('id', $request->admin_id)->where('role', 'admin')->first();
            if (!$admin) {
                \Log::error("Admin introuvable ou n'est pas admin", ['admin_id' => $request->admin_id]);
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
            \Log::error('Erreur assignation admin', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Erreur serveur lors de l\'assignation de l\'admin', 'details' => $e->getMessage()], 500);
        }
    }
}
