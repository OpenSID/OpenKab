<?php

namespace App\Http\Controllers\Api;

use App\Models\Config;

use App\Models\Bantuan;
use Illuminate\Http\Request;
use App\Http\Requests\BantuanRequest;
use App\Http\Controllers\Api\Controller;
use App\Http\Repository\BantuanRepository;
use App\Http\Transformers\BantuanTransformer;
use Symfony\Component\HttpFoundation\Response;

class BantuanKabupatenController extends Controller
{

    public function __construct(protected BantuanRepository $bantuan)
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
         return $this->fractal($this->bantuan->listBantuanKabupaten(), new BantuanTransformer(), 'daftar bantuan kabupaten')->respond();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BantuanRequest $request)
    {
        try {
            $data = $request->validated();
            $insert = [
                'config_id' => null,
                'nama'      => $data['nama'],
                'ndesc'     => $data['ndesc'],
                'sdate'     => $data['sdate'],
                'ndate'     => $data['ndate'],
                'asaldana'  => $data['asaldana'],
            ];
            Bantuan::insert($insert);

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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(BantuanRequest $request, $id)
    {
        try {
            $data = $request->validated();
            $update = [
                'config_id' => null,
                'nama'      => $data['nama'],
                'ndesc'     => $data['ndesc'],
                'sdate'     => $data['sdate'],
                'ndate'     => $data['ndate'],
                'asaldana'  => $data['asaldana'],
            ];
            Bantuan::where('id' , (int) $id)->whereNull('config_id')->update($update);

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
    public function destroy(Request $request)
    {
        try {
            Bantuan::where('id' , (int) $request->id)->delete();

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
