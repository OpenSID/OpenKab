<?php

namespace App\Http\Controllers;

use App\Enums\AccessTypeEnum;
use App\Exports\LokasiTemplateExport;
use App\Http\Requests\Peta\BulkDestroyLokasiRequest;
use App\Http\Requests\Peta\DownloadTemplateLokasiRequest;
use App\Http\Requests\Peta\ImportLokasiRequest;
use App\Http\Requests\Peta\StoreLokasiRequest;
use App\Http\Requests\Peta\UpdateLokasiRequest;
use App\Imports\LokasiImport;
use App\Services\ConfigApiService;
use App\Services\PemetaanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class PlanController extends Controller
{
    private int $tip = 3;

    public function __construct(
        private PemetaanService $peta,
        private ConfigApiService $config,
    ) {}

    public function index(int|string $parent = 0): View
    {
        $point = $this->peta->getAllPoint();

        return view('peta.lokasi.index', [
            'tip' => $this->tip,
            'parent' => $parent,
            'status' => [
                AccessTypeEnum::LOCK->value() => 'Aktif',
                AccessTypeEnum::UNLOCK->value() => 'Tidak Aktif',
            ],
            'point' => $point,
            'jenisKategori' => $this->jenisKategoriForJs($point),
            'desa' => $this->currentDesa(),
            'desaList' => $this->desaList(),
        ]);
    }

    /**
     * Bentuk data Jenis + Kategori untuk konsumsi JavaScript.
     *
     * @param  array<int, array<string, mixed>>  $point
     * @return array<int, array{id: string, nama: string, children: array<int, array{id: string, nama: string}>}>
     */
    private function jenisKategoriForJs(array $point): array
    {
        return collect($point)->map(fn (array $item): array => [
            'id' => (string) ($item['id'] ?? ''),
            'nama' => (string) ($item['nama'] ?? ''),
            'children' => collect($item['children'] ?? [])->map(fn (array $child): array => [
                'id' => (string) ($child['id'] ?? ''),
                'nama' => (string) ($child['nama'] ?? ''),
            ])->values()->all(),
        ])->values()->all();
    }

    public function ajax_lokasi_maps(string $parent, int $id): View
    {
        return view('peta.lokasi.maps', [
            'lokasi' => $this->peta->getAllPlan(['filter[id]' => $id]),
            'parent' => $parent,
            'id' => $id,
        ]);
    }

    public function show_ajax_lokasi_maps(string $parent, int $id): View
    {
        return view('peta.lokasi.template_maps', [
            'lokasi' => $this->peta->getAllPlan(['filter[id]' => $id]),
            'parent' => $parent,
            'id' => $id,
        ]);
    }

    public function detail(string $id): JsonResponse
    {
        $plan = $this->peta->planById($id);

        if ($plan === null) {
            return response()->json(['success' => false, 'message' => 'Data lokasi tidak ditemukan.'], 404);
        }

        return response()->json(['success' => true, 'data' => $plan]);
    }

    public function import(ImportLokasiRequest $request): RedirectResponse
    {
        $master = $this->findJenisKategori($request->validated('jenis'), $request->validated('kategori'));

        if ($master === null) {
            return $this->redirectBackWith('error', 'Kombinasi Jenis dan Kategori tidak valid.');
        }

        $import = new LokasiImport;
        $result = $import->process($request->file('file')->getRealPath());

        if (empty($result['meta']) && $result['valid'] === [] && $result['errors'] !== []) {
            return $this->redirectBackWith('error', 'File tidak dapat diproses. '.$this->firstErrorMessage($result['errors']));
        }

        $summary = $this->processImport($result, $master);

        Session::flash('import_summary', $summary);

        if ($summary['success'] > 0) {
            Session::flash('success', "Import selesai: {$summary['success']} berhasil, {$summary['failed']} gagal.");
        } else {
            Session::flash('error', "Import gagal: {$summary['failed']} baris ditolak.");
        }

        return redirect()->route('plan');
    }

    public function downloadTemplate(DownloadTemplateLokasiRequest $request): BinaryFileResponse|RedirectResponse
    {
        $master = $this->findJenisKategori($request->validated('jenis'), $request->validated('kategori'));

        if ($master === null) {
            return $this->redirectBackWith('error', 'Kombinasi Jenis dan Kategori tidak valid.');
        }

        $directory = storage_path('app/templates');
        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $filePath = $directory.'/template_lokasi_'.date('YmdHis').'.xlsx';

        (new LokasiTemplateExport(
            $this->desaList()->values()->all(),
            (string) $master['jenis']['nama'],
            (string) $master['kategori']['nama'],
        ))->write($filePath);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function store(StoreLokasiRequest $request): RedirectResponse
    {
        $desa = $this->resolveDesa((string) $request->validated('kode_desa'));

        if ($desa === null) {
            return $this->redirectBackWith('error', 'Desa yang dipilih tidak valid.');
        }

        $master = $this->findJenisKategori($request->validated('jenis'), $request->validated('kategori'));

        if ($master === null) {
            return $this->redirectBackWith('error', 'Kombinasi Jenis dan Kategori tidak valid.');
        }

        try {
            $this->peta->planStore($this->buildPayload($request->validated(), $master, $desa));
            Session::flash('success', 'Data lokasi berhasil disimpan.');
        } catch (Throwable $e) {
            report($e);
            Session::flash('error', 'Terjadi kesalahan saat menyimpan data lokasi.');
        }

        return redirect()->route('plan');
    }

    public function update(UpdateLokasiRequest $request, string $id): RedirectResponse
    {
        $existing = $this->peta->planById($id);

        if ($existing === null) {
            return $this->redirectBackWith('error', 'Data lokasi tidak ditemukan.');
        }

        $kodeDesa = (string) ($request->validated('kode_desa') ?? ($existing['kode_desa'] ?? session('desa.kode_desa') ?? ''));

        $desa = $this->resolveDesa($kodeDesa);

        if ($desa === null) {
            return $this->redirectBackWith('error', 'Desa yang dipilih tidak valid.');
        }

        $master = $this->findJenisKategori(
            $request->validated('jenis') ?? ($existing['jenis_id'] ?? $existing['jenis'] ?? null),
            $request->validated('kategori') ?? ($existing['kategori_id'] ?? $existing['kategori'] ?? null),
        );

        if ($master === null) {
            return $this->redirectBackWith('error', 'Kombinasi Jenis dan Kategori tidak valid.');
        }

        try {
            $this->peta->planUpdate($this->buildPayload($request->validated(), $master, $desa), $id);
            Session::flash('success', 'Data lokasi berhasil diperbarui.');
        } catch (Throwable $e) {
            report($e);
            Session::flash('error', 'Terjadi kesalahan saat memperbarui data lokasi.');
        }

        return redirect()->route('plan');
    }

    public function destroy(string $id): RedirectResponse
    {
        try {
            $this->peta->planDelete($id);
            Session::flash('success', 'Data lokasi berhasil dihapus.');
        } catch (Throwable $e) {
            report($e);
            Session::flash('error', 'Terjadi kesalahan saat menghapus data lokasi.');
        }

        return redirect()->route('plan');
    }

    public function bulkDestroy(BulkDestroyLokasiRequest $request): RedirectResponse
    {
        $ids = array_map('intval', (array) $request->validated('ids'));

        try {
            $this->peta->planBulkDelete($ids);
            Log::info('Plan bulk delete berhasil', ['ids' => $ids, 'user_id' => auth()->id()]);
            Session::flash('success', count($ids).' data lokasi berhasil dihapus.');
        } catch (Throwable $e) {
            report($e);
            Log::warning('Plan bulk delete gagal', ['ids' => $ids, 'user_id' => auth()->id()]);
            Session::flash('error', 'Terjadi kesalahan saat menghapus data lokasi.');
        }

        return redirect()->route('plan');
    }

    /**
     * Validasi lokal per baris lalu kirim seluruh baris valid sebagai SATU payload batch.
     * Desa ditentukan PER BARIS dari kolom `Nama Desa`.
     *
     * @param  array{meta: array<string, string>, rows: array<int, array<string, mixed>>, errors: array<int, array<string, mixed>>, valid: array<int, array<string, mixed>>}  $result
     * @param  array{jenis: array<string, mixed>, kategori: array<string, mixed>}  $master
     * @return array{total: int, success: int, failed: int, errors: array<int, string>}
     */
    private function processImport(array $result, array $master): array
    {
        $errors = array_map($this->formatRowError(...), $result['errors']);

        $desaByName = $this->desaNameMap();
        $existingPerDesa = [];
        $validRows = [];

        foreach ($result['valid'] as $row) {
            $namaDesa = trim((string) ($row['desa'] ?? ''));
            $key = mb_strtolower($namaDesa);
            $desa = $namaDesa !== '' && array_key_exists($key, $desaByName) ? $desaByName[$key] : null;

            if ($desa === null) {
                $errors[] = 'Baris '.$row['_row'].': Nama Desa "'.$namaDesa.'" tidak ditemukan atau ganda.';

                continue;
            }

            $kodeDesa = (string) $desa['kode_desa'];

            if (! array_key_exists($kodeDesa, $existingPerDesa)) {
                $existingPerDesa[$kodeDesa] = $this->existingLokasiNames($kodeDesa, $master['jenis']['id'], $master['kategori']['id']);
            }

            $namaKey = mb_strtolower(trim((string) ($row['nama'] ?? '')));

            if (isset($existingPerDesa[$kodeDesa][$namaKey])) {
                $errors[] = 'Baris '.$row['_row'].': data dengan nama "'.$row['nama'].'" sudah ada di desa '.$desa['nama_desa'].' (hanya data baru yang dapat diimport).';

                continue;
            }

            $existingPerDesa[$kodeDesa][$namaKey] = true;
            $validRows[] = ['row' => $row, 'config_id' => (int) $desa['id']];
        }

        $summary = [
            'total' => count($result['rows']),
            'success' => 0,
            'failed' => count($errors),
            'errors' => $errors,
        ];

        if ($validRows === []) {
            return $summary;
        }

        try {
            $refPoint = (int) $master['kategori']['id'];

            $payload = [
                'data' => array_map(
                    fn (array $item): array => $this->rowPayload($item['row'], $item['config_id'], $refPoint),
                    $validRows,
                ),
            ];

            $apiResult = $this->peta->planImport($payload);

            $apiSummary = (array) ($apiResult['summary'] ?? []);
            $apiFailed = (int) ($apiSummary['failed'] ?? 0);
            $apiSuccess = (int) ($apiSummary['success'] ?? (count($validRows) - $apiFailed));

            $summary['success'] = $apiSuccess;
            $summary['failed'] += $apiFailed;

            foreach ((array) ($apiResult['errors'] ?? []) as $error) {
                $summary['errors'][] = is_string($error)
                    ? $error
                    : 'Baris '.($error['row'] ?? '-').': '.($error['message'] ?? 'Data ditolak API.');
            }
        } catch (Throwable $e) {
            report($e);
            $summary['failed'] += count($validRows);
            $summary['errors'][] = 'Gagal mengirim data ke API database gabungan.';
        }

        return $summary;
    }

    /**
     * Payload item plan untuk API database gabungan (store/update).
     * Mengikuti bentuk item plan: `config_id`, `nama`, `desk`, `lat`, `lng`, `ref_point`.
     *
     * @param  array<string, mixed>  $data
     * @param  array{jenis: array<string, mixed>, kategori: array<string, mixed>}  $master
     * @param  array{id: string, kode_desa: string, nama_desa: string}  $desa
     * @return array<string, mixed>
     */
    private function buildPayload(array $data, array $master, array $desa): array
    {
        return [
            'config_id' => (int) $desa['id'],
            'nama' => trim((string) ($data['nama'] ?? '')),
            'desk' => trim((string) ($data['keterangan'] ?? '')),
            'lat' => (string) ($data['lat'] ?? ''),
            'lng' => (string) ($data['lng'] ?? ''),
            'ref_point' => (int) $master['kategori']['id'],
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{config_id: int, nama: string, desk: string, lat: string, lng: string, ref_point: int}
     */
    private function rowPayload(array $row, int $configId, int $refPoint): array
    {
        return [
            'config_id' => $configId,
            'nama' => trim((string) ($row['nama'] ?? '')),
            'desk' => trim((string) ($row['keterangan'] ?? '')),
            'lat' => (string) ($row['lat'] ?? ''),
            'lng' => (string) ($row['lng'] ?? ''),
            'ref_point' => $refPoint,
        ];
    }

    /**
     * Cari jenis (root point) dan kategori (child) dari master point.
     *
     * @return array{jenis: array<string, mixed>, kategori: array<string, mixed>}|null
     */
    private function findJenisKategori(mixed $jenisId, mixed $kategoriId): ?array
    {
        if ($jenisId === null || $kategoriId === null) {
            return null;
        }

        foreach ($this->peta->getAllPoint() as $root) {
            if ((string) ($root['id'] ?? '') !== (string) $jenisId) {
                continue;
            }

            foreach ($root['children'] ?? [] as $child) {
                if ((string) ($child['id'] ?? '') === (string) $kategoriId) {
                    return ['jenis' => $root, 'kategori' => $child];
                }
            }
        }

        return null;
    }

    /**
     * @return array<string, bool>
     */
    private function existingLokasiNames(string $kodeDesa, mixed $jenisId, mixed $kategoriId): array
    {
        $names = [];

        try {
            $plans = $this->peta->getAllPlan([
                'filter[kode_desa]' => $kodeDesa,
                'filter[jenis]' => $jenisId,
                'filter[kategori]' => $kategoriId,
                'page[size]' => 10000,
            ]);
        } catch (Throwable $e) {
            report($e);

            return $names;
        }

        foreach ((array) $plans as $plan) {
            $plan = (array) $plan;
            if (isset($plan['nama'])) {
                $names[mb_strtolower(trim((string) $plan['nama']))] = true;
            }
        }

        return $names;
    }

    /**
     * @param  array<string, mixed>  $error
     */
    private function formatRowError(array $error): string
    {
        $row = $error['row'] ?? 0;

        if (isset($error['messages']) && is_array($error['messages'])) {
            return 'Baris '.$row.': '.implode(' ', $error['messages']);
        }

        return 'Baris '.$row.': '.($error['message'] ?? 'Data tidak valid.');
    }

    /**
     * @param  array<int, array<string, mixed>>  $errors
     */
    private function firstErrorMessage(array $errors): string
    {
        return $errors === [] ? '' : $this->formatRowError($errors[0]);
    }

    /**
     * @return array{kode_desa?: string, nama_desa?: string}
     */
    private function currentDesa(): array
    {
        $desa = session('desa');

        if (is_object($desa)) {
            $desa = (array) $desa;
        }

        if (is_array($desa) && ! empty($desa['kode_desa'])) {
            return [
                'kode_desa' => (string) $desa['kode_desa'],
                'nama_desa' => (string) ($desa['nama_desa'] ?? ''),
            ];
        }

        $kodeDesa = (string) (session('kode_desa') ?? '');

        return $kodeDesa !== '' ? ['kode_desa' => $kodeDesa] : [];
    }

    /**
     * @return array{id: string, kode_desa: string, nama_desa: string}|null
     */
    private function resolveDesa(string $kodeDesa): ?array
    {
        if ($kodeDesa === '') {
            return null;
        }

        $found = $this->desaList()->firstWhere('kode_desa', $kodeDesa);

        if ($found !== null) {
            return [
                'id' => (string) $found['id'],
                'kode_desa' => $found['kode_desa'],
                'nama_desa' => $found['nama_desa'],
            ];
        }

        if ((string) session('desa.kode_desa') === $kodeDesa) {
            return [
                'id' => (string) (session('desa.id') ?? ''),
                'kode_desa' => $kodeDesa,
                'nama_desa' => (string) session('desa.nama_desa'),
            ];
        }

        return null;
    }

    /**
     * Peta `nama_desa` (lowercase) ke data desa. Nilai `null` menandakan nama ganda (ambigu).
     *
     * @return array<string, array{id: string, kode_desa: string, nama_desa: string, nama_kecamatan: string}|null>
     */
    private function desaNameMap(): array
    {
        $map = [];

        foreach ($this->desaList() as $desa) {
            $key = mb_strtolower(trim((string) $desa['nama_desa']));

            if ($key === '') {
                continue;
            }

            $map[$key] = array_key_exists($key, $map) ? null : $desa;
        }

        return $map;
    }

    /**
     * @return Collection<int, array{id: string, kode_desa: string, nama_desa: string, nama_kecamatan: string}>
     */
    private function desaList(): Collection
    {
        return collect($this->config->desaConfig())
            ->map(fn ($desa): array => [
                'id' => $desa->id ?? '',
                'kode_desa' => (string) ($desa->kode_desa ?? ''),
                'nama_desa' => (string) ($desa->nama_desa ?? ''),
                'nama_kecamatan' => (string) ($desa->nama_kecamatan ?? ''),
            ])
            ->filter(fn (array $desa): bool => $desa['kode_desa'] !== '')
            ->sortBy('nama_desa')
            ->values();
    }

    private function redirectBackWith(string $key, string $message): RedirectResponse
    {
        return redirect()->route('plan')->with($key, $message);
    }
}
