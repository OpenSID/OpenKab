<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LaporanDesaAktifController extends Controller
{
    protected array $headers = [];

    protected string $baseUrl;

    public function __construct()
    {
        $apiKey = Setting::where('key', 'database_gabungan_api_key')->first()?->value ?? '';

        $this->headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$apiKey,
        ];

        $this->baseUrl = config('app.databaseGabunganUrl');
    }

    public function index(Request $request)
    {
        $title = 'Laporan Desa Aktif';

        $filters = [
            'kode_kabupaten' => session('kabupaten.kode_kabupaten') ?? '',
            'kode_kecamatan' => $request->get('filter.kode_kecamatan'),
            'kode_desa' => $request->get('filter.kode_desa'),
            'page' => (int) $request->get('page', 1),
            'size' => (int) $request->get('size', 20),
            'search' => $request->get('filter.search'),
            'sort' => $request->get('sort', '-nama_desa'),
        ];

        $kecamatanList = $this->apiRequest('statistik-web/get-list-kecamatan', [
            'kode_kabupaten' => $filters['kode_kabupaten'],
        ]);

        $kodeKecamatan = $filters['kode_kecamatan'] ?? null;
        $desaList = $kodeKecamatan
            ? $this->apiRequest('statistik-web/get-list-desa', [
                'kode_kabupaten' => $filters['kode_kabupaten'],
                'filter[kode_kecamatan]' => $kodeKecamatan,
            ])
            : [];

        $meta = [
            'pagination' => [
                'total' => 0,
                'page' => $filters['page'],
                'size' => $filters['size'],
            ],
        ];

        return view('laporan.desa_aktif.index', compact('title', 'filters', 'kecamatanList', 'desaList', 'meta'));
    }

    public function data(Request $request): JsonResponse
    {
        $kodeKabupaten = session('kabupaten.kode_kabupaten') ?? '';
        $kodeKecamatan = $request->get('filter[kode_kecamatan]');
        $kodeDesa = $request->get('filter[kode_desa]');
        $page = (int) $request->get('page', 1);
        $size = (int) $request->get('length', 20);
        $search = $request->get('search.value');

        $params = [
            'kode_kabupaten' => $kodeKabupaten,
            'page[size]' => $size,
            'page[number]' => $page,
        ];

        if ($kodeKecamatan) {
            $params['filter[kode_kecamatan]'] = $kodeKecamatan;
        }

        if ($kodeDesa) {
            $params['filter[kode_desa]'] = $kodeDesa;
        }

        if ($search) {
            $params['filter[search]'] = $search;
        }

        $sortColumn = $this->getSortColumn($request);
        $orderDir = $request->get('order.0.dir', 'desc');
        $params['sort'] = ($orderDir === 'asc' ? '' : '-').$sortColumn;

        $result = $this->apiRequest('desa-aktif', $params);

        return response()->json($result ?? [
            'data' => [],
            'meta' => ['pagination' => ['total' => 0]],
        ]);
    }

    public function kecamatan(Request $request): JsonResponse
    {
        $kodeKabupaten = $request->get('kode_kabupaten') ?: (session('kabupaten.kode_kabupaten') ?? '');
        $data = $this->apiRequest('statistik-web/get-list-kecamatan', [
            'kode_kabupaten' => $kodeKabupaten,
        ]);

        return response()->json(['data' => $data ?? []]);
    }

    public function desa(Request $request): JsonResponse
    {
        $kodeKabupaten = $request->get('kode_kabupaten') ?: (session('kabupaten.kode_kabupaten') ?? '');
        $kodeKecamatan = $request->get('kode_kecamatan');

        $params = ['kode_kabupaten' => $kodeKabupaten];
        if ($kodeKecamatan) {
            $params['filter[kode_kecamatan]'] = $kodeKecamatan;
        }

        $data = $this->apiRequest('statistik-web/get-list-desa', $params);

        return response()->json(['data' => $data ?? []]);
    }

    public function cetak(Request $request)
    {
        $title = 'Laporan Desa Aktif';

        $filters = [
            'kode_kabupaten' => session('kabupaten.kode_kabupaten') ?? '',
            'kode_kecamatan' => $request->get('filter.kode_kecamatan'),
            'kode_desa' => $request->get('filter.kode_desa'),
            'page' => (int) $request->get('page', 1),
            'size' => (int) $request->get('size', 100),
            'search' => $request->get('filter.search'),
            'sort' => $request->get('sort', '-nama_desa'),
        ];

        $data = $this->apiRequest('desa-aktif', [
            'kode_kabupaten' => $filters['kode_kabupaten'],
            'page[size]' => $filters['size'],
            'page[number]' => $filters['page'],
            'filter[kode_kecamatan]' => $filters['kode_kecamatan'],
            'filter[kode_desa]' => $filters['kode_desa'],
            'filter[search]' => $filters['search'],
            'sort' => $filters['sort'],
        ]);

        return view('laporan.desa_aktif.cetak', array_merge(compact('title', 'filters'), ['data' => $data ?? []]));
    }

    protected function apiRequest(string $endpoint, array $params = []): ?array
    {
        $url = rtrim($this->baseUrl, '/').'/api/v1/'.ltrim($endpoint, '/');

        try {
            $response = Http::withHeaders($this->headers)
                ->timeout(30)
                ->get($url, $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('LaporanDesaAktifController: API error', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('LaporanDesaAktifController: Exception', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    protected function getSortColumn(Request $request): string
    {
        $columns = [
            0 => 'nama_desa',
            1 => 'jml_surat_tercetak',
            2 => 'artikel_terbit',
            3 => 'terakhir_akses',
            4 => 'penduduk_terinput',
            5 => 'status',
        ];

        $columnIndex = (int) $request->get('order.0.column', 0);

        return $columns[$columnIndex] ?? 'nama_desa';
    }
}
