<?php

namespace App\Http\Transformers;

use App\Models\Penduduk;
use League\Fractal\TransformerAbstract;

class PendidikanTransformer extends TransformerAbstract
{
    public function transform(Penduduk $penduduk)
    {
        return [
            'id' => $penduduk->id,
            'nik' => $penduduk->nik,
            'pendidikan_kk_id' => $penduduk->pendidikanKK?->nama ?? 'TIDAK TAHU',
            'pendidikan_sedang_id' => $penduduk->pendidikan?->nama ?? 'TIDAK TAHU',
            'partisipasi_sekolah' => $penduduk->dtks_anggota?->partisipasiSekolah?->nama ?? 'TIDAK TAHU',
            'pendidikan_tertinggi' => $penduduk->dtks_anggota?->pendidikanTertinggi?->nama ?? 'TIDAK TAHU',
            'kelas_tertinggi' => $penduduk->dtks_anggota?->kelasTertinggi?->nama ?? 'TIDAK TAHU',
            'ijazah_tertinggi' => $penduduk->dtks_anggota?->ijazahTertinggi?->nama ?? 'TIDAK TAHU',
        ];
    }
}
