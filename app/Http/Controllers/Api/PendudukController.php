<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\PendudukRepository;
use App\Http\Requests\PindahRequest;
use App\Http\Requests\SyncRemovePendudukRequest;
use App\Http\Requests\SyncStorePendudukRequest;
use App\Http\Transformers\PendudukTransformer;
use App\Imports\SyncPendudukImport;
use App\Jobs\PendudukQueueJob;
use App\Models\LogKeluarga;
use App\Models\LogPenduduk;
use App\Models\Penduduk;
use App\Models\PendudukStatus;
use App\Models\PendudukStatusDasar;
use App\Models\Sex;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use ZipArchive;
use Illuminate\Support\Facades\Storage;

class PendudukController extends Controller
{
    public function __construct(
        protected PendudukRepository $penduduk
    ) {
    }

    public function index()
    {
        return $this->fractal($this->penduduk->listPenduduk(), new PendudukTransformer, 'penduduk')->respond();
    }

    public function pendudukStatus()
    {
        return $this->fractal(
            $this->penduduk->pendudukReferensi(PendudukStatus::class),
            function ($referensi) {
                return [
                    'id' => $referensi->id,
                    'nama' => Str::title($referensi->nama),
                ];
            },
            'status'
        );
    }

    public function pendudukStatusDasar()
    {
        return $this->fractal(
            $this->penduduk->pendudukReferensi(PendudukStatusDasar::class),
            function ($referensi) {
                return [
                    'id' => $referensi->id,
                    'nama' => Str::title($referensi->nama),
                ];
            },
            'status-dasar'
        );
    }

    public function pendudukSex()
    {
        return $this->fractal(
            $this->penduduk->pendudukReferensi(Sex::class),
            function ($referensi) {
                return [
                    'id' => $referensi->id,
                    'nama' => Str::title($referensi->nama),
                ];
            },
            'sex'
        );
    }

