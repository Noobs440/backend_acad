<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\Ressources\UserManagementController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AdminDashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CloudinaryController;

use App\Http\Controllers\Usecases\{
    ProfileController,
    NotificationController,
    AuthController,
    FileUploadController,
    GestionMotDePasseController,
    ListingController,
    APIAcceuilController,
    ProjectViewController,
    AddController,
    ProjectStatusController,
    SoumissionController,
    RechercheController
};
use App\Http\Controllers\Ressources\{
    TblUniversiteController,
    TblFaculteController,
    TblFiliereController,
    TblCollaborateurController,
    TblSuperviseurController,
    TblNiveauController,
    TblCategorieController,
    TblProjetController,
    TblDocumentController
};

// Route pour la mise à jour du statut d'un projet
use App\Http\Controllers\TblProjetController as MainTblProjetController;
Route::put('/usecases/status/projects/{id}', [MainTblProjetController::class, 'updateStatus']);

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Routes API accessibles via /api/...
|
*/


Route::middleware('auth:sanctum')->post('/projects/{project}/chat-comments', [ChatController::class, 'store']);



Route::get('/user-management', [UserManagementController::class, 'index']);
Route::post('/user-management', [UserManagementController::class, 'store']);
Route::put('/user-management/{user}', [UserManagementController::class, 'update']);
Route::delete('/user-management/{user}', [UserManagementController::class, 'destroy']);
Route::put('/user-management/{id}/reset-password', [UserManagementController::class, 'resetPassword']);


Route::get('/user/{userId}/conversations', [CommentController::class, 'userConversations']);
Route::get('/projects/{projectId}/comments', [CommentController::class, 'index']);
Route::post('/projects/{project}/comments', [CommentController::class, 'store']);

Route::get('/user/{userId}/comment-conversations', [CommentController::class, 'userConversationsWithComments']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [ProfileController::class, 'getUserProfile']);
    Route::put('/user/update-name', [ProfileController::class, 'updateName']);
    Route::put('/user/email', [ProfileController::class, 'updateEmail']);
    Route::put('/user/update-password', [ProfileController::class, 'updatePassword']);
    Route::post('/user/photo', [ProfileController::class, 'updatePhoto']);
});

// Route spécifique pour ajouter un superviseur à un projet (hors préfixe 'ressources' pour cohérence)
//Route::post('superviseurs/add-to-project/{projectId}', [TblSuperviseurController::class, 'addToProject']);

Route::post('/upload-cloudinary', [CloudinaryController::class, 'upload']);
Route::get('/download/cloudinary/{publicId}', [CloudinaryController::class, 'downloadCloudinaryFile'])
    ->where('publicId', '.*');

Route::post('/projects/{id}/assign-supervisor', [App\Http\Controllers\ProjectController::class, 'assignSupervisor']);
// Route pour récupérer les projets supervisés par l'utilisateur connecté
Route::get('/projects/supervised', [App\Http\Controllers\ProjectController::class, 'getSupervisedProjects']);

// Ressources CRUD
Route::prefix('ressources')->group(function () {
    Route::apiResource('universites', TblUniversiteController::class);
    Route::apiResource('facultes', TblFaculteController::class);
    Route::apiResource('filieres', TblFiliereController::class);
    Route::apiResource('collaborateurs', TblCollaborateurController::class);
    //Route::apiResource('superviseurs', TblSuperviseurController::class);
    Route::apiResource('niveaux', TblNiveauController::class);
    Route::apiResource('categories', TblCategorieController::class);
    Route::apiResource('projets', TblProjetController::class);
    // Assigner un admin à un projet existant
    Route::post('projets/{id}/assign-admin', [TblProjetController::class, 'assignAdmin']);
    // Rejeter un projet avec motif (admin)
    Route::post('projets/{id}/reject', [TblProjetController::class, 'rejectWithReason']);
    // Resoumettre un projet rejeté (utilisateur)
    Route::post('projets/{id}/resubmit', [TblProjetController::class, 'resubmit']);
    Route::apiResource('documents', TblDocumentController::class);
});

// Routes sécurisées avec authentification Sanctum
Route::middleware('auth:sanctum')->group(function () {

    // Profil utilisateur
    Route::prefix('user')->controller(ProfileController::class)->group(function () {
        Route::get('/', 'getUserProfile');
        Route::put('/update-name', 'updateName');
        Route::put('/email', 'updateEmail');
        Route::put('/update-password', 'updatePassword');
        Route::post('/photo', 'updatePhoto');
    });

    // Authenticated routes
    Route::prefix('auth')->group(function () {
        Route::post('deconnexion', [AuthController::class, 'deconnexion']);
        
        // Notifications
        Route::controller(NotificationController::class)->group(function () {
            Route::get('notifications', 'getNotifications');
            Route::post('notifications/read/{id}', 'markAsRead');
            Route::post('notifications/readAll', 'markAllAsRead');
        });
    });
});

