<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\IdentitasRepository;
use App\Http\Requests\IdentitasRequest;
use App\Http\Requests\UploadImageRequest;
use App\Http\Transformers\IdentitasTransformer;
use App\Models\Identitas;
use Illuminate\Support\Facades\Storage;
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

            Image::make($file->path())->resize(16, 16)->save(public_path().'/favicon.png');
            copy(public_path().'/favicon.png', public_path().'/favicon.ico'); //create favicon
            Image::make($file->path())->resize(150, 150)->save($path.'/'.$filename.'.png'); //create logo

            Identitas::where('id', $id)->update([
                'logo' => $filename.'.png',
                'favicon' => $filename,
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
}
