<?php

declare(strict_types=1);

namespace App\Http\Controllers\DataSpasial;

use App\Http\Requests\DataSpasial\UbahStatusDesaRequest;
use App\Services\DataSpasialResult;
use App\Services\StatusDesaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Halaman Status Desa (KAB-09 / API-09 / SID-11).
 *
 * Pemerintah Kabupaten menonaktifkan atau mengaktifkan kembali pengisian Data Spasial per desa.
 * Status ditegakkan Modul DataSpasial di OpenSID desa (mode Terpusat); OpenKab hanya mengelola
 * lewat API Satu Data dan tidak menyimpan status secara lokal.
 *
 * Pola halaman mengikuti KAB-08: `index` = kerangka + filter, `data` = AJAX tabel,
 * `ubahStatus` = aksi tulis dengan flash.
 */
class StatusDesaController extends DataSpasialController
{
    public function __construct(
        protected StatusDesaService $statusDesa,
    ) {
        parent::__construct($this->statusDesa->dataspasial);
    }

    protected function pageTitle(): string
    {
        return 'Status Desa';
    }

    protected function viewName(): string
    {
        return 'dataspasial.desa.index';
    }

    protected function fetch(Request $request): DataSpasialResult
    {
        // Kerangka halaman tidak membaca data (dimuat lewat AJAX).
        return DataSpasialResult::empty();
    }

    public function index(Request $request): View
    {
        return view($this->viewName(), [
            'title' => $this->pageTitle(),
            'result' => $this->fetch($request),
            'pilihanStatus' => $this->statusDesa->pilihanStatus(),
            // Tombol aksi hanya tampil bagi pemilik izin tulis; route-nya tetap dijaga middleware permission.
            'bolehUbah' => (bool) $request->user()?->can('dataspasial-desa-write'),
        ]);
    }

    /**
     * Tabel desa + paginasi (AJAX).
     */
    public function data(Request $request): JsonResponse
    {
        $hasil = $this->statusDesa->daftar($request->only(['kode_kecamatan', 'status', 'page']));

        return response()->json([
            'meta' => [
                'unavailable' => ! $hasil['tersedia'],
                'empty' => $hasil['tersedia'] && $hasil['baris'] === [],
                'message' => $hasil['tersedia'] ? null : (string) config('dataspasial.unavailable_message'),
                'page' => $hasil['halaman'],
                'total_pages' => $hasil['total_halaman'],
                'total' => $hasil['total'],
                'per_page' => $hasil['per_halaman'],
            ],
            'ringkasan' => $hasil['ringkasan'],
            'baris' => $hasil['baris'],
        ]);
    }

    /**
     * Nonaktifkan atau aktifkan kembali pengisian satu desa.
     */
    public function ubahStatus(UbahStatusDesaRequest $request, int $config_id): RedirectResponse|JsonResponse
    {
        $aktif = $request->aktif();
        $result = $this->statusDesa->ubahStatus($config_id, $aktif, $request->alasan());

        if ($result->isUnavailable()) {
            $pesan = $result->httpStatus === 404
                ? 'Desa tidak ditemukan atau berada di luar kabupaten Anda.'
                : (string) config('dataspasial.unavailable_message');

            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $pesan], $result->httpStatus === 404 ? 404 : 503)
                : redirect()->route('dataspasial.desa', $request->only(['kode_kecamatan', 'status']))->with('error', $pesan);
        }

        $pesan = $this->statusDesa->pesanBerhasil($result, $aktif);

        return $request->wantsJson()
            ? response()->json(['success' => true, 'message' => $pesan, 'desa' => $result->first()])
            : redirect()->route('dataspasial.desa', $request->only(['kode_kecamatan', 'status']))->with('success', $pesan);
    }
}
