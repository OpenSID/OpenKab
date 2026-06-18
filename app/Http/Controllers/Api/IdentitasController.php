<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\IdentitasRepository;
use App\Http\Requests\IdentitasRequest;
use App\Http\Requests\UploadImageRequest;
use App\Http\Transformers\IdentitasTransformer;
use App\Models\Identitas;
use App\Services\SecureImageUploadService;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Symfony\Component\HttpFoundation\Response;

class IdentitasController extends Controller
{
    protected $identitas;

    public function __construct(IdentitasRepository $identitas)
    {
        $this->identitas = $identitas;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->fractal($this->identitas->identitas(), new IdentitasTransformer(), 'identitas')->respond();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function update(IdentitasRequest $request, $id)
    {
        try {
            $data = $request->all();
            $identitas = Identitas::find($id);
            $identitas->update($data);

            return response()->json([
                'success' => true,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function upload(UploadImageRequest $request, $id)
    {
        try {
            $file = $request->file('file');
            
            // Use secure image upload service
            $secureService = new SecureImageUploadService(2048);
            $result = $secureService->processSecureUpload($file, 'png', 'img');
            
            // Resize for logo
            $path = storage_path('app/public/img');
            if (! file_exists($path)) {
                mkdir($path, 755, true);
            }
            $filename = uniqid('img_');
            $file = $request->file('file');

            Image::read($file->path())->scale(width: 150, height: 150)->save($path.'/'.$filename.'.png'); //create logo

            Identitas::where('id', $id)->update([
                'logo' => $result['filename'],
            ]);

            return response()->json([
                'success' => true,
                'data' => asset('/storage/img/' . $result['filename']),
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Upload ditolak: ' . $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function uploadFavicon(UploadImageRequest $request, $id)
    {
        try {
            $path = public_path('favicons');
            if (! file_exists($path)) {
                mkdir($path, 755, true);
            }
            $file = $request->file('file');
            
            // Use secure image upload service first
            $secureService = new SecureImageUploadService(2048);
            $result = $secureService->processSecureUpload($file, 'png', 'temp');
            
            // Generate favicons from the processed (safe) image
            $this->generateFaviconsFromImagePath(storage_path('app/public/' . $result['path']), $path);
            
            Identitas::where('id', $id)->update([
                'favicon' => 'favicon-96x96.png',
            ]);

            return response()->json([
                'success' => true,
                'data' => asset('favicons/favicon-96x96.png'),
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Upload ditolak: ' . $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    private function generateFaviconsFromImagePath($filePath, $distPath)
    {
        // create an image manager instance with imagick driver
        // Image::configure(['driver' => 'imagick']);

        Image::read($filePath)->resize(192, 192)->save($distPath.'/android-chrome-192x192.png', quality: 100);
        Image::read($filePath)->resize(512, 512)->save($distPath.'/android-chrome-512x512.png', quality: 100);
        Image::read($filePath)->resize(180, 180)->save($distPath.'/apple-touch-icon.png', quality: 100);
        Image::read($filePath)->resize(16, 16)->save($distPath.'/favicon-16x16.png', quality: 100);
        Image::read($filePath)->resize(32, 32)->save($distPath.'/favicon-32x32.png', quality: 100);
        Image::read($filePath)->resize(96, 96)->save($distPath.'/favicon-96x96.png', quality: 100);
        Image::read($filePath)->resize(150, 150)->save($distPath.'/mstile-150x150.png', quality: 100);
        copy($distPath.'/favicon-16x16.png', $distPath.'/favicon.ico');

        $dataManifest = [
            'name' => 'Favicon',
            'icons' => [
                [
                    'src' => '/android-chrome-192x192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'density' => 0.75,
                ],
                [
                    'src' => '/android-chrome-512x512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'density' => .75,
                ],
                [
                    'src' => '/apple-touch-icon.png',
                    'sizes' => '180x180',
                    'type' => 'image/png',
                    'density' => 0.75,
                ],
                [
                    'src' => '/favicon-16x16.png',
                    'sizes' => '16x16',
                    'type' => 'image/png',
                    'density' => 1,
                ],
                [
                    'src' => '/favicon-32x32.png',
                    'sizes' => '32x32',
                    'type' => 'image/png',
                    'density' => 1,
                ],
                [
                    'src' => '/favicon-96x96.png',
                    'sizes' => '96x96',
                    'type' => 'image/png',
                    'density' => 1,
                ],
                [
                    'src' => '/mstile-150x150.png',
                    'sizes' => '150x150',
                    'type' => 'image/png',
                    'density' => 1,
                ],
            ],
        ];
        file_put_contents($distPath.'/manifest.json', json_encode($dataManifest));
        // favicon.ico
        // $icon = new \Imagick();
        // $icon->addImage(new \Imagick($distPath . "/favicon-16x16.png"));
        // $icon->addImage(new \Imagick($distPath . "/favicon-32x32.png"));
        // $icon->setResolution(16,16);
        // $icon->writeImages($distPath . "/favicon.ico", true);
    }
}
