<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\AppBaseController;
use App\Traits\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArtikelUploadController extends AppBaseController
{
    use UploadedFile;

    public function __construct()
    {
        $this->pathFolder = 'uploads/artikel';
    }

    /**
     * Upload gambar artikel
     */
    public function uploadGambar(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048',
            ]);

            if ($request->file('file')) {
                $path = $this->uploadFile($request, 'file');
                $url = Storage::url($path);

                return response()->json([
                    'success' => true,
                    'url' => $url,
                    'path' => $path,
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'File tidak ditemukan',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload gagal: ' . $e->getMessage(),
            ], 500);
        }
    }
}
