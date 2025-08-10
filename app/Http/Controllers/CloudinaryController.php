<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CloudinaryController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,docx|max:10240'
        ]);

        $file = $request->file('file');

        // Générer un public_id unique pour éviter conflits (pas juste nom original)
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $uniquePublicId = $originalName . '_' . time() . '_' . uniqid();

        $uploaded = Cloudinary::uploadFile(
            $file->getRealPath(),
            [
                'folder' => 'documents',
                'resource_type' => 'raw', // important pour PDF, DOCX
                'public_id' => $uniquePublicId,
                'overwrite' => false,
            ]
        );

        return response()->json([
            'public_url' => $uploaded->getSecurePath(),
            'cloudinary_id' => $uploaded->getPublicId(),
            'original_name' => $file->getClientOriginalName(),
        ]);
    }

public function downloadCloudinaryFile(string $publicId)
{
    // Supprime toute extension .tmp, .pdf, etc. du publicId
    $publicIdWithoutExt = preg_replace('/\.[^\.]+$/', '', $publicId);

    $cloudName = config('cloudinary.cloud_name');
    $api = Cloudinary::adminApi();

    try {
        $resource = $api->resource($publicIdWithoutExt, ['resource_type' => 'auto']);
    } catch (\Exception $e) {
        abort(404, 'Fichier non trouvé sur Cloudinary.');
    }

    $resourceType = $resource['resource_type'] ?? 'raw';
    $version = 'v' . ($resource['version'] ?? time());
    $publicIdPath = $resource['public_id'];
    $extension = $resource['format'] ?? 'bin';

    $baseUrl = "https://res.cloudinary.com/{$cloudName}/";

    switch ($resourceType) {
        case 'image':
            $url = $baseUrl . "image/upload/{$version}/{$publicIdPath}.{$extension}";
            break;
        case 'video':
            $url = $baseUrl . "video/upload/{$version}/{$publicIdPath}.{$extension}";
            break;
        case 'raw':
        default:
            $url = $baseUrl . "raw/upload/{$version}/{$publicIdPath}.{$extension}";
            break;
    }

    $originalFilename = $resource['filename'] ?? 'downloaded_file';
    $filename = $originalFilename . '.' . $extension;

    $response = Http::get($url);

    if ($response->failed()) {
        abort(404, 'Impossible de récupérer le fichier Cloudinary.');
    }

    return new StreamedResponse(function () use ($response) {
        echo $response->body();
    }, 200, [
        'Content-Type' => $response->header('Content-Type') ?? 'application/octet-stream',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        'Content-Length' => $response->header('Content-Length') ?? strlen($response->body()),
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
        'Pragma' => 'no-cache',
        'Expires' => '0',
    ]);
}


}
