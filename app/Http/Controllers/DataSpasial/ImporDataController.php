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
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

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

        $tempFile = $this->templateService->bangun($sektor, $request->validated('kecamatan'), $periode);

        $filename = 'template_impor_'.str_replace([' ', '/'], '_', strtolower($sektor)).'_'.date('YmdHis').'.xlsx';

        return response()->download($tempFile, $filename)->deleteFileAfterSend();
    }

    public function upload(UploadImporRequest $request): JsonResponse
    {
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
        $parsed = Session::get('impor_parsed');
        if (! $parsed) {
            return response()->json(['success' => false, 'message' => 'Tidak ada data hasil unggah.'], 422);
        }

        $forceOverwrite = (bool) $request->input('force_overwrite', false);

        $payload = [
            'sektor' => $parsed['sektor'],
            'filename' => $parsed['filename'],
            'force_overwrite' => $forceOverwrite,
            'mode' => $request->input('mode', 'sebagian'),
            'baris' => $this->berkasService->bangunBarisPratinjau($parsed['rows'] ?? []),
        ];
        if ($forceOverwrite) {
            $payload['alasan_override'] = $request->validated('alasan_override');
        }

        $result = $this->dataspasial->imporPratinjau($payload);

        if ($result->isUnavailable()) {
            return response()->json(['success' => false, 'message' => $result->message ?? 'Gagal melakukan pratinjau.'], 422);
        }

        $previewData = $this->berkasService->normalisasiPratinjau($result->data, $result->meta);

        Session::put('impor_preview', $previewData);

        return response()->json(['success' => true, 'preview' => $previewData]);
    }

    public function save(Request $request, string $imporId): JsonResponse
    {
        $result = $this->dataspasial->imporSimpan($imporId, $request->all());

        if ($result->isUnavailable()) {
            return response()->json(['success' => false, 'message' => $result->message ?? 'Gagal menyimpan impor.'], 422);
        }

        Session::forget(['impor_parsed', 'impor_preview']);

        return response()->json(['success' => true, 'result' => $result->data]);
    }
}
