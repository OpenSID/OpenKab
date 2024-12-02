<?php

namespace App\Http\Transformers;

use App\Models\Penduduk;
use League\Fractal\TransformerAbstract;

class PendudukProdeskelTransformer extends TransformerAbstract
{
    public function transform(Penduduk $penduduk)
    {
        // Ambil data JSON dari relasi prodeskelLembagaAdat pertama
        $dataProdeskel = optional($penduduk->prodeskelLembagaAdat->first(), function ($prodeskel) {
            return json_decode($prodeskel->data, true);
        });

        // Ambil semua data dari JSON yang sudah didekode
        $prodeskelData = $dataProdeskel ?? [];  // Jika data tidak ada, gunakan array kosong

        // Tentukan elemen-elemen yang akan dihitung (membuang bulan, tahun, tanggal)
        $keysToExclude = ['bulan', 'tahun', 'tanggal'];

        // Filter data untuk menghapus key yang tidak relevan
        $filteredData = collect($prodeskelData)->except($keysToExclude);

        // Hitung jumlah total dari nilai-nilai yang relevan
        $jumlah = $filteredData->sum(function ($value) {
            return (int) $value;  // Mengubah nilai menjadi integer dan menjumlahkannya
        });

        // Loop melalui semua data JSON dan siapkan nilai default jika data tidak ada
        $generalData = [];
        foreach ($prodeskelData as $key => $value) {
            $generalData[$key] = $value ?? 'TIDAK ADA';
        }

        return [
            'id' => $penduduk->id,
            'nama' => $penduduk->nama,
            'nik' => $penduduk->nik,
            'agama' => $penduduk->agama ? $penduduk->agama->nama : 'TIDAK TAHU',
            'suku' => $penduduk->suku ?? 'TIDAK TAHU',
            'lembaga_adat' => $generalData,  // Menampilkan semua data prodeskel secara dinamis
            'prasarana_peribadatan' => $penduduk->prodeskelPrasaranaPeribadatan && $penduduk->prodeskelPrasaranaPeribadatan->isNotEmpty()
                ? $penduduk->prodeskelPrasaranaPeribadatan->toArray()
                : 'TIDAK ADA DATA',
            'jumlah' => $jumlah,  // Menambahkan jumlah total
        ];
    }
}
