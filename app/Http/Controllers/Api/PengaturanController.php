<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\PengaturanRepository;
use App\Http\Requests\PengaturanRequest;
use App\Http\Transformers\PengaturanTransformer;
use App\Models\Pengaturan;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\HttpFoundation\Response;

class PengaturanController extends Controller
{
    public function __construct(protected PengaturanRepository $pengaturan)
    {
    }

    public function index()
    {
        return $this->fractal($this->pengaturan->listPengaturan(), new PengaturanTransformer(), 'daftar pengaturan')->respond();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function update(PengaturanRequest $request)
    {
        try {
            Pengaturan::where('key', 'warna_tema')->update(['value' => $request->warna_tema]);
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
