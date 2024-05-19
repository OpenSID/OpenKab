<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\BantuanRepository;
use App\Http\Requests\BantuanRequest;
use App\Http\Transformers\BantuanTransformer;
use App\Models\Bantuan;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(BantuanRequest $request)
    {
        try {
            $data = $request->validated();
            $data['sdate'] = Carbon::parse($data['sdate']);
            $data['edate'] = Carbon::parse($data['edate']);
            $data['status'] = $data['sdate'] < now() && $data['edate'] < now() ? 0 : 1;
            $data['userid'] = 0;
            Bantuan::create($data);

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
     * @param Request $request
     * @param int     $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(BantuanRequest $request, $id)
    {
        try {
            $data = $request->validated();
            $data['sdate'] = Carbon::parse($data['sdate']);
            $data['edate'] = Carbon::parse($data['edate']);
            $data['status'] = $data['sdate'] < now() && $data['edate'] < now() ? 0 : 1;
            $data['userid'] = 0;
            $bantuan = Bantuan::find((int) $id);
            $bantuan->update($data);

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

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $id = (int) $request->id;
        try {
            $bantuan = Bantuan::find($id);
            $bantuan->delete();

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
