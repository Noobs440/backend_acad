<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class UploadImagesToCloudinary extends Command
{
    protected $signature = 'cloudinary:upload-local-images';
    protected $description = 'Upload des images locales vers Cloudinary en gardant les noms d\'origine';

    public function handle()
    {
        $localFolder = storage_path('app\public\images_cat');

        if (!File::exists($localFolder)) {
            $this->error("Le dossier $localFolder n'existe pas.");
            return 1;
        }

        $files = File::files($localFolder);

        if (count($files) === 0) {
            $this->info("Aucun fichier trouvé dans $localFolder.");
            return 0;
        }

        foreach ($files as $file) {
            $originalName = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            $extension = $file->getExtension();
            $fullName = $originalName . '.' . $extension;

            $this->info("Upload de: $fullName");

            try {
                $uploadResult = Cloudinary::upload($file->getRealPath(), [
                    'public_id'     => 'projets/' . $originalName,
                    'resource_type' => 'auto',
                    'overwrite'     => true,
                ]);

                $this->info("✅ Fichier envoyé: " . $uploadResult->getSecurePath());
            } catch (\Exception $e) {
                $this->error("❌ Erreur d'upload: " . $e->getMessage());
            }
        }

        $this->info("✅ Tous les fichiers ont été traités.");
        return 0;
    }
}
