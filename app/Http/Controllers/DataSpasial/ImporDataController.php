<?php

declare(strict_types=1);

namespace App\Http\Controllers\DataSpasial;

use App\Http\Requests\DataSpasial\DownloadTemplateRequest;
use App\Http\Requests\DataSpasial\PreviewImporRequest;
use App\Http\Requests\DataSpasial\UploadImporRequest;
use App\Services\ConfigApiService;
use App\Services\DataSpasialApiService;
use App\Services\DataSpasialResult;
use App\Services\ImporBerkasService;
use App\Services\ImporTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use RuntimeException;

class ImporDataController extends DataSpasialController
{
    public function __construct(
        DataSpasialApiService $dataspasial,
        protected ConfigApiService $configApiService,
        protected ImporTemplateService $templateService,
        protected ImporBerkasService $berkasService,
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
        return view($this->viewName(), [
            'title' => $this->pageTitle(),
            'sektors' => $this->dataspasial->listSektor(),
            'kecamatans' => $this->configApiService->kecamatan(),
        ]);
    }

    public function downloadTemplate(DownloadTemplateRequest $request)
    {
        $sektor = $request->validated('sektor');
        $periode = $request->validated('periode') ?? date('Y');

        try {
            $tempFile = $this->templateService->bangun($sektor, $request->validated('kecamatan'), $periode);
        } catch (RuntimeException|ConnectionException $exception) {
            Log::warning('DataSpasial: daftar lokasi untuk templat tidak tersedia', ['message' => $exception->getMessage()]);

            return back()->with('error', 'Templat tidak dapat dibuat karena daftar lokasi tidak tersedia. Silakan coba lagi.');
        }

        $filename = 'template_impor_'.str_replace([' ', '/'], '_', strtolower($sektor)).'_'.date('YmdHis').'.xlsx';

        return response()->download($tempFile, $filename)->deleteFileAfterSend();
    }

    public function upload(UploadImporRequest $request): JsonResponse
    {
        Session::forget(['impor_parsed', 'impor_preview']);
        $file = $request->file('file');

        $rows = $this->berkasService->baca($file->getRealPath());
        $rows = $this->berkasService->normalisasiKodeDesa($rows, $this->configApiService->desaConfig());

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
        Session::forget('impor_preview');
        $parsed = Session::get('impor_parsed');
        if (! $parsed) {
            return response()->json(['success' => false, 'message' => 'Tidak ada data hasil unggah.'], 422);
        }

        $forceOverwrite = (bool) $request->input('force_overwrite', false);
        $baris = $this->berkasService->bangunBarisPratinjau($parsed['rows'] ?? []);
        if ($baris === []) {
            return response()->json(['success' => false, 'message' => 'Tidak ada baris data untuk dipratinjau.'], 422);
        }

        foreach ($baris as &$row) {
            $id = filter_var($row['lokasi_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($id === false) {
                return response()->json(['success' => false, 'message' => 'Baris '.$row['nomor_baris'].': pilih lokasi yang valid pada kolom lokasi_id.'], 422);
            }
            $row['lokasi_id'] = $id;
        }
        unset($row);

        try {
            $desa = array_map(static fn (array $row): array => ['kode_desa' => (string) $row['kode_desa'], 'nama_desa' => ''], $baris);
            $lokasi = array_column($this->templateService->daftarLokasi($desa), null, 'id');
        } catch (RuntimeException|ConnectionException $exception) {
            Log::warning('DataSpasial: validasi lokasi impor tidak tersedia', ['message' => $exception->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Daftar lokasi tidak tersedia. Silakan coba lagi.'], 503);
        }
        foreach ($baris as $row) {
            $pilihan = $lokasi[$row['lokasi_id']] ?? null;
            if ($pilihan === null || $pilihan['kode_desa'] !== (string) $row['kode_desa']) {
                return response()->json(['success' => false, 'message' => 'Baris '.$row['nomor_baris'].': lokasi tidak ditemukan, tidak memiliki koordinat, atau tidak berada di desa yang dipilih.'], 422);
            }
        }

        $payload = [
            'sektor' => $parsed['sektor'],
            'filename' => $parsed['filename'],
            'force_overwrite' => $forceOverwrite,
            'mode' => $request->input('mode', 'sebagian'),
            'baris' => $baris,
        ];
        if ($forceOverwrite) {
            $payload['alasan_override'] = $request->validated('alasan_override');
        }

        $result = $this->dataspasial->imporPratinjau($payload);

        if ($result->isUnavailable()) {
            return response()->json(['success' => false, 'message' => $result->message ?? 'Gagal melakukan pratinjau.'], 422);
        }

        $previewData = $this->berkasService->normalisasiPratinjau($result->data, $result->meta);

        if (count($previewData['baris']) !== count($baris)) {
            return response()->json(['success' => false, 'message' => 'API belum mengonfirmasi lokasi untuk seluruh baris. Perbarui API DataSpasial sebelum menyimpan impor.'], 503);
        }
        foreach ($baris as $index => $row) {
            $confirmed = $previewData['baris'][$index];
            if ((string) ($confirmed['nomor_baris'] ?? '') !== (string) $row['nomor_baris']
                || (string) ($confirmed['lokasi_id'] ?? '') !== (string) $row['lokasi_id']) {
                return response()->json(['success' => false, 'message' => 'API belum mengonfirmasi lokasi untuk seluruh baris. Perbarui API DataSpasial sebelum menyimpan impor.'], 503);
            }
        }

        Session::put('impor_preview', $previewData);

        return response()->json(['success' => true, 'preview' => $previewData]);
    }

    public function save(Request $request, string $imporId): JsonResponse
    {
        $preview = Session::get('impor_preview');
        if (! is_array($preview) || ($preview['impor_id'] ?? null) !== $imporId
            || ! ($preview['ringkasan']['bisa_disimpan'] ?? false)) {
            return response()->json(['success' => false, 'message' => 'Lakukan pratinjau lokasi yang valid sebelum menyimpan impor.'], 422);
        }
        $result = $this->dataspasial->imporSimpan($imporId, $request->all());

        if ($result->isUnavailable()) {
            return response()->json(['success' => false, 'message' => $result->message ?? 'Gagal menyimpan impor.'], 422);
        }

        Session::forget(['impor_parsed', 'impor_preview']);

        return response()->json(['success' => true, 'result' => $result->data]);
    }
}
