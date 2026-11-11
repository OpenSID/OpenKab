<?php

declare(strict_types=1);

namespace App\Http\Controllers\DataSpasial;

use App\Exports\AuditDataSpasialExport;
use App\Http\Requests\DataSpasial\AuditDataRequest;
use App\Services\AuditDataSpasialService;
use App\Services\ConfigApiService;
use App\Services\DataSpasialResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Halaman Log Entri Audit DataSpasial (KAB-08 / API-08).
 *
 * Menampilkan log seluruh entri & perubahan beserta pelaku dan asalnya
 * (OpenKab atau OpenSID desa). Halaman bersifat **baca-saja** — tidak ada
 * aksi ubah/hapus entri log (di luar lingkup epic).
 *
 * Pola halaman mengikuti KAB-07: `index` = kerangka + pilihan filter,
 * `data` = AJAX (kartu ringkasan + tabel), `detail` = rincian sebelum/sesudah,
 * `ekspor` = berkas Excel dari hasil filter yang sama.
 */
class AuditDataSpasialController extends DataSpasialController
{
    public function __construct(
        protected AuditDataSpasialService $audit,
        protected ConfigApiService $configApi,
    ) {
        parent::__construct($this->audit->dataspasial);
    }

    protected function pageTitle(): string
    {
        return 'Log Entri';
    }

    protected function viewName(): string
    {
        return 'dataspasial.audit.index';
    }

    protected function fetch(Request $request): DataSpasialResult
    {
        // Kerangka halaman tidak membaca data log (dimuat lewat AJAX).
        return DataSpasialResult::empty();
    }

    public function index(Request $request): View
    {
        return view($this->viewName(), [
            'title' => $this->pageTitle(),
            'result' => $this->fetch($request),
            'sektorList' => $this->daftarSektorTersimpan(),
            'desaList' => $this->daftarDesa(),
        ]);
    }

    /**
     * Kartu ringkasan + tabel + meta (kontrak `meta.validation` saat 422).
     */
    public function data(AuditDataRequest $request): JsonResponse
    {
        $built = $this->audit->buildHalaman($request->validated());

        return response()->json([
            'meta' => $built['meta'],
            'kartu' => $built['kartu'],
            'tabel' => $built['tabel'],
            'ringkasan' => $built['ringkasan'],
        ]);
    }

    /**
     * Detail satu entri audit — nilai sebelum & sesudah per isian.
     */
    public function detail(Request $request, string $id): JsonResponse
    {
        $detail = $this->audit->detail($id);

        if ($detail === null) {
            return response()->json([
                'meta' => [
                    'unavailable' => true,
                    'message' => 'Data sementara tidak tersedia',
                ],
            ], 200);
        }

        return response()->json([
            'meta' => ['unavailable' => false, 'message' => null],
            'detail' => $detail,
        ]);
    }

    /**
     * Ekspor hasil filter aktif ke berkas Excel (.xlsx).
     *
     * Isi berkas = **seluruh** baris hasil filter (bukan hanya halaman
     * yang tampil) dengan kolom yang sama seperti tabel di layar.
     */
    public function ekspor(AuditDataRequest $request): BinaryFileResponse|JsonResponse
    {
        $filter = $this->audit->filter($request->validated());
        $hasil = $this->audit->barisEkspor($filter);

        if ($hasil['baris'] === []) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data yang cocok dengan saringan.',
            ], 422);
        }

        $ekspor = new AuditDataSpasialExport($hasil['baris'], $filter, $hasil['terpotong']);
        $tempFile = tempnam(sys_get_temp_dir(), 'audit_');
        $ekspor->write($tempFile);

        return response()->download(
            $tempFile,
            'log-entri-dataspasial-'.date('Ymd-His').'.xlsx'
        )->deleteFileAfterSend();
    }

    // -----------------------------------------------------------------------

    /**
     * Daftar sektor untuk dropdown filter (di-cache 30 menit, pola dasbor).
     *
     * @return array<int, array<string, string>>
     */
    private function daftarSektorTersimpan(): array
    {
        $cached = Cache::get('kab08:sektor-list:v1');

        if (is_array($cached)) {
            return $cached;
        }

        $out = [];
        $result = $this->dataspasial->listSektor();

        if ($result->isData()) {
            foreach ($result->data as $item) {
                $atribut = is_array($item['attributes'] ?? null) ? $item['attributes'] : $item;
                $kode = $atribut['kode'] ?? null;

                if ($kode === null || $kode === '') {
                    continue;
                }

                $out[] = ['kode' => (string) $kode, 'nama' => (string) ($atribut['nama'] ?? $kode)];
            }
        }

        Cache::put('kab08:sektor-list:v1', $out, now()->addMinutes(30));

        return $out;
    }

    /**
     * Daftar desa untuk dropdown filter (di-cache service config).
     *
     * @return array<int, array<string, string>>
     */
    private function daftarDesa(): array
    {
        $out = [];

        foreach ($this->configApi->desa() as $item) {
            $atribut = is_object($item) ? (array) $item : (array) ($item['attributes'] ?? $item);
            $kode = (string) ($atribut['kode_desa'] ?? '');

            if ($kode === '') {
                continue;
            }

            $out[] = [
                'kode_desa' => $kode,
                'nama_desa' => (string) ($atribut['nama_desa'] ?? $kode),
                'kode_kecamatan' => (string) ($atribut['kode_kecamatan'] ?? ''),
            ];
        }

        return $out;
    }
}
