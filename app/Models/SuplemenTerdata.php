<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuplemenTerdata extends BaseModel
{
    use HasFactory;

    public const PENDUDUK = 1;

    public const KELUARGA = 2;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'suplemen_terdata';

    /**
     * The timestamps for the model.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The guarded with the model.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['suplemen', 'penduduk'];

    public function suplemen()
    {
        return $this->belongsTo(Suplemen::class, 'id_suplemen');
    }

    public function penduduk()
    {
        return $this->belongsTo(Penduduk::class, 'penduduk_id');
    }

    public function keluarga()
    {
        return $this->belongsTo(Keluarga::class, 'keluarga_id');
    }

    public function scopeAnggota($query, $sasaran, $suplemen): ?array
    {
        switch ($sasaran) {
            case SuplemenTerdata::PENDUDUK:
                $query->join('tweb_penduduk', 'tweb_penduduk.id', '=', 'suplemen_terdata.penduduk_id', 'left')
                    ->join('tweb_keluarga', 'tweb_keluarga.id', '=', 'tweb_penduduk.id_kk', 'left')
                    ->selectRaw('no_kk as terdata_info')
                    ->selectRaw('nik as terdata_plus')
                    ->selectRaw('nama as terdata_nama');
                break;

            case SuplemenTerdata::KELUARGA:
                $query->join('tweb_keluarga', 'tweb_keluarga.id', '=', 'suplemen_terdata.keluarga_id', 'left')
                    ->join('tweb_penduduk', 'tweb_penduduk.id', '=', 'tweb_keluarga.nik_kepala', 'left')
                    ->selectRaw('nik as terdata_info')
                    ->selectRaw('no_kk as terdata_plus')
                    ->selectRaw('nama as terdata_nama');
                break;

            default:
                return [];
        }

        // $query->join('tweb_wil_clusterdesa', 'tweb_wil_clusterdesa.id', '=', 'tweb_penduduk.id_cluster', 'left')
        //     ->selectRaw('suplemen_terdata.*, tweb_penduduk.nik, tweb_penduduk.nama, tweb_penduduk.tempatlahir, tweb_penduduk.tanggallahir, tweb_penduduk.sex, tweb_keluarga.no_kk, tweb_wil_clusterdesa.rt, tweb_wil_clusterdesa.rw, tweb_wil_clusterdesa.dusun, tweb_wil_clusterdesa.id as dusun_id')
        //     ->selectRaw('(case when (tweb_penduduk.id_kk is null) then tweb_penduduk.alamat_sekarang else tweb_keluarga.alamat end) AS alamat')
        //     ->where('id_suplemen', $suplemen);

        $query->join('tweb_wil_clusterdesa', 'tweb_wil_clusterdesa.id', '=', 'tweb_penduduk.id_cluster', 'left')
            ->join('config', 'config.id', '=', 'tweb_wil_clusterdesa.config_id', 'left') // Tambahkan join ini
            ->selectRaw('suplemen_terdata.*, 
                   tweb_penduduk.nik, 
                   tweb_penduduk.nama, 
                   tweb_penduduk.tempatlahir, 
                   tweb_penduduk.tanggallahir, 
                   tweb_penduduk.sex, 
                   tweb_keluarga.no_kk, 
                   tweb_wil_clusterdesa.rt, 
                   tweb_wil_clusterdesa.rw, 
                   tweb_wil_clusterdesa.dusun, 
                   tweb_wil_clusterdesa.id as dusun_id, 
                   config.kode_desa,
                   config.kode_kecamatan, 
                   config.kode_kabupaten') // Ambil kolom dari tabel config jika dibutuhkan
            ->selectRaw('(case when (tweb_penduduk.id_kk is null) then tweb_penduduk.alamat_sekarang else tweb_keluarga.alamat end) AS alamat')
            ->where('id_suplemen', $suplemen);

        // Tambahkan validasi berdasarkan session
        if (session('desa.kode_desa')) {
            $query->where('config.kode_desa', session('desa.kode_desa'));
        }

        if (session('kecamatan.kode_kecamatan')) {
            $query->where('config.kode_kecamatan', session('kecamatan.kode_kecamatan'));
        }

        if (session('kabupaten.kode_kabupaten')) {
            $query->where('config.kode_kabupaten', session('kabupaten.kode_kabupaten'));
        }

        return null;
    }

    public function scopeSasaranPenduduk($query)
    {
        return $query->where('sasaran', self::PENDUDUK);
    }

    public function scopeSasaranKeluarga($query)
    {
        return $query->where('sasaran', self::KELUARGA);
    }
}
