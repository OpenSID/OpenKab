<?php

namespace App\Http\Transformers;

use App\Http\Repository\KelembagaanRepository;
use App\Models\Penduduk;
use App\Models\Potensi;
use League\Fractal\TransformerAbstract;

class KelembagaanTransformer extends TransformerAbstract
{
    public function __construct(protected KelembagaanRepository $prasarana)
    {
    }

    protected function fractal(
        $data,
        null|callable|\League\Fractal\TransformerAbstract $transformer,
        null|string $resourceName = null,
    ): \Spatie\Fractal\Fractal {
        return fractal(
            $data,
            $transformer,
            \League\Fractal\Serializer\JsonApiSerializer::class
        )
            ->withResourceName($resourceName);
    }

    public function transform(Potensi $potensi)
    {
        // Hide created and updated_at
        $potensi = $potensi->setHidden(['created_at', 'updated_at']);

        // Convert to array
        $data = $potensi->toArray();

        // Extract the 'data' field
        $kelembagaanData = $data['data'] ?? [];

        // List of keys to sum up
        $fieldsToSum = [
            'pemangku_adat',
            'kepengurusan_adat',
            'rumah_adat',
            'barang_pusaka',
            'naskah',
            'lainnya',
            'musyawarah_adat',
            'sanksi_adat',
            'upacara_adat_perkawinan',
            'upacara_adat_kematian',
            'upacara_adat_kelahiran',
            'upacara_adat_cocok_tanam',
            'upacara_adat_perikanan',
            'upacara_adat_kehutanan',
            'upacara_adat_sda',
            'upacara_adat_pembangunan',
            'upacara_adat_penyelesaian_masalah',
        ];

        // Calculate the sum
        $total = 0;
        foreach ($fieldsToSum as $field) {
            $total += isset($kelembagaanData[$field]) ? (int) $kelembagaanData[$field] : 0;
        }

        $penduduk = $this->fractal($this->prasarana->penduduk($potensi->config_id), new KelembagaanPendudukTransformer(), 'penduduk')->toArray();

        $prasaranaPeribadatan = $potensi
            ->prasaranaPeribadatan()
            ->where('config_id', $potensi->config_id)
            ->get(); // Pilih kolom yang relevan

        // Add new fields to 'data'
        $kelembagaanData['penduduk'] = $penduduk;
        $kelembagaanData['prasarana_peribadatan'] = $prasaranaPeribadatan;
        $kelembagaanData['jumlah'] = $total;

        // Update the main 'data' field
        $data['data'] = $kelembagaanData;

        return $data;
    }
}
