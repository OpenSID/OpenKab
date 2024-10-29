<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\BantuanRepository;
use App\Http\Repository\KeluargaRepository;
use App\Http\Repository\PendudukRepository;
use App\Http\Repository\RtmRepository;
use App\Http\Repository\StatistikRepository;
use App\Http\Transformers\StatistikTransformer;
use App\Models\Bantuan;
use App\Models\BantuanPeserta;
use App\Models\Config;
use Illuminate\Http\Response;

class StatistikController extends Controller
{
    protected $statistik;

    protected $kategori;
    protected $tahun;
    protected $kecamatan;
    protected $desa;

    public function __construct(StatistikRepository $statistik)
    {
        $this->statistik = $statistik;
        $this->kategori = request()->input('filter')['id'] ?? null;
        $this->tahun = request()->input('filter')['tahun'] ?? null;
        $this->kecamatan = request()->input('filter')['kecamatan'] ?? null;
        $this->desa = request()->input('filter')['desa'] ?? null;
    }

    public function kategoriStatistik()
    {
        if ($this->kategori) {
            return response()->json([
                'success' => true,
                'data' => $this->statistik->getKategoriStatistik($this->kategori),
            ], Response::HTTP_OK);
        }

        return response()->json([
            'success' => false,
            'message' => 'Kategori tidak ditemukan',
        ], Response::HTTP_NOT_FOUND);
    }

    public function penduduk(PendudukRepository $penduduk)
    {
        if ($this->kategori) {
            return $this->fractal($this->statistik->getStatistik($penduduk->listStatistik($this->kategori)), new StatistikTransformer(), 'statistik-penduduk')->respond();
        }

        return response()->json([
            'success' => false,
            'message' => 'Kategori tidak ditemukan',
        ], Response::HTTP_NOT_FOUND);
    }

    public function refTahunPenduduk(PendudukRepository $penduduk)
    {
        return response()->json([
            'success' => true,
            'data' => $penduduk->listTahun(),
        ], Response::HTTP_OK);
    }

    public function refTahunBantuan(BantuanRepository $bantuan)
    {
        return response()->json([
            'success' => true,
            'data' => $bantuan->tahun(),
        ], Response::HTTP_OK);
    }

    public function keluarga(KeluargaRepository $keluarga)
    {
        if ($this->kategori) {
            return $this->fractal($this->statistik->getStatistik($keluarga->listStatistik($this->kategori)), new StatistikTransformer(), 'statistik-keluarga')->respond();
        }

        return response()->json([
            'success' => false,
            'message' => 'Kategori tidak ditemukan',
        ], Response::HTTP_NOT_FOUND);
    }

    public function refTahunKeluarga(KeluargaRepository $keluarga)
    {
        return response()->json([
            'success' => true,
            'data' => $keluarga->listTahun(),
        ], Response::HTTP_OK);
    }

    public function rtm(RtmRepository $rtm)
    {
        return $this->fractal($this->statistik->getStatistik($rtm->listStatistik($this->kategori)), new StatistikTransformer(), 'statistik-rtm')->respond();
    }

    public function refTahunRtm(RtmRepository $rtm)
    {
        return response()->json([
            'success' => true,
            'data' => $rtm->listTahun(),
        ], Response::HTTP_OK);
    }

    public function bantuan(BantuanRepository $bantuan)
    {
        set_time_limit(0);
        if ($this->kategori) {
            return $this->fractal($this->statistik->getStatistik($bantuan->listStatistik($this->kategori, $this->tahun, $this->kecamatan, $this->desa)), new StatistikTransformer(), 'statistik-bantuan')->respond();
        }

        return response()->json([
            'success' => false,
            'message' => 'Kategori tidak ditemukan',
        ], Response::HTTP_NOT_FOUND);
    }
    public function getListProgram(){
        $program = Bantuan::get(['id', 'nama']);
        return $program->toJson();
    }
    public function getListTahun(){
        $tahun = Bantuan::selectRaw('YEAR(sdate) as year')->whereNotNull('slug')->distinct()->orderBy('year', 'ASC')->get();
        return $tahun->toJson();
    }
    public function getListKecamatan(){
        $tahun = Config::selectRaw('config.kode_kecamatan, config.nama_kecamatan')
                ->distinct()->orderBy('config.nama_kecamatan', 'ASC')->get();
        return $tahun->toJson();
    }
    public function getListDesa($id){
        if (!empty($id)){
            $tahun = Config::selectRaw('config.kode_desa, config.nama_desa')
                    ->where('config.kode_kecamatan','=',$id)
                    ->distinct()->orderBy('config.nama_desa', 'ASC')->get();
            return $tahun->toJson();
        }
        return null;
    }
    public function getListCoordinate(){
            $coordinate = Config::selectRaw('config.kode_propinsi, config.nama_propinsi, config.kode_kabupaten, config.nama_kabupaten,config.kode_kecamatan, config.nama_kecamatan,config.kode_desa, config.nama_desa, config.lat, config.lng, config.kode_pos');
            if(!empty($this->kecamatan)){
                $coordinate = $coordinate->where('config.kode_kecamatan','=',$this->kecamatan);
            }
            if(!empty($this->desa)){
                $coordinate = $coordinate->where('config.kode_desa','=',$this->desa);
            }

            $coordinate = $coordinate->distinct()->orderBy('config.nama_desa', 'ASC')->get();
            return $coordinate->toJson();
    }
    public function getListPenerimaBantuan(){
        if ($this->kategori) {
                $data = BantuanPeserta::join('program', 'program.id', '=', "program_peserta.program_id", 'left')
               ->join('config', 'config.id', '=', "program_peserta.config_id", 'left')
               ->join('tweb_penduduk', 'tweb_penduduk.id', '=', "program_peserta.kartu_id_pend", 'left');
            if ($this->kategori == "penduduk"){
                $data = $data->where('program.sasaran','=',1);
            }
            else if ($this->kategori == "keluarga"){
                $data = $data->where('program.sasaran','=',2);
            }
            else{
                $data = $data->where('program.id','=',$this->kategori);
            }

            if (!empty($this->tahun)){
                $data = $data->whereRaw("YEAR(program.sdate) = " . $this->tahun);
            }

            if (!empty($this->kecamatan)){
                $data = $data->where('config.kode_kecamatan','=',$this->kecamatan);
            }

            if (!empty($this->desa)){
                $data = $data->where('config.kode_desa','=',$this->desa);
            }
            $data = $data->selectRaw('program.nama as nama_program, program_peserta.kartu_nama as nama_penerima, program_peserta.kartu_alamat as alamat_penerima')->get();
            return $data->toJson();
        }

        return response()->json([
            'success' => false,
            'message' => 'Kategori tidak ditemukan',
        ], Response::HTTP_NOT_FOUND);
    }
}
