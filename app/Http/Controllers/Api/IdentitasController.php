<?php

namespace App\Http\Controllers\Api;

use App\Models\Identitas;
use Intervention\Image\Facades\Image;
use App\Http\Requests\IdentitasRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UploadImageRequest;
use App\Http\Repository\IdentitasRepository;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Transformers\IdentitasTransformer;

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

    public function upload(UploadImageRequest $request)
    {
        try {
            $path = public_path('/assets/img');
            if (!file_exists($path)) {
                mkdir($path, 755, true);
            }
            $filename = uniqid('img_');
            $file = $request->file('file');

            Image::make($file->path())->resize(16, 16)->save(public_path().'/'.$filename.'.png');
            copy(public_path().'/'.$filename.'.png', public_path().'/'.$filename.'.ico'); //create favicon
            Image::make($file->path())->resize(150, 150)->save($path.'/'.$filename.'.png'); //create logo

            return response()->json([
                'success' => true,
                'data' => asset('/assets/img/'.$filename.'.png')
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
