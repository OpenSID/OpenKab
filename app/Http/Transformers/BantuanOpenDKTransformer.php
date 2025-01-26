<?php

namespace App\Http\Transformers;

use App\Http\Repository\BantuanOpenDKRepository;
use App\Models\Bantuan;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class BantuanOPenDKTransformer extends TransformerAbstract
{
    public function transform(Bantuan $bantuan)
    {
        return [
            'id' => $bantuan->id,
            'nama' => $bantuan->nama,
            'sasaran' => $bantuan->sasaran,
            'nama_sasaran' => $this->getNamaSasaran($bantuan->sasaran),
            'jumlah_peserta' => $bantuan->jumlah_peserta,
            'ndesc' => $bantuan->ndesc,
            'sdate' => (Carbon::parse($bantuan->sdate))->format(config('app.format.date')),
            'edate' => (Carbon::parse($bantuan->edate))->format(config('app.format.date')),
            'status' => $bantuan->status,
            'nama_status' => $bantuan->nama_status,
            'asaldana' => $bantuan->asaldana,
            'masa_berlaku' => (Carbon::parse($bantuan->sdate))->format(config('app.format.date')).' - '.(Carbon::parse($bantuan->edate))->format(config('app.format.date')),
            'desa' => $bantuan->config->nama_desa,
            'kode_desa' => $bantuan->config->kode_desa,
        ];
    }

    private function getNamaSasaran($sasaran)
    {
        switch ($sasaran) {
            case BantuanOpenDKRepository::SASARAN_PENDUDUK:
                return 'Penduduk';
            case BantuanOpenDKRepository::SASARAN_KELUARGA:
                return 'Keluarga';
            case BantuanOpenDKRepository::SASARAN_RUMAH_TANGGA:
                return 'Rumah Tangga';
            case BantuanOpenDKRepository::SASARAN_KELOMPOK:
                return 'Kelompok';
            default:
                return 'Tidak Diketahui';
        }
    }
}
