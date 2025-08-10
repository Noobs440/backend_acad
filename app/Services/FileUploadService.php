<?php

namespace App\Services;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\UploadedFile;

class FileUploadService
{
    /**
     * Upload un fichier sur Cloudinary dans un dossier donné.
     *
     * @param UploadedFile $file
     * @param string $folder Nom du dossier Cloudinary (ex: 'images/project')
     * @return string URL publique
     */
public function uploadFile(UploadedFile $file, string $folder): string
{
    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
    $extension = strtolower($file->getClientOriginalExtension());
    $uniqueName = $originalName . '_' . time() . '_' . uniqid() . '.' . $extension;

    $resourceType = match (true) {
        in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']) => 'image',
        in_array($extension, ['mp4', 'mov', 'avi', 'mkv']) => 'video',
        default => 'raw',
    };

    $uploaded = Cloudinary::upload($file->getRealPath(), [
        'folder' => $folder, // par ex 'Projets', sans 'public/'
        'public_id' => $uniqueName,
        'resource_type' => $resourceType,
        'use_filename' => true,
        'overwrite' => false,
    ]);

    // Ici, on récupère le chemin complet
    $url = $uploaded->getSecurePath();

    /* Si c’est raw, vérifie et ajuste
    if ($resourceType === 'raw' && !str_contains($url, '/raw/upload/')) {
        $url = 'https://res.cloudinary.com/' . config('cloudinary.cloud_name') .
               '/raw/upload/' . $uploaded->getVersion() . '/' . $uploaded->getPublicId();
    }*/

    return $url;
}











    /**
     * Supprime un fichier de Cloudinary à partir de son public ID.
     *
     * @param string $publicId
     * @return bool
     */
    public function deleteFile(string $publicId): bool
    {
        try {
            Cloudinary::destroy($publicId, ['resource_type' => 'auto']);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
