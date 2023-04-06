<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rtm extends Model
{
    /** {@inheritdoc} */
    protected $connection = 'openkab';

    /** {@inheritdoc} */
    protected $table = 'tweb_rtm';

    /** {@inheritdoc} */
    protected $appends = [
        'statistik',
    ];

    /** {@inheritdoc} */
    protected $dbConnection;

    /**
     * constract
     */
    public function __construct()
    {
        $this->dbConnection = DB::connection($this->connection);
    }

    public function getStatistikAttribute()
    {
        return $this->getStatistik($this->id, $this->sasaran);
    }

    private function getStatistik($id, $sasaran)
    {
        $peserta = $this->getPeserta($id, $sasaran);
        $total  = $this->getTotal($sasaran);

        return [
            [
                'jumlah'    => $peserta->jumlah,
                'laki_laki' => $peserta->laki_laki,
                'perempuan' => $peserta->perempuan,
                'persentase_jumlah' => $total->jumlah > 0 ? $peserta->jumlah / $total->jumlah * 100 : 0,
                'persentase_laki_laki' => $total->laki_laki > 0 ? $peserta->laki_laki / $total->laki_laki * 100 : 0,
                'persentase_perempuan' => $total->perempuan > 0 ? $peserta->perempuan / $total->perempuan * 100 : 0,
                'nama'      => 'Peserta',
            ],
            [
                'jumlah'    => $total->jumlah - $peserta->jumlah,
                'laki_laki' => $total->laki_laki - $peserta->laki_laki,
                'perempuan' => $total->perempuan - $peserta->perempuan,
                'persentase_jumlah' => $total->jumlah > 0 ? ($total->jumlah - $peserta->jumlah) / $total->jumlah * 100 : 0,
                'persentase_laki_laki' => $total->laki_laki > 0 ? ($total->laki_laki - $peserta->laki_laki) / $total->laki_laki * 100 : 0,
                'persentase_perempuan' => $total->perempuan > 0 ? ($total->perempuan - $peserta->perempuan) / $total->perempuan * 100 : 0,
                'nama'      => 'Bukan Peserta',
            ],
            [
                'jumlah'    => $total->jumlah,
                'laki_laki' => $total->laki_laki,
                'perempuan' => $total->perempuan,
                'persentase_jumlah' => 100,
                'persentase_laki_laki' => 100,
                'persentase_perempuan' => 100,
                'nama'      => 'Total',
            ],
        ];
    }

    private function getPeserta()
    {
        return $this->dbConnection->table('program_peserta')
            ->selectRaw('COUNT(tweb_penduduk.id) AS jumlah')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki_laki')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 2 THEN tweb_penduduk.id END) AS perempuan')
            ->where('program_peserta.program_id', $id)
            ->first();
    }

    private function getTotal()
    {
        return $this->dbConnection->table('kelompok')
            ->selectRaw('COUNT(tweb_penduduk.id) AS jumlah')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki_laki')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 2 THEN tweb_penduduk.id END) AS perempuan')
            ->first();
    }
}
