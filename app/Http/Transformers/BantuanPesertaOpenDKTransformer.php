<?php

namespace App\Http\Transformers;

use App\Models\BantuanPeserta;
use League\Fractal\TransformerAbstract;

class BantuanPesertaOpenDKTransformer extends TransformerAbstract
{
    public function transform(BantuanPeserta $bantuanPeserta)
    {
        return [
            'id' => $bantuanPeserta->id,
            'peserta' => $bantuanPeserta->peserta,
            'nama' => $bantuanPeserta->penduduk->nama ?? '',
            'nik' => $bantuanPeserta->nik,
            'no_kk' => $bantuanPeserta->no_kk,
            'program_id' => $bantuanPeserta->program_id,
            'program' => $bantuanPeserta->bantuan,
            'no_id_kartu' => $bantuanPeserta->no_id_kartu,
            'kartu_nama' => $bantuanPeserta->kartu_nama,
            'kartu_nik' => $bantuanPeserta->kartu_nik,
            'kartu_tempat_lahir' => $bantuanPeserta->kartu_tempat_lahir,
            'kartu_tanggal_lahir' => $bantuanPeserta->kartu_tanggal_lahir,
            'kartu_alamat' => $bantuanPeserta->kartu_alamat,
            'jenis_kelamin' => $bantuanPeserta->jenis_kelamin,
            'keterangan' => $bantuanPeserta->keterangan,
        ];
    }
}
