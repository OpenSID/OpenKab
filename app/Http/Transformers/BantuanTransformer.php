<?php

namespace App\Http\Transformers;

use App\Models\Bantuan;
use App\Models\Penduduk;
use App\Models\BantuanPeserta;
use Illuminate\Support\Facades\DB;
use League\Fractal\TransformerAbstract;

class BantuanTransformer extends TransformerAbstract
{
    public function transform(Bantuan $bantuan)
    {
        $bantuan = $bantuan->toArray();

        $bantuan['statistik'] = $this->getStatistik($bantuan['id']);

        return $bantuan;
    }

    private function getStatistik($id)
    {
        $peserta = $this->getPeserta($id);
        $total  = $this->getTotal($id);

        return [
            'peserta' => $peserta,
            'bukan_peserta' => [
                'total' => $total->jumlah - $peserta->jumlah,
                'laki' => $total->laki - $peserta->laki,
                'perempuan' => $total->perempuan - $peserta->perempuan,
            ],
            'total' => $total,
        ];
    }

    private function getPeserta($id)
    {
        return DB::connection('openkab')
            ->table('program_peserta')
            ->selectRaw('COUNT(tweb_penduduk.id) AS jumlah')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 2 THEN tweb_penduduk.id END) AS perempuan')
            ->join('tweb_penduduk', 'tweb_penduduk.id', '=', 'program_peserta.kartu_id_pend')
            ->where('tweb_penduduk.status_dasar', 1)
            ->where('program_peserta.program_id', $id)
            ->first();
    }

    private function getTotal($id)
    {
        return DB::connection('openkab')
            ->table('tweb_penduduk')
            ->selectRaw('COUNT(id) AS jumlah')
            ->selectRaw('COUNT(CASE WHEN sex = 1 THEN id END) AS laki')
            ->selectRaw('COUNT(CASE WHEN sex = 2 THEN id END) AS perempuan')
            ->where('status_dasar', 1)
            ->first();
    }
}