// Routes non authentifiées (usecases)
Route::prefix('usecases')->group(function () {

    // Authentification
    Route::prefix('auth')->controller(AuthController::class)->group(function () {
        Route::post('inscription', 'inscription')->middleware('web');
        Route::post('connexion', 'connexion');
        Route::post('verification', 'sendVerificationCode')->middleware('web');
        Route::post('verify', 'verify')->middleware('web');
    });

Route::middleware('auth:sanctum')->get('/admin/dashboard-stats', [AdminDashboardController::class, 'getStats']);


    // Gestion mot de passe
    Route::prefix('password')->controller(GestionMotDePasseController::class)->group(function () {
        Route::post('sendcode', 'sendVerificationCode');
        Route::post('verificationcode', 'verifyCode');
        Route::post('reset', 'resetPassword');
    });

    // Upload fichiers
    Route::prefix('upload')->controller(FileUploadController::class)->group(function () {
        Route::post('/', 'uploadFile');
        Route::post('/delete', 'deleteFile');
    });

    // Recherche
    Route::prefix('search')->controller(RechercheController::class)->group(function () {
        Route::post('/projets', 'search');
        Route::post('/categories', 'searchCategories');
        Route::post('/documents', 'searchDocuments');
    });
// Route pour récupérer les projets supervisés par email
    Route::get('/projects/supervised-by-email', [App\Http\Controllers\ProjectController::class, 'getSupervisedProjectsByEmail']);

    // Listing
    Route::prefix('listing')->controller(ListingController::class)->group(function () {
    Route::get('/user/not_submitted/{id}', 'showUserNotSubmittedProjects');
        Route::get('/categorie/projets/{id}', 'showProjects');
        Route::get('/projet/documents/{id}', 'ShowDocuments');
        Route::get('/projet/collaborateurs/{id}', 'ShowCollaborateurs');
        Route::get('/niveau/projets/{id}', 'ShowLevelProjects');
        Route::get('/user/documents/{id}', 'showUserDocuments');
        Route::get('/user/projets/{id}', 'showUserProjects');
        Route::get('/user/approved_projets/{id}', 'showUserApprovedProjects');
        Route::get('/collaborateur/projets/{id}', 'showCollaboratorProjects');
        Route::get('/count/', 'countProjectsByStatus');
        Route::get('/getprojectstype', 'getProjectTypes');
        Route::get('/levels-with-project-count', 'levelsWithProjectCount');
    });

    // Accueil
    Route::prefix('acceuil')->controller(APIAcceuilController::class)->group(function () {
        Route::get('/categories', 'index');
        Route::get('/projets', 'listerProjets');
        Route::get('/projets/ordre', 'listerProjetsParDate');
    });

    // Vues projets
    Route::prefix('addview')->controller(ProjectViewController::class)->group(function () {
        Route::get('/{id}', 'addView');
    });

    // Ajout documents et collaborateurs
    Route::prefix('add')->controller(AddController::class)->group(function () {
        Route::post('doc/projet/{id}', 'ajouterDocument');
        Route::post('collaborateur/projet/{id}', 'ajouterCollaborateur');
    });

    // Statut projets
    Route::prefix('status')->controller(ProjectStatusController::class)->group(function () {
        Route::get('/approved/pending/{id}', 'approvePendingProject')->middleware('web');
        Route::patch('/rejected/pending/{id}', 'rejectPendingProject')->middleware('web');
        Route::get('/pending/{id}', 'PendingProject')->middleware('web');
        Route::put('projects/{id}', 'updateStatus')->middleware('web');
    });

    // Soumission projet
    Route::prefix('submit')->controller(SoumissionController::class)->group(function () {
    Route::post('/{id}', 'submitProject');
    });
});

// Historique des modifications d'un projet
Route::get('/projects/{id}/history', [App\Http\Controllers\Ressources\TblProjetController::class, 'history']);

Route::post('collaborateurs/add-to-project/{projectId}', [TblCollaborateurController::class, 'addToProject']);

Route::get('/admins', [App\Http\Controllers\Ressources\UserController::class, 'index']);
// Projets dont l'utilisateur est admin
Route::get('/listing/admin/projets/{id}', [\App\Http\Controllers\Usecases\ListingController::class, 'showAdminProjects']);
