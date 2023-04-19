<?php

namespace App\Http\Controllers\Api;

use App\Models\Config;

use App\Models\Kategori;
use Illuminate\Http\Request;
use App\Http\Requests\KategoriRequest;
use App\Http\Controllers\Api\Controller;
use App\Http\Repository\KategoriRepository;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Transformers\ListKategoriTransformer;

class KategoriController extends Controller
{

    public function __construct(protected KategoriRepository $keluarga)
    {
    }

    public function __invoke()
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
         return $this->fractal($this->keluarga->listKategori(), new ListKategoriTransformer(), 'daftar kategori')->respond();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(KategoriRequest $request)
    {
        try {
            $data = $request->validated();
            $parrent = $data['parrent'] ?? 0;
            $insert = [
                'config_id' => null,
                'kategori' => $data['kategori'],
                'tipe' => 1,
                'parrent' => (int) $parrent,
                'enabled' => 1,
                'slug' => url_title($data['kategori']),
                'urut' => 0
            ];
            Kategori::insert($insert);

            return response()->json([
                'success' => true,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'success' => false,
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {

        return response()->json([
            'success' => true,
            'data' => $this->keluarga->show($request->id)
        ], Response::HTTP_OK);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(KategoriRequest $request, $id)
    {
        try {
            $data = $request->validated();
            $data->slug = url_title($data['kategori']);
            Kategori::where('id' , (int) $id)->whereNull('config_id')->update($data);

            return response()->json([
                'success' => true,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            Kategori::where('id' , (int) $id)->delete();

            return response()->json([
                'success' => true,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
