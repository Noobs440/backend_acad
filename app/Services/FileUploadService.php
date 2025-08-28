<?php

namespace App\Services;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\UploadedFile;

class FileUploadService
{
    /**
     * Upload un fichier sur S3 dans un dossier donné.
     *
     * @param UploadedFile $file
     * @param string $folder Nom du dossier S3 (ex: 'images/project')
     * @return string URL publique
     */
    public function uploadFile(UploadedFile $file, string $folder): string
    {
        $path = $file->store($folder, 's3');
        return \Storage::disk('s3')->url($path);
    }











    /**
     * Supprime un fichier de S3 à partir de son chemin.
     *
     * @param string $path
     * @return bool
     */
    public function deleteFile(string $path): bool
    {
        return \Storage::disk('s3')->delete($path);
    }
}
