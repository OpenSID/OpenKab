<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\PendudukRepository;
use App\Http\Requests\PindahRequest;
use App\Http\Transformers\PendudukTransformer;
use App\Models\LogKeluarga;
use App\Models\LogPenduduk;
use App\Models\Penduduk;
use App\Models\PendudukStatus;
use App\Models\PendudukStatusDasar;
use App\Models\Sex;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

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
}
