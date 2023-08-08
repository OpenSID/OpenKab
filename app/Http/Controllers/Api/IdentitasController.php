<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\IdentitasRepository;
use App\Http\Requests\IdentitasRequest;
use App\Http\Requests\UploadFaviconImageRequest;
use App\Http\Requests\UploadImageRequest;
use App\Http\Transformers\IdentitasTransformer;
use App\Models\Identitas;
use Intervention\Image\Facades\Image;
use Symfony\Component\HttpFoundation\Response;

class IdentitasController extends Controller
{
    public function __construct(protected IdentitasRepository $identitas)
    {
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
            Identitas::where('id', $id)->update($data);

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
            $path = storage_path('app/public/img');
            if (! file_exists($path)) {
                mkdir($path, 755, true);
            }
            $filename = uniqid('img_');
            $file = $request->file('file');

            Image::make($file->path())->resize(150, 150,
                function ($constraint) {
                    $constraint->aspectRatio();
                })->save($path.'/'.$filename.'.png'); //create logo

            Identitas::where('id', $id)->update([
                'logo' => $filename.'.png',
                // 'favicon' => $filename.'.ico',
            ]);

            return response()->json([
                'success' => true,
                'data' => asset('/storage/img/'.$filename.'.png'),
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function uploadFavicon(UploadImageRequest $request, $id)
    {
        try {
            $path = public_path('favicons');
            if (! file_exists($path)) {
                mkdir($path, 755, true);
            }
            $filename = 'favicon.ico';
            $file = $request->file('file');

            $this->generateFaviconsFromImagePath($file->path(), $path);
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
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function generateFaviconsFromImagePath($filePath, $distPath) {
        // create an image manager instance with imagick driver
        // Image::configure(['driver' => 'imagick']);

        Image::make($filePath)->resize(192, 192)->save($distPath . "/android-chrome-192x192.png", '100', 'png');
        Image::make($filePath)->resize(512, 512)->save($distPath . "/android-chrome-512x512.png", '100', 'png');
        Image::make($filePath)->resize(180, 180)->save($distPath . "/apple-touch-icon.png", '100', 'png');
        Image::make($filePath)->resize(16, 16)->save($distPath . "/favicon-16x16.png", '100', 'png');
        Image::make($filePath)->resize(32, 32)->save($distPath . "/favicon-32x32.png", '100', 'png');
        Image::make($filePath)->resize(96, 96)->save($distPath . "/favicon-96x96.png", '100', 'png');
        Image::make($filePath)->resize(150, 150)->save($distPath . "/mstile-150x150.png", '100', 'png');

        // favicon.ico
        // $icon = new \Imagick();
        // $icon->addImage(new \Imagick($distPath . "/favicon-16x16.png"));
        // $icon->addImage(new \Imagick($distPath . "/favicon-32x32.png"));
        // $icon->setResolution(16,16);
        // $icon->writeImages($distPath . "/favicon.ico", true);
    }
}
