<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\ArtikelImageRequest;
use App\Services\SecureImageUploadService;
use Illuminate\Support\Facades\Storage;

class ArtikelUploadController extends AppBaseController
{
    protected $pathFolder = 'uploads/artikel';

    /**
     * Upload gambar artikel dengan validasi keamanan yang ketat
     */
    public function uploadGambar(ArtikelImageRequest $request)
    {
        try {
            $file = $request->file('file');
            
            // Use secure image upload service
            $secureService = new SecureImageUploadService(2048);
            $result = $secureService->processSecureUpload($file, 'jpg', $this->pathFolder);
            
            $url = Storage::url($result['path']);

            return response()->json([
                'success' => true,
                'url' => $url,
                'path' => $result['path'],
                'filename' => $result['filename'],
                'size' => $result['size'],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload ditolak: ' . $e->getMessage(),
            ], 400);
        }
    }
}
