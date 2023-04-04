<?php

namespace App\Http\Transformers;

use App\Models\Bantuan;
use App\Models\Penduduk;
use App\Models\BantuanPeserta;
use Illuminate\Support\Facades\DB;
use League\Fractal\TransformerAbstract;

class BantuanTransformer extends TransformerAbstract
{
    protected $connection;

    public function __construct()
    {
        $this->connection = DB::connection('openkab');
    }

    public function transform(Bantuan $bantuan)
    {
        $bantuan = $bantuan->toArray();

        $bantuan['statistik'] = $this->getStatistik($bantuan['id'], $bantuan['sasaran']);

        return $bantuan;
    }

    private function getStatistik($id, $sasaran)
    {
        $peserta = $this->getPeserta($id, $sasaran);
        $total  = $this->getTotal($id, $sasaran);

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

    private function getPeserta($id, $sasaran)
    {
        switch (true) {
            case '1':
                return $this->connection->table('program_peserta')
                            ->selectRaw('COUNT(tweb_penduduk.id) AS jumlah')
                            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki')
                            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 2 THEN tweb_penduduk.id END) AS perempuan')
                            ->join('tweb_penduduk', 'tweb_penduduk.id', '=', 'program_peserta.kartu_id_pend', 'left')
                            ->where('tweb_penduduk.status_dasar', 1)
                            ->where('program_peserta.program_id', $id)
                            ->first();

            case '2':
                return $this->connection->table('program_peserta')
                            ->selectRaw('COUNT(tweb_penduduk.id) AS jumlah')
                            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki')
                            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 2 THEN tweb_penduduk.id END) AS perempuan')
                            ->join('tweb_keluarga', 'tweb_keluarga.nokk = program_peserta.peserta', 'left')
                            ->join('tweb_penduduk', 'tweb_penduduk.id', '=', 'program_peserta.kartu_id_pend', 'left')
                            ->where('tweb_penduduk.status_dasar', 1)
                            ->where('program_peserta.program_id', $id)
                            ->first();

            case '3':
                return $this->connection->table('program_peserta')
                            ->selectRaw('COUNT(tweb_penduduk.id) AS jumlah')
                            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki')
                            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 2 THEN tweb_penduduk.id END) AS perempuan')
                            ->join('tweb_rtm', 'tweb_rtm.no_kk = program_peserta.peserta', 'left')
                            ->join('tweb_penduduk', 'tweb_penduduk.id', '=', 'program_peserta.kartu_id_pend', 'left')
                            ->where('tweb_penduduk.status_dasar', 1)
                            ->where('program_peserta.program_id', $id)
                            ->first();

            case '4':
                return $this->connection->table('program_peserta')
                            ->selectRaw('COUNT(tweb_penduduk.id) AS jumlah')
                            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki')
                            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 2 THEN tweb_penduduk.id END) AS perempuan')
                            ->join('kelompok', 'kelompok.id = program_peserta.peserta', 'left')
                            ->join('tweb_penduduk', 'kelompok.id_ketua = tweb_penduduk.id', 'left')
                            ->where('tweb_penduduk.status_dasar', 1)
                            ->where('program_peserta.program_id', $id)
                            ->first();

            default:
                return [];
        }
    }

    private function getTotal($id, $sasaran)
    {
        switch (true) {
            case '1':
                return $this->connection->table('tweb_penduduk')
                            ->selectRaw('COUNT(id) AS jumlah')
                            ->selectRaw('COUNT(CASE WHEN sex = 1 THEN id END) AS laki')
                            ->selectRaw('COUNT(CASE WHEN sex = 2 THEN id END) AS perempuan')
                            ->where('status_dasar', 1)
                            ->first();


            case '2':
                return $this->connection->table('tweb_keluarga')
                            ->selectRaw('COUNT(id) AS jumlah')
                            ->selectRaw('COUNT(CASE WHEN sex = 1 THEN id END) AS laki')
                            ->selectRaw('COUNT(CASE WHEN sex = 2 THEN id END) AS perempuan')
                            ->join('tweb_penduduk', 'tweb_keluarga.nik_kepala = tweb_penduduk.id', 'left')
                            ->where('tweb_penduduk.status_dasar', 1)
                            ->first();

            case '3':
                return $this->connection->table('tweb_rtm')
                            ->selectRaw('COUNT(id) AS jumlah')
                            ->selectRaw('COUNT(CASE WHEN sex = 1 THEN id END) AS laki')
                            ->selectRaw('COUNT(CASE WHEN sex = 2 THEN id END) AS perempuan')
                            ->join('tweb_penduduk', 'tweb_rtm.no_kk = tweb_penduduk.id', 'left')
                            ->where('tweb_penduduk.status_dasar', 1)
                            ->first();

            case '4':
                return $this->connection->table('kelompok')
                            ->selectRaw('COUNT(id) AS jumlah')
                            ->selectRaw('COUNT(CASE WHEN sex = 1 THEN id END) AS laki')
                            ->selectRaw('COUNT(CASE WHEN sex = 2 THEN id END) AS perempuan')
                            ->join('tweb_penduduk', 'kelompok.id_ketua = tweb_penduduk.id', 'left')
                            ->where('tweb_penduduk.status_dasar', 1)
                            ->first();

            default:
                return [];
        }
    }
}
