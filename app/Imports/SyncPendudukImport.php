<?php

namespace App\Imports;

use App\Models\Config;
use Exception;
use App\Models\SyncPenduduk;
use App\Models\SyncTingkatPendidikan;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SyncPendudukImport implements ToCollection, WithHeadingRow, WithChunkReading, ShouldQueue
{
    use Importable;

    protected $kode_kecamatan;

    public function __construct($kode_kecamatan)
    {
        $this->kode_kecamatan = $kode_kecamatan;
    }

    /**
     * {@inheritdoc}
     */
    public function chunkSize(): int
    {
        return 1000;
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        DB::beginTransaction();

        try {
            $kode_desa = Arr::flatten(Config::where('kode_kecamatan', str_replace('.', '', $this->kode_kecamatan))->pluck('kode_desa'));

            foreach ($collection as $index => $value) {

                $kd_desa = str_replace('.', '', $value['desa_id']);

                if (!in_array($kd_desa, $kode_desa)) {
                    Log::debug('Desa tidak terdaftar');

                    DB::rollBack(); // rollback data yang sudah masuk karena ada data yang bermasalah
                    Storage::deleteDirectory('temp'); // Hapus folder temp ketika gagal

                    throw  new Exception('kode Desa pada baris ke-' . $index + 2 . ' tidak terdaftar . kode Desa yang bermasalah : ' . $value['desa_id']);
                }

                $config = Config::where('kode_desa', $kd_desa)->first();

                $insert = [
                    'config_id' => $config?->id,
                    'nik' => $value['nomor_nik'],
                    'nama' => $value['nama'],
                    'no_kk' => $value['nomor_kk'],
                    'sex' => $value['jenis_kelamin'],
                    'tempat_lahir' => $value['tempat_lahir'],
                    'tanggal_lahir' => $value['tanggal_lahir'],
                    'agama_id' => $value['agama'],
                    'pendidikan_kk_id' => $value['pendidikan_dlm_kk'],
                    'pendidikan_sedang_id' => $value['pendidikan_sdg_ditempuh'],
                    'pekerjaan_id' => $value['pekerjaan'],
                    'status_kawin' => $value['kawin'],
                    'kk_level' => $value['hubungan_keluarga'],
                    'warga_negara_id' => $value['kewarganegaraan'],
                    'nama_ibu' => $value['nama_ibu'],
                    'nama_ayah' => $value['nama_ayah'],
                    'golongan_darah_id' => $value['gol_darah'],
                    'akta_lahir' => $value['akta_lahir'],
                    'dokumen_pasport' => $value['nomor_dokumen_pasport'],
                    'tanggal_akhir_pasport' => $value['tanggal_akhir_pasport'],
                    'dokumen_kitas' => $value['nomor_dokumen_kitas'],
                    'ayah_nik' => $value['nik_ayah'],
                    'ibu_nik' => $value['nik_ibu'],
                    'akta_perkawinan' => $value['nomor_akta_perkawinan'],
                    'tanggal_perkawinan' => $value['tanggal_perkawinan'],
                    'akta_perceraian' => $value['nomor_akta_perceraian'],
                    'tanggal_perceraian' => $value['tanggal_perceraian'],
                    'cacat_id' => $value['cacat'],
                    'cara_kb_id' => $value['cara_kb'],
                    'hamil' => $value['hamil'],

                    // Tambahan
                    'foto' => $value['foto'],
                    'alamat_sekarang' => $value['alamat_sekarang'],
                    'alamat' => $value['alamat'],
                    'dusun' => $value['dusun'],
                    'rw' => $value['rw'],
                    'rt' => $value['rt'],
                    'desa_id' => $value['desa_id'],
                    'kecamatan_id' => $this->kode_kecamatan,
                    'kabupaten_id' => $config?->kode_kabupaten,
                    'provinsi_id' => $config?->kode_propinsi,
                    'id_pend_desa' => $value['id'],
                    'status_dasar' => $value['status_dasar'],
                    'status_rekam' => $value['status_rekam'],
                    'created_at' => $value['created_at'],
                    'updated_at' => $value['updated_at'],
                    'imported_at' => now(),
                ];

                SyncPenduduk::updateOrInsert([
                    'desa_id' => $insert['desa_id'],
                    'id_pend_desa' => $insert['id_pend_desa'],
                ], $insert);
            }

            // update rekap tingkat pendidikan
            $dt = \Carbon\Carbon::now();
            SyncTingkatPendidikan::updateOrCreate(
                [
                    'desa_id' => $insert['desa_id'],
                    'semester' => ($dt->format('n') <= 6) ? 1 : 2,
                    'tahun' => $dt->format('Y'),
                ],
                [
                    'config_id' => $config?->id,
                    'desa_id' => $insert['desa_id'],
                    'kecamatan_id' => $this->kode_kecamatan,
                    'semester' => ($dt->format('n') <= 6) ? 1 : 2,
                    'tahun' => $dt->format('Y'),
                    'tidak_tamat_sekolah' => $collection->filter(fn($value, $key) => $value['pendidikan_dlm_kk'] <= 2)->count(),
                    'tamat_sd' => $collection->filter(fn($value, $key) => $value['pendidikan_dlm_kk'] == 3)->count(),
                    'tamat_smp' => $collection->filter(fn($value, $key) => $value['pendidikan_dlm_kk'] == 4)->count(),
                    'tamat_sma' => $collection->filter(fn($value, $key) => $value['pendidikan_dlm_kk'] == 5)->count(),
                    'tamat_diploma_sederajat' => $collection->filter(fn($value, $key) => $value['pendidikan_dlm_kk'] >= 6)->count(),
                ]
            );

            DB::commit(); // Commit transaksi jika semua berhasil
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback jika ada kesalahan
            throw $e->getMessage(); // Lempar ulang error untuk ditangani lebih lanjut
        }
    }
}