    public function pindah(PindahRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // ambil data penduduk
            $penduduk_lama = Penduduk::where('id', $data['id'])->where('config_id', $data['config_asal'])->first();
            // ambil data penduduk di desa tujuan
            $penduduk_tujuan = Penduduk::where('config_id', $data['kelurahan_tujuan'])->where('nik', $penduduk_lama->nik)->first();

            // cek log penduduk lama
            // jika sudah pernah pindah, tidak bisa melakukan pindah
            $log_penduduk_lama = LogPenduduk::where('config_id', $data['config_asal'])
                ->where('id_pend', $data['id'])
                ->where('kode_peristiwa', 3)
                ->where('tgl_peristiwa', $data['tgl_peristiwa'])
                ->exists();

            if ($log_penduduk_lama) {
                DB::rollback();

                return response()->json([
                    'success' => false,
                    'message' => 'Penduduk Sudah tercatat Pindah di tanggal tersebut.',
                ], Response::HTTP_OK);
            }

            if ($penduduk_tujuan) {
                // cek log penduduk tujuan
                // jika di log penduduk, tgl peristiwa sudah ada di desa tujuan, perpindahan tidak bisa dilakukan
                $log_penduduk_tujuan = LogPenduduk::where('config_id', $data['kelurahan_tujuan'])
                    ->where('id_pend', $penduduk_tujuan->id)
                    ->where('kode_peristiwa', 3)
                    ->where('tgl_peristiwa', $data['tgl_peristiwa'])
                    ->exists();

                if ($log_penduduk_tujuan) {
                    DB::rollback();

                    return response()->json([
                        'success' => false,
                        'message' => 'Penduduk Sudah tercatat Pindah di tanggal tersebut.',
                    ], Response::HTTP_OK);
                }

                $penduduk_tujuan->status_dasar = 1;
                $penduduk_tujuan->save();
                $log_penduduk = [
                    'id_pend' => $penduduk_tujuan->id,
                    'config_id' => $data['kelurahan_tujuan'],
                    'kode_peristiwa' => 1,
                    'tgl_lapor' => $request->tgl_lapor,
                    'tgl_peristiwa' => $request->tgl_peristiwa,
                    'catatan' => $request->catatan,
                    'no_kk' => $penduduk_tujuan->keluarga->no_kk,
                    'nama_kk' => $penduduk_tujuan->keluarga->kepala_keluarga,
                    'ref_pindah' => $request->ref_pindah,
                    'catatan' => $request->catatan,
                ];
            } else {
                //update penduduk baru
                $penduduk_baru = $penduduk_lama->replicate();
                $penduduk_baru->config_id = $data['kelurahan_tujuan'];
                $penduduk_baru->id_kk = null;
                $penduduk_baru->status_dasar = 1;
                $penduduk_baru->save();

                $log_penduduk = [
                    'id_pend' => $penduduk_baru->id,
                    'config_id' => $data['kelurahan_tujuan'],
                    'kode_peristiwa' => 1,
                    'tgl_lapor' => $request->tgl_lapor,
                    'tgl_peristiwa' => $request->tgl_peristiwa,
                    'catatan' => $request->catatan,
                    'no_kk' => $penduduk_baru->keluarga->no_kk,
                    'nama_kk' => $penduduk_baru->keluarga->kepala_keluarga,
                    'ref_pindah' => $request->ref_pindah,
                    'catatan' => $request->catatan,
                ];
            }

            $penduduk_lama->status_dasar = 3;
            $penduduk_lama->save();

            // LOG keluarga
            if ($penduduk_lama->keluarga->id) {
                LogKeluarga::create([
                    'id_kk' => $penduduk_lama->keluarga->id,
                    'config_id' => $penduduk_lama->config_id,
                    'id_peristiwa' => 3,
                    'updated_by' => 1,
                    'id_log_penduduk' => $penduduk_lama->id,
                ]);
            }

            // LOG penduduk
            LogPenduduk::create([
                'id_pend' => $penduduk_lama->id,
                'kode_peristiwa' => 3,
                'config_id' => $penduduk_lama->config_id,
                'alamat_tujuan' => $request->alamat_tujuan,
                'tgl_lapor' => $request->tgl_lapor,
                'tgl_peristiwa' => $request->tgl_peristiwa,
                'catatan' => $request->catatan,
                'no_kk' => $penduduk_lama->keluarga->no_kk,
                'nama_kk' => $penduduk_lama->keluarga->kepala_keluarga,
                'ref_pindah' => $request->ref_pindah,
                'catatan' => $request->catatan,
            ]);

            // LOG penduduk Desa Tujuan
            LogPenduduk::create($log_penduduk);
            DB::commit();

            return response()->json([
                'success' => true,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollback();
            report($e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Hapus Data Penduduk Sesuai OpenSID
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncRemovePenduduk(SyncRemovePendudukRequest $request)
    {
        // dispatch queue job penduduk
        PendudukQueueJob::dispatch($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Proses sync Data Penduduk OpenSID sedang berjalan',
        ]);
    }

    /**
     * Tambah dan Ubah Data dan Foto Penduduk Sesuai OpenSID
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncStorePenduduk(SyncStorePendudukRequest $request)
    {
        try {
            // Upload file zip temporary.
            $file = $request->file('file');
            $file->storeAs('temp', $name = $file->getClientOriginalName());

            // Temporary path file
            $path = storage_path("app/temp/{$name}");
            $extract = storage_path('app/public/penduduk/foto/');

            // Ekstrak file
            $zip = new ZipArchive();
            $zip->open($path);
            $zip->extractTo($extract);
            $zip->close();

            // Proses impor excell
            (new SyncPendudukImport($request->kode_kecamatan))
                ->queue($extract.$excellName = Str::replaceLast('zip', 'xlsx', $name));
        } catch (\Exception $e) {
            report($e);

            return back()->with('error', 'Import data gagal.');
        }

        // Hapus folder temp ketika sudah selesai
        Storage::deleteDirectory('temp');
        // Hapus file excell temp ketika sudah selesai
        Storage::disk('public')->delete('penduduk/foto/'.$excellName);

        return response()->json([
            'message' => 'Data Foto Telah Berhasil di Sinkronkan',
        ]);
    }

}
