<?php

namespace App\Http\Controllers\Api;

use App\Models\Sex;
use App\Models\Penduduk;
use Illuminate\Support\Str;
use App\Models\PendudukStatus;
use PhpParser\Node\Stmt\TryCatch;
use App\Models\PendudukStatusDasar;
use App\Http\Requests\PindahRequest;
use App\Http\Repository\PendudukRepository;
use App\Http\Transformers\PendudukTransformer;

class PendudukController extends Controller
{
    public function __construct(
        protected PendudukRepository $penduduk
    ) {
    }

    public function index()
    {
        return $this->fractal($this->penduduk->listPenduduk(), new PendudukTransformer, 'penduduk')->respond();
    }

    public function pendudukStatus()
    {
        return $this->fractal(
            $this->penduduk->pendudukReferensi(PendudukStatus::class),
            function ($referensi) {
                return [
                    'id' => $referensi->id,
                    'nama' => Str::title($referensi->nama),
                ];
            },
            'status'
        );
    }

    public function pendudukStatusDasar()
    {
        return $this->fractal(
            $this->penduduk->pendudukReferensi(PendudukStatusDasar::class),
            function ($referensi) {
                return [
                    'id' => $referensi->id,
                    'nama' => Str::title($referensi->nama),
                ];
            },
            'status-dasar'
        );
    }

    public function pendudukSex()
    {
        return $this->fractal(
            $this->penduduk->pendudukReferensi(Sex::class),
            function ($referensi) {
                return [
                    'id' => $referensi->id,
                    'nama' => Str::title($referensi->nama),
                ];
            },
            'sex'
        );
    }

    public function pindah(PindahRequest $request)
    {
        try {
            $data = $request->validated();

            // cek data di desa tujuan
            $penduduk = Penduduk::where('nik',  $data['nik'])->where('config_id', $data['config_tujuan'])->first();
            if ($penduduk) {
                $penduduk->status_dasar = 1;
                $penduduk->save();
            }else{
                $penduduk_lama =  Penduduk::where('nik',  $data['nik'])->where('config_id', $data['config_asal'])->first();
                $penduduk_lama->status_dasar = 3;
                $penduduk_lama->save();

                //update penduduk baru
                $penduduk_baru = $penduduk_lama->replicate();
                $penduduk_baru->config_id = $data['config_tujuan'];
                $penduduk_baru->id_kk = null;
                $penduduk_baru->status_dasar = 1;
                $penduduk_baru->save();

            }
        } catch (\Throwable $th) {
            dd($th);
            //throw $th;
        }
    }
}
