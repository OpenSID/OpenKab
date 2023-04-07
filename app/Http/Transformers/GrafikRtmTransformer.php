<?php

namespace App\Http\Transformers;

use App\Models\Rtm;
use Fractal\Resource\Collection;
use Illuminate\Support\Facades\DB;
use League\Fractal\TransformerAbstract;

class GrafikRtmTransformer extends TransformerAbstract
{
    /** {@inheritdoc} */
    protected $connection = 'openkab';

    /** {@inheritdoc} */
    protected $dbConnection;

    /**
     * constract
     */
    public function __construct()
    {
        $this->dbConnection = DB::connection($this->connection);
    }

    public function transform($user)
    {
        return [
            'id' => $user['id'],
            'nama' => [
                'sy' => $user,
            ],
        ];
    }

    private function getStatistik()
    {
        return null;

        $peserta = $this->getPeserta();
        $total  = $this->getTotal();

        return [
            [
                'id'        => 1,
                'jumlah'    => $peserta->jumlah,
                'laki_laki' => $peserta->laki_laki,
                'perempuan' => $peserta->perempuan,
                'persentase_jumlah' => $total->jumlah > 0 ? $peserta->jumlah / $total->jumlah * 100 : 0,
                'persentase_laki_laki' => $total->laki_laki > 0 ? $peserta->laki_laki / $total->laki_laki * 100 : 0,
                'persentase_perempuan' => $total->perempuan > 0 ? $peserta->perempuan / $total->perempuan * 100 : 0,
                'nama'      => 'Peserta',
            ],
            [
                'id'        => 2,
                'jumlah'    => $total->jumlah - $peserta->jumlah,
                'laki_laki' => $total->laki_laki - $peserta->laki_laki,
                'perempuan' => $total->perempuan - $peserta->perempuan,
                'persentase_jumlah' => $total->jumlah > 0 ? ($total->jumlah - $peserta->jumlah) / $total->jumlah * 100 : 0,
                'persentase_laki_laki' => $total->laki_laki > 0 ? ($total->laki_laki - $peserta->laki_laki) / $total->laki_laki * 100 : 0,
                'persentase_perempuan' => $total->perempuan > 0 ? ($total->perempuan - $peserta->perempuan) / $total->perempuan * 100 : 0,
                'nama'      => 'Bukan Peserta',
            ],
            [
                'id'        => 3,
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
        return $this->getRtmRaw()->where('tweb_rtm.bdt', '=', null)->get();
    }

    private function getTotal()
    {
        return $this->getRtmRaw()->get();
    }

    private function getRtmRaw()
    {
        return $this->dbConnection->table('tweb_rtm')
            ->selectRaw('COUNT(tweb_penduduk.id) AS jumlah')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki_laki')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 2 THEN tweb_penduduk.id END) AS perempuan')
            ->join('tweb_penduduk', 'tweb_penduduk.id', '=', 'tweb_rtm.nik_kepala', 'left');
    }
}
