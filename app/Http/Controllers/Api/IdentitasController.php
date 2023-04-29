<?php

namespace App\Http\Controllers\Api;

use App\Models\Kategori;
use Illuminate\Http\Request;
use App\Http\Requests\KategoriRequest;
use App\Http\Repository\IdentitasRepository;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Transformers\ListKategoriTransformer;

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
        return $this->fractal($this->identitas->identitas(), new ListKategoriTransformer(), 'identitas')->respond();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function update(KategoriRequest $request, $id)
    {
        try {
            $data = $request->validated();
            $data['slug'] = url_title($data['kategori']);
            Kategori::where('id', $id)->whereNull('config_id')->update($data);

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
}
