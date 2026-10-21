<?php

declare(strict_types=1);

namespace App\Http\Controllers\DataSpasial;

use App\Exports\ImporDataSpasialTemplateExport;
use App\Http\Requests\DataSpasial\PreviewImporRequest;
use App\Http\Requests\DataSpasial\UploadImporRequest;
use App\Services\ConfigApiService;
use App\Services\DataSpasialApiService;
use App\Services\DataSpasialResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use OpenSpout\Reader\XLSX\Reader;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImporDataController extends DataSpasialController
{
    public function __construct(
        DataSpasialApiService $dataspasial,
        protected ConfigApiService $configApiService
    ) {
        parent::__construct($dataspasial);
    }

    protected function pageTitle(): string
    {
        return 'Impor Data';
    }

    protected function viewName(): string
    {
        return 'dataspasial.impor.index';
    }

    protected function fetch(Request $request): DataSpasialResult
    {
        return DataSpasialResult::empty([], null, 200);
    }

    public function index(Request $request): View
    {
        $sektors = $this->dataspasial->listSektor();
        $kecamatans = $this->configApiService->kecamatan();

        return view($this->viewName(), [
            'title' => $this->pageTitle(),
            'sektors' => $sektors,
            'kecamatans' => $kecamatans,
        ]);
    }

    public function downloadTemplate(Request $request)
    {
        $request->validate([
            'sektor' => 'required|string',
        ]);

        $sektor = $request->input('sektor');

        $desaList = $this->getDesaListForTemplate($request);
        
        $skema = $this->dataspasial->listSkema(['sektor' => $sektor]);
        $items = $this->extractSkemaItems($skema);

        $filename = 'template_impor_'.str_replace([' ', '/'], '_', strtolower($sektor)).'_'.date('YmdHis').'.xlsx';

        $periode = $request->input('periode', date('Y-m'));
        $export = new ImporDataSpasialTemplateExport($desaList, $items, $sektor, $periode);
        $tempFile = tempnam(sys_get_temp_dir(), 'tmpl_');
        $export->write($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend();
    }

    public function upload(UploadImporRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $rows = $this->parseFile($file);

        // Normalisasi kode_desa dengan memisahkan "Kode - Nama" dan validasi format
        $desaList = $this->configApiService->desaConfig();
        if ($desaList instanceof Collection) {
            $desaList = $desaList->toArray();
        }
        $validKodeDesa = [];
        foreach ((array) $desaList as $d) {
            if (is_object($d)) {
                $validKodeDesa[] = (string) ($d->kode_desa ?? $d->kode ?? '');
            } elseif (is_array($d)) {
                $attrs = $d['attributes'] ?? [];
                $validKodeDesa[] = (string) ($d['kode_desa'] ?? $d['kode'] ?? ($attrs['kode_desa'] ?? ($attrs['kode'] ?? '')));
            }
        }
        $validKodeDesa = array_filter($validKodeDesa);

        foreach ($rows as $i => &$row) {
            $kd = (string) ($row['kode_desa'] ?? ($row['desa'] ?? ''));
            $kd = trim($kd);
            if (strpos($kd, ' - ') !== false) {
                $parts = explode(' - ', $kd, 2);
                $kd = trim($parts[0]);
            }
            $row['kode_desa'] = $kd;
            $row['nomor_baris'] = $i + 2;
            $row['_kode_valid'] = $kd !== '' && (empty($validKodeDesa) || in_array($kd, $validKodeDesa, true));
        }
        unset($row);

        Session::put('impor_parsed', [
            'sektor' => $request->input('sektor'),
            'filename' => $file->getClientOriginalName(),
            'rows' => $rows,
        ]);

        return response()->json([
            'success' => true,
            'rows_count' => count($rows),
            'invalid_count' => count(array_filter($rows, fn ($r) => ($r['_kode_valid'] ?? true) === false)),
            'rows' => array_slice($rows, 0, 200),
        ]);
    }

    public function preview(PreviewImporRequest $request): JsonResponse
    {
        $parsed = Session::get('impor_parsed');        
        if (! $parsed) {
            return response()->json(['success' => false, 'message' => 'Tidak ada data hasil unggah.'], 422);
        }

        $baris = [];
        foreach (($parsed['rows'] ?? []) as $idx => $row) {
            $nilai = $row['nilai'] ?? $this->extractNilai($row);
            // Buang key internal yang bukan bagian skema (prefix underscore)
            foreach (array_keys($nilai) as $k) {
                if (strpos((string) $k, '_') === 0) {
                    unset($nilai[$k]);
                }
            }
            $baris[] = [
                'nomor_baris' => $row['nomor_baris'] ?? ($idx + 2),
                'kode_desa' => $row['kode_desa'] ?? ($row['desa'] ?? ''),
                'kode_fasilitas' => $row['kode_fasilitas'] ?? '',
                'nama_fasilitas' => $row['nama_fasilitas'] ?? '',
                'periode' => $row['periode'] ?? date('Y-m'),
                'nilai' => $nilai,
            ];
        }

        $payload = [
            'sektor' => $parsed['sektor'],
            'filename' => $parsed['filename'],
            'force_overwrite' => (bool) $request->input('force_overwrite', false),
            'mode' => $request->input('mode', 'sebagian'),
            'baris' => $baris,
        ];
        $result = $this->dataspasial->imporPratinjau($payload);
        if ($result->isUnavailable()) {
            return response()->json(['success' => false, 'message' => $result->message ?? 'Gagal melakukan pratinjau.'], 422);
        }

        $data = $result->data;
        logger()->error('data', $data);
        $previewData = $this->normalizePreview($data, $result->meta);

        Session::put('impor_preview', $previewData);

        return response()->json(['success' => true, 'preview' => $previewData]);
    }

    public function save(Request $request, string $imporId): JsonResponse
    {
        $result = $this->dataspasial->imporSimpan($imporId, $request->all());

        if ($result->isUnavailable()) {
            return response()->json(['success' => false, 'message' => $result->message ?? 'Gagal menyimpan impor.'], 422);
        }

        Session::forget('impor_parsed');
        Session::forget('impor_preview');

        return response()->json(['success' => true, 'result' => $result->data]);
    }

    /**
     * Normalisasi respons pratinjau API-06 (JSON:API) menjadi struktur flat
     * yang mudah dikonsumsi frontend.
     */
    private function normalizePreview(array $data, array $meta = []): array
    {
        $first = $data[0] ?? $data;
        $attrs = [];
        if (is_array($first) && isset($first['attributes']) && is_array($first['attributes'])) {
            $attrs = $first['attributes'];
        } elseif (is_array($first)) {
            $attrs = $first;
        }

        if (empty($attrs)) {
            $attrs = $meta;
        }

        $ringkasan = $attrs['ringkasan'] ?? [];
        $laporan = $attrs['laporan'] ?? ($attrs['baris'] ?? []);

        $baris = [];
        foreach ((array) $laporan as $item) {
            if (is_object($item)) {
                $item = (array) $item;
            }
            $pesan = $item['pesan'] ?? ($item['alasan'] ?? null);
            if (is_array($pesan)) {
                $pesan = implode('; ', $pesan);
            }
            $baris[] = [
                'nomor_baris' => $item['nomor_baris'] ?? null,
                'kode_desa' => $item['kode_desa'] ?? '',
                'nama_desa' => $item['nama_desa'] ?? '',
                'kode_fasilitas' => $item['kode_fasilitas'] ?? '',
                'nama_fasilitas' => $item['nama_fasilitas'] ?? '',
                'periode' => $item['periode'] ?? '',
                'status' => $item['status'] ?? ($item['keterangan'] ?? ''),
                'pesan' => (string) ($pesan ?? ''),
                'nilai' => $item['nilai'] ?? [],
            ];
        }

        return [
            'impor_id' => $attrs['impor_id'] ?? ($first['impor_id'] ?? ($first['id'] ?? ($attrs['id'] ?? ''))),
            'sektor' => $attrs['sektor'] ?? null,
            'skema_versi' => $attrs['skema_versi'] ?? null,
            'filename' => $attrs['filename'] ?? '',
            'kadaluwarsa_pada' => $attrs['kadaluwarsa_pada'] ?? null,
            'force_overwrite' => $attrs['force_overwrite'] ?? false,
            'mode' => $attrs['mode'] ?? 'sebagian',
            'ringkasan' => [
                'total_baris' => $ringkasan['total_baris'] ?? count($baris),
                'baris_siap' => $ringkasan['baris_siap'] ?? 0,
                'baris_menimpa' => $ringkasan['baris_menimpa'] ?? 0,
                'baris_konflik' => $ringkasan['baris_konflik'] ?? 0,
                'baris_galat' => $ringkasan['baris_galat'] ?? 0,
                'bisa_disimpan' => $ringkasan['bisa_disimpan'] ?? false,
            ],
            'baris' => $baris,
        ];
    }

    private function getDesaListForTemplate(Request $request): array
    {
        $kecamatanId = $request->input('kecamatan');
        if ($kecamatanId) {
            $result = $this->configApiService->desaConfig(['filter[kode_kecamatan]' => $kecamatanId]);
        } else {
            $result = $this->configApiService->desaConfig();
        }

        if ($result instanceof Collection) {
            $result = $result->toArray();
        }

        if (! is_array($result)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($desa) {
            if (is_object($desa)) {
                $kode = (string) ($desa->kode_desa ?? $desa->kode ?? '');
                $nama = (string) ($desa->nama_desa ?? $desa->nama ?? '');

                return $kode === '' ? null : ['kode_desa' => $kode, 'nama_desa' => $nama];
            }
            if (is_array($desa)) {
                $attrs = $desa['attributes'] ?? [];
                $kode = (string) ($desa['kode_desa'] ?? $desa['kode'] ?? ($attrs['kode_desa'] ?? ($attrs['kode'] ?? '')));
                $nama = (string) ($desa['nama_desa'] ?? $desa['nama'] ?? ($attrs['nama_desa'] ?? ($attrs['nama'] ?? '')));

                return $kode === '' ? null : ['kode_desa' => $kode, 'nama_desa' => $nama];
            }

            return null;
        }, $result)));
    }

    private function extractSkemaItems(DataSpasialResult $skema): array
    {
        $data = $skema->data;
        if (empty($data)) {
            return [];
        }
        $first = is_array($data[0]) ? $data[0] : $data;
        $attrs = $first['attributes'] ?? $first;
        if (isset($attrs['isian']) && is_array($attrs['isian'])) {
            return $attrs['isian'];
        }
        if (isset($attrs['fields']) && is_array($attrs['fields'])) {
            return $attrs['fields'];
        }

        return [];
    }

    private function parseFile($file): array
    {
        $rows = [];
        $path = $file->getRealPath();
     
        try {
            $reader = new Reader;
            $reader->open($path);
            foreach ($reader->getSheetIterator() as $sheet) {
                $sheetName = strtolower($sheet->getName());
                if (in_array($sheetName, ['petunjuk', 'daftar desa'], true)) {
                    continue;
                }
                $allRows = [];
                foreach ($sheet->getRowIterator() as $row) {
                    $cells = $row->getCells();
                    $values = [];
                    foreach ($cells as $cell) {
                        $values[] = trim((string) $cell->getValue());
                    }
                    $allRows[] = $values;
                }
                if (empty($allRows)) {
                    continue;
                }
                $headerIndex = -1;
                foreach ($allRows as $i => $r) {
                    $joined = strtolower(implode(' ', $r));
                    if (strpos($joined, 'kode_desa') !== false && strpos($joined, 'kode_fasilitas') !== false) {
                        $headerIndex = $i;
                        break;
                    }
                }
                if ($headerIndex === -1) {
                    foreach ($allRows as $i => $r) {
                        $joined = strtolower(implode(' ', $r));
                        if (strpos($joined, 'kode_desa') !== false) {
                            // look ahead a few rows
                            $headerIndex = $i;
                            break;
                        }
                    }
                }
                if ($headerIndex === -1) {
                    continue;
                }
                $header = array_map(function ($h) {
                    $h = strtolower(trim((string) $h));
                    $h = str_replace([' ', '-'], '_', $h);
                    $h = preg_replace('/[^a-z0-9_]/', '', $h);

                    return $h;
                }, $allRows[$headerIndex]);
                
                for ($i = $headerIndex + 1; $i < count($allRows); $i++) {
                    $data = $allRows[$i];
                    $hasContent = false;
                    foreach ($data as $k => $v) {
                        $val = trim((string) $v);
                        if ($val !== '' && strpos($val, '=') !== 0) {
                            $hasContent = true;
                            break;
                        }
                    }
                    if (! $hasContent) {
                        continue;
                    }
                    $row = [];
                    foreach ($header as $idx => $col) {
                        if ($col === '') {
                            continue;
                        }
                        if (isset($data[$idx])) {
                            $val = trim((string) $data[$idx]);
                            if (strpos($val, '=') === 0) {
                                continue;
                            }
                            $row[$col] = $val;
                        }
                    }
                    if (! empty($row)) {
                if (isset($row['kode_desa']) && strpos($row['kode_desa'], ' - ') !== false) {
                    $parts = explode(' - ', $row['kode_desa'], 2);
                    $row['kode_desa'] = trim($parts[0]);
                }
                if (isset($row['kode_desa']) && strpos($row['kode_desa'], '=') === 0) {
                    continue;
                }
                        if (empty($row['kode_desa']) && isset($row['desa'])) {
                            unset($row['desa']);
                        }
                        $rows[] = $row;
                    }
                }
                break;
            }
            $reader->close();
        } catch (\Throwable $e) {
            // Fallback to previous method if parsing fails
        }

        return $rows;
    }

    private function extractNilai(array $row): array
    {
        $nilai = [];
        $exclude = ['nomor_baris', 'kode_desa', 'desa', 'kode_fasilitas', 'nama_fasilitas', 'periode', 'petunjuk'];
        foreach ($row as $k => $v) {
            if (in_array($k, $exclude, true)) {
                continue;
            }
            if ($v === null || $v === '') {
                continue;
            }
            $nilai[$k] = $v;
        }

        return $nilai;
    }
}
