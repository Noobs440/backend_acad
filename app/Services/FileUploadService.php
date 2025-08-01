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
        $extension = $file->getClientOriginalExtension();

        // Générer un nom unique
        $uniqueName = $originalName . '_' . time() . '_' . uniqid();

        // Upload sur Cloudinary avec un nom public unique
        $uploaded = Cloudinary::upload($file->getRealPath(), [
            'folder'     => $folder,
            'public_id'  => $uniqueName,
            'resource_type' => 'auto', // accepte images, pdf, vidéos, etc.
            'overwrite'  => false,
        ]);

        return $uploaded->getSecurePath(); // URL HTTPS du fichier
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
