<?php

namespace App\Http\Controllers\Web;
use App\Libraries\Statistik;
use App\Models\Bantuan;
use App\Models\BantuanPeserta;
use App\Models\Wilayah;
use App\Services\LaporanPenduduk;
use App\Http\Controllers\Controller;
use App\Models\Enums\StatistikJenisBantuanEnum;
use App\Models\Enums\SasaranEnum;
use App\Models\Enums\StatusDasarEnum;
use Illuminate\Http\Request;


class PresisiStatistikBantuanController extends Controller
{
    public function index($id = 'bantuan_penduduk')
    {
        $data = $this->dataMenu($id);

        $view = in_array($id, array_keys(StatistikJenisBantuanEnum::allKeyLabel())) ? 'presisi.bantuan.sasaran' : 'presisi.bantuan.program';

        return view($view, $data);
    }

    private function dataMenu($id)
    {
        $sasaran      = ($id == 'bantuan_penduduk') ? SasaranEnum::PENDUDUK : SasaranEnum::KELUARGA;
        $tahunPertama = Bantuan::selectRaw('YEAR(sdate) as sdate')->when($sasaran, static fn ($q) => $q->whereSasaran($sasaran))->whereNotNull('sdate')->orderByRaw('YEAR(sdate)')->first()?->sdate;
        $tahunPertama ??= date('Y');
        $config    = '';
        // $config    = $this->header['desa'];
        $heading   = LaporanPenduduk::judulStatistik($id);
        $idProgram = $this->generateIdProgram($id);
        // $statistik = getStatistikLabel($idProgram, $heading, $config['nama_desa']);
        $statistik = getStatistikLabel($idProgram, $heading, '');

        return [
            'lap'                   => $id,
            'heading'               => $heading,
            'allKategori'           => LaporanPenduduk::menuLabel(),
            'tahun_bantuan_pertama' => $tahunPertama,
            'judul_kelompok'        => 'Jenis Kelompok',
            'kategori'              => 'Program Bantuan',
            'wilayah'               => Wilayah::treeAccess(),
            'label'                 => $statistik['label'],
        ];
    }

    private function generateIdProgram($id)
    {
        $idProgram = $id;
        if (! in_array($id, array_keys(StatistikJenisBantuanEnum::allKeyLabel()))) {
            $idProgram = '50' . $id;
        }

        return $idProgram;
    }

    public function datatables(Request $request, $id = 'bantuan_penduduk')
    {
        [$filter, $filterGlobal] = $this->getFilters();
        $filterGlobal            = http_build_query($filterGlobal ?? []);
        $sasaran                 = SasaranEnum::PENDUDUK;
        $idProgram               = $this->generateIdProgram($id);
        if ($id == 'bantuan_keluarga') {
            $sasaran = SasaranEnum::KELUARGA;
        }
        if (! in_array($id, array_keys(StatistikJenisBantuanEnum::allKeyLabel()))) {
            $sasaran = Bantuan::find($id)?->sasaran;
        }

        switch($sasaran) {
            case SasaranEnum::PENDUDUK:
                $tautan_data = ci_route("penduduk.statistik.{$idProgram}");
                break;

            case SasaranEnum::KELUARGA:
                $tautan_data = ci_route("keluarga.statistik.{$idProgram}");
                break;

            case SasaranEnum::RUMAH_TANGGA:
                $tautan_data = ci_route("rtm.statistik.{$idProgram}");
                break;

            case SasaranEnum::KELOMPOK:
                $tautan_data = ci_route("kelompok.statistik.{$idProgram}");
                break;
        }

        if ($request->ajax()) {
            return datatables()->of($this->sumberData($id, $filter))
                ->addIndexColumn()
                ->editColumn('nama', static fn ($row) => strtoupper($row['nama']))
                ->editColumn('jumlah', static fn ($row) => '<a href="' . $tautan_data . '/' . $row['id'] . '/0?' . $filterGlobal . '" target="_blank">' . $row['jumlah'] . '</a>')
                ->editColumn('laki', static fn ($row) => '<a href="' . $tautan_data . '/' . $row['id'] . '/1?' . $filterGlobal . '" target="_blank">' . $row['laki'] . '</a>')
                ->editColumn('perempuan', static fn ($row) => '<a href="' . $tautan_data . '/' . $row['id'] . '/2?' . $filterGlobal . '" target="_blank">' . $row['perempuan'] . '</a>')
                ->rawColumns(['jumlah', 'laki', 'perempuan', 'nama'])
                ->make();
        }

        return show_404();
    }

    public function peserta_datatables(Request $request, $id = 'bantuan_penduduk')
    {
        if ($request->ajax()) {
            [$filter, $filterGlobal] = $this->getFilters();
            $sasaran                 = SasaranEnum::PENDUDUK;
            $query                   = BantuanPeserta::join('program', 'program.id', '=', 'program_peserta.program_id')
                ->when($filter['tahun'], static fn ($q) => $q->whereRaw("YEAR(sdate) <= {$filter['tahun']}")->whereRaw("YEAR(edate) >= {$filter['tahun']}"))
                ->when($filter['status'], static fn ($q) => $q->whereStatus($filter['status']));
            $cluster = $filter['cluster'];

            switch($id) {
                case 'bantuan_penduduk':
                    $sasaran = SasaranEnum::PENDUDUK;
                    break;

                case 'bantuan_keluarga':
                    $sasaran = SasaranEnum::KELUARGA;
                    break;

                default:
                    $query->where('program.id', $id);
                    $sasaran = Bantuan::find($id)->sasaran;
            }
            $query->whereSasaran($sasaran);

            switch($sasaran) {
                case SasaranEnum::PENDUDUK:
                    $query->when($cluster, static fn ($r) => $r->whereHas('penduduk', static fn ($s) => $s->whereIn('id_cluster', $cluster)));
                    break;

                case SasaranEnum::KELUARGA:
                    $query->when($cluster, static fn ($r) => $r->whereHas('keluarga', static fn ($s) => $s->whereHas('kepalaKeluarga', static fn ($r) => $r->whereIn('id_cluster', $cluster))));
                    break;

                case SasaranEnum::RUMAH_TANGGA:
                    $query->when($cluster, static fn ($r) => $r->whereHas('rtm', static fn ($s) => $s->whereHas('kepalaKeluarga', static fn ($r) => $r->whereIn('id_cluster', $cluster))));
                    break;

                case SasaranEnum::KELOMPOK:
                    break;
            }

            return datatables()->of($query)
                ->addIndexColumn()
                ->make();
        }
    }
}
