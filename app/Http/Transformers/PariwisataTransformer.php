<?php

namespace App\Http\Transformers;

use App\Enums\KomoditasPotensiWisataEnum;
use App\Enums\SaranaWisataEnum;
use App\Models\Komoditas;
use League\Fractal\TransformerAbstract;

class PariwisataTransformer extends TransformerAbstract
{
    public function transform(Komoditas $komoditas)
    {
        $data = $komoditas->data;

        $jenis_hiburan = array_key_exists('sarana_wisata', $data) && ! empty($data['sarana_wisata'])
            ? SaranaWisataEnum::fromValue((int) $data['sarana_wisata'])
            : null;

        $lokasi_tempat_area_wisata = in_array((int) $komoditas->komoditas, KomoditasPotensiWisataEnum::getValues(), true)
            ? KomoditasPotensiWisataEnum::fromValue((int) $komoditas->komoditas)
            : null;

        return [
            'id' => $komoditas->id,
            'nama_desa' => $komoditas->config?->nama_desa,
            'kode_desa' => $komoditas->config?->kode_desa,
            'jenis_hiburan' => $jenis_hiburan ? SaranaWisataEnum::fromValue((int) $data['sarana_wisata'])->description : 'TIDAK TAHU',
            'jumlah_penginapan' => $data['jumlah'] ?? 'TIDAK TAHU',
            'lokasi_tempat_area_wisata' => $lokasi_tempat_area_wisata ? KomoditasPotensiWisataEnum::fromValue((int) $komoditas->komoditas)->description : 'TIDAK TAHU',
            'keberadaan' => ! empty($data['keberadaan'])
                ? ($data['keberadaan'] == 1 ? 'Ada' : 'Tidak Ada')
                : 'TIDAK TAHU',
            'luas' => $data['luas'] ?? 'TIDAK TAHU',
            'tingkat_pemanfaatan' => ! empty($data['tingkat_pemanfaatan'])
                ? ($data['tingkat_pemanfaatan'] == 1 ? 'Aktif' : 'Tidak Aktif')
                : 'TIDAK TAHU',
        ];
    }
}
