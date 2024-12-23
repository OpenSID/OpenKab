<?php

namespace App\Http\Transformers;

use App\Models\Enums\JenisKelaminEnum;
use App\Models\Suplemen;
use App\Models\SuplemenTerdata;
use League\Fractal\TransformerAbstract;

class SuplemenTerdataTransformer extends TransformerAbstract
{
    public function transform(SuplemenTerdata $suplemen)
    {
        // Ambil data_form_isian dari suplemen_terdata
        $dataFormIsian = $suplemen->data_form_isian;

        if ($dataFormIsian) {
            // Cek jika data_form_isian berupa string JSON dan decode jika perlu
            if (is_string($dataFormIsian)) {
                $dataFormIsian = json_decode($dataFormIsian, true);
            }

            // Cek jika data_form_isian sudah ter-decode menjadi array
            if (! is_array($dataFormIsian)) {
                return []; // Jika tidak ter-decode dengan benar, kembalikan array kosong
            }

            // Ambil data form_isian dari suplemen yang terkait dengan suplemen_terdata
            $formIsian = Suplemen::find($suplemen->id_suplemen)->form_isian;

            // Decode form_isian jika diperlukan (dari JSON ke array)
            $formIsian = json_decode($formIsian, true);

            // Validasi JSON decode
            if (json_last_error() !== JSON_ERROR_NONE) {
                logger('JSON Decode Error: '.json_last_error_msg());

                return [];
            }

            // Ganti key dengan label_kode dari form_isian
            $mappedData = [];
            foreach ($dataFormIsian as $key => $value) {
                // Cari label_kode yang sesuai dengan nama_kode
                $labelKode = collect($formIsian)
                    ->firstWhere('nama_kode', $key)['label_kode'] ?? $key; // Gunakan key jika tidak ada yang cocok
                $mappedData[$labelKode] = $value; // Ganti key dengan label_kode
            }

            return [
                'id' => $suplemen->id,
                'terdata_info' => $suplemen->no_kk,
                'terdata_plus' => $suplemen->nik,
                'terdata_nama' => $suplemen->terdata_nama,
                'tempatlahir' => $suplemen->tempatlahir,
                'tanggallahir' => $suplemen->tanggallahir,
                'sex' => JenisKelaminEnum::getLabel($suplemen->sex),
                'alamat' => $suplemen->alamat,
                'keterangan' => $suplemen->keterangan,
                'data_form_isian' => json_encode($mappedData), // Menampilkan data dengan label_kode
            ];
        } else {
            // Transform data suplemen
            return [
                'id' => $suplemen->id,
                'terdata_info' => $suplemen->no_kk,
                'terdata_plus' => $suplemen->nik,
                'terdata_nama' => $suplemen->terdata_nama,
                'tempatlahir' => $suplemen->tempatlahir,
                'tanggallahir' => $suplemen->tanggallahir,
                'sex' => JenisKelaminEnum::getLabel($suplemen->sex),
                'alamat' => $suplemen->alamat,
                'keterangan' => $suplemen->keterangan,
                'data_form_isian' => $suplemen->data_form_isian,
            ];
        }
    }
}
