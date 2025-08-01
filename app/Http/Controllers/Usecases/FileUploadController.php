<?php

namespace App\Http\Controllers\Usecases;

use App\Http\Controllers\Controller;
use App\Services\FileUploadService;
use Illuminate\Http\Request;

class FileUploadController extends Controller
{
    protected $uploadService;

    public function __construct(FileUploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    /**
     * @OA\Post(
     *     path="/api/usecases/upload",
     *     summary="Télécharger un fichier sur Cloudinary",
     *     tags={"Fichiers"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="file", type="string", format="binary", description="Fichier à télécharger")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="URL publique du fichier téléchargé",
     *         @OA\JsonContent(
     *             @OA\Property(property="url", type="string", example="https://res.cloudinary.com/demo/image/upload/v1234567890/images/project/file.jpg")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation échouée"
     *     )
     * )
     */
    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
        ]);

        $file = $request->file('file');

        // Exemple : dossier Cloudinary = "images/project"
        $url = $this->uploadService->uploadFile($file, 'images/project');

        return response()->json(['url' => $url], 200, [], JSON_UNESCAPED_SLASHES);
    }

    /**
     * @OA\Delete(
     *     path="/api/usecases/upload/delete",
     *     summary="Supprimer un fichier sur Cloudinary",
     *     tags={"Fichiers"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="public_id", type="string", description="Public ID du fichier Cloudinary à supprimer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Fichier supprimé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="File deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Fichier non trouvé"
     *     )
     * )
     */
    public function deleteFile(Request $request)
    {
        $request->validate([
            'public_id' => 'required|string',
        ]);

        $deleted = $this->uploadService->deleteFile($request->input('public_id'));

        if ($deleted) {
            return response()->json(['message' => 'File deleted successfully.']);
        } else {
            return response()->json(['message' => 'File not found.'], 404);
        }
    }
}
