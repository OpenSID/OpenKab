<?php

namespace App\Http\Transformers;

use App\Enums\Dtks\Regsosek2022kEnum;
use App\Models\DTKS;
use League\Fractal\TransformerAbstract;

class DTKSPanganTransformer extends TransformerAbstract
{
    public function transform(DTKS $dtks)
    {
        $pilihanBagian3 = Regsosek2022kEnum::pilihanBagian3();

        return [
            'id' => $dtks->id,
            'nik_kepala_rtm' => $dtks?->rtm?->kepalaKeluarga?->nik,
            'status_kepemilikan_bangunan_tempat_tinggal_yang_ditempati' => $pilihanBagian3['301a'][$dtks->kd_stat_bangunan_tinggal] ?? '',
            'luas_lantai_m2' => $dtks->luas_lantai,
            'jenis_lantai_terluas' => $pilihanBagian3['303'][$dtks->kd_jenis_lantai_terluas] ?? '',
            'jenis_dinding_terluas' => $pilihanBagian3['304'][$dtks->kd_jenis_dinding] ?? '',
            'jenis_atap_terluas' => $pilihanBagian3['305'][$dtks->kd_jenis_atap] ?? '',
            'sumber_air_minum' => $pilihanBagian3['306a'][$dtks->kd_sumber_air_minum] ?? '',
            'sumber_penerangan_utama' => $pilihanBagian3['307a'][$dtks->kd_sumber_penerangan_utama] ?? '',
            'bahan_bakar_energi_utama_untuk_memasak' => $pilihanBagian3['308'][$dtks->kd_bahan_bakar_memasak] ?? '',
            'kepemilikan_dan_penggunaan_fasilitas_tempat_buang_air_besar' => $pilihanBagian3['309a'][$dtks->kd_fasilitas_tempat_bab] ?? '',
            'tempat_pembuangan_akhir_tinja' => $pilihanBagian3['310'][$dtks->kd_pembuangan_akhir_tinja] ?? '',
        ];
    }
}
