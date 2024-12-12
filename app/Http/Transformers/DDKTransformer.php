<?php

namespace App\Http\Transformers;

use App\Enums\DDKEnum;
use App\Enums\DDKPilihanCheckboxEnum;
use App\Enums\DDKPilihanProduksiTahunIniEnum;
use App\Enums\DDKPilihanSelectEnum;
use App\Models\KeluargaDDK;
use League\Fractal\TransformerAbstract;

class DDKTransformer extends TransformerAbstract
{
    public function transform(KeluargaDDK $keluarga)
    {
        return [
            'id' => $keluarga->id,
            'nik' => $keluarga->no_kk,
            'kepemilikan_lahan' => collect(DDKPilihanCheckboxEnum::KEPEMILIKAN_LAHAN)
                ->map(function ($item, $key) use ($keluarga) {
                    return [
                        'kk' => $keluarga->no_kk,
                        'jenis_lahan' => $item,
                        'luas_lahan' => collect(DDKPilihanCheckboxEnum::KEPEMILIKAN_LAHAN_CHECKBOX)
                            ->get(
                                $keluarga
                                    ?->prodeskelDDK
                                    ?->detail
                                    ?->where('kode_field', DDKEnum::KODE_KEPEMILIKAN_LAHAN)
                                    ?->first()
                                    ?->value[$key] ?? null
                            ),
                    ];
                }
                )->values(),
            'produksi_tanaman_pangan' => $this->transformProduksi($keluarga, DDKPilihanProduksiTahunIniEnum::DATA[DDKPilihanProduksiTahunIniEnum::TANAMAN_PANGAN], 'k1_6a_'),
            'produksi_buah_buahan' => $this->transformProduksi($keluarga, DDKPilihanProduksiTahunIniEnum::DATA[DDKPilihanProduksiTahunIniEnum::BUAH_BUAHAN], 'k1_6b_'),
            'produksi_tanaman_obat' => $this->transformProduksi($keluarga, DDKPilihanProduksiTahunIniEnum::DATA[DDKPilihanProduksiTahunIniEnum::TANAMAN_OBAT], 'k1_6c_'),
            'produksi_perkebunan' => $this->transformProduksi($keluarga, DDKPilihanProduksiTahunIniEnum::DATA[DDKPilihanProduksiTahunIniEnum::TANAMAN_PERKEBUNAN], 'k1_6d_'),
            'produksi_hasil_ternak' => $this->transformProduksi($keluarga, DDKPilihanProduksiTahunIniEnum::DATA[DDKPilihanProduksiTahunIniEnum::PENGOLAHAN_HASIL_TERNAK], 'k1_6f_'),
            'produksi_perikanan' => $this->transformProduksi($keluarga, DDKPilihanProduksiTahunIniEnum::DATA[DDKPilihanProduksiTahunIniEnum::PERIKANAN], 'k1_6g_'),
            'pola_makan_keluarga' => collect(DDKPilihanSelectEnum::POLA_MAKAN_KELUARGA)
                ->mapWithKeys(function ($item, $key) use ($keluarga) {
                    return [$item => $key == $keluarga?->prodeskelDDK?->detail?->where('kode_field', DDKEnum::KODE_POLA_MAKAN_KELUARGA)?->first()?->value];
                }),
        ];
    }

    private function transformProduksi($keluarga, $data, $prefix)
    {
        return collect($data)
            ->map(function ($item, $key) use ($keluarga, $prefix) {
                $key++;
                $produksi = $keluarga
                    ?->prodeskelDDK
                    ?->produksi
                    ?->firstWhere('kode_komoditas', "{$prefix}{$key}");

                return [
                    'jenis_komoditas' => $item['komoditas'],
                    'satuan' => $item['satuan'],
                    'jumlah_pohon' => $produksi?->jumlah_pohon,
                    'luas_panen' => $produksi?->luas_panen,
                    'nilai_produksi' => $produksi?->nilai_produksi_per_satuan,
                    'pemasaran_hasil' => $produksi?->pemasaran_hasil,
                ];
            })
        ->filter(function ($item) {
            // Exclude items where any of the specified keys are null
            return ! is_null($item['jumlah_pohon'])
                || ! is_null($item['luas_panen'])
                || ! is_null($item['nilai_produksi'])
                || ! is_null($item['pemasaran_hasil']);
        })
        ->values();
    }
}
