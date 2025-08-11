<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Identitas extends OpenKabModel
{
    use HasFactory;

    protected $table = 'identitas';

    protected $fillable = [
        'nama_aplikasi', 'deskripsi', 'favicon',
        'logo', 'nama_kabupaten', 'kode_kabupaten',
        'nama_provinsi', 'kode_provinsi', 'sebutan_kab', 'sebutan_desa',
    ];

    public static function pengaturan()
    {
        $identitas = self::first();

        $data['nama_aplikasi'] = $identitas->nama_aplikasi ?? config('app.namaAplikasi');
        $data['sebutanKab'] = $identitas->sebutan_kab ?? config('app.sebutanKab');
        $data['sebutanDesa'] = $identitas->sebutan_desa ?? config('app.sebutanDesa');
        $nama_kabupaten = preg_replace('/KAB/', '', $identitas->nama_kabupaten) ?? config('app.namaKab');
        $data['nama_kabupaten'] = strtolower($data['sebutanKab']) == 'kota' ? $nama_kabupaten : $data['sebutanKab'].' '.$nama_kabupaten;

        return $data;
    }
}
