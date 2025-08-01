<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class CloudinaryController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,docx|max:10240'
        ]);

        $uploaded = Cloudinary::uploadFile(
            $request->file('file')->getRealPath(),
            [
                'folder' => 'documents',
                'resource_type' => 'raw', // important pour PDF, DOCX
                'public_id' => pathinfo($request->file('file')->getClientOriginalName(), PATHINFO_FILENAME),
            ]
        );

        return response()->json([
            'public_url' => $uploaded->getSecurePath(),
            'cloudinary_id' => $uploaded->getPublicId(),
        ]);
    }
}