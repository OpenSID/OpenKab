<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\PengaturanRepository;
use App\Http\Requests\PengaturanRequest;
use App\Http\Transformers\PengaturanTransformer;
use App\Models\Pengaturan;

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
            foreach ($this->pengaturan->listPengaturan() as $data) {
                if ($request->{$data->key}) {
                    Pengaturan::where('key', $data->key)->update(['value' => $request->{$data->key}]);
                }
            }

            return back()->withInput()->with('success', 'Data berhasil diubah!');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
