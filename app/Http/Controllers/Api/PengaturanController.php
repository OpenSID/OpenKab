<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\PengaturanRepository;
use App\Http\Requests\PengaturanRequest;
use App\Http\Transformers\PengaturanTransformer;
use App\Models\Pengaturan;
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
            foreach ($request->validated() as $key => $value) {
                Pengaturan::where('key', $key)->update(['value' => $value]);
                activity('data-log')->event('updated')->withProperties($request)->log('Pengaturan Aplikasi');
                if ($key == 'lock_theme') {
                    // udpate class tema
                    $attributeValue = null;
                    if ($value) {
                        $attributeValue = 'class="disabled" disabled';
                        Pengaturan::where('key', 'web_theme')->update(['attribute' => $attributeValue]);
                    } else {
                        Pengaturan::where('key', 'web_theme')->update(['attribute' => $attributeValue]);
                    }
                    activity('data-log')->event('updated')->withProperties(['key' => 'web_theme', 'attribute' => $attributeValue])->log('Pengaturan Aplikasi');
                }
            }

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
