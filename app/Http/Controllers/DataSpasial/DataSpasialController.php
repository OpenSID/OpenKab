<?php

declare(strict_types=1);

namespace App\Http\Controllers\DataSpasial;

use App\Http\Controllers\Controller;
use App\Services\DataSpasialApiService;
use App\Services\DataSpasialResult;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Kerangka dasar untuk kelima halaman menu DataSpasial.
 *
 * Aturan yang berlaku untuk semua halaman:
 * - HANYA membaca lewat `DataSpasialApiService` (FR-002/SC-009);
 * - controller TIDAK melempar exception HTTP ke view — kegagalan API sudah
 *   diterjemahkan menjadi `DataSpasialResult` berstatus `unavailable`
 *   (FR-014);
 * - respons selalu HTTP 200 agar kerangka halaman & navigasi tetap utuh.
 */
abstract class DataSpasialController extends Controller
{
    public function __construct(protected DataSpasialApiService $dataspasial) {}

    /**
     * Nama tampilan halaman untuk judul & breadcrumb.
     */
    abstract protected function pageTitle(): string;

    /**
     * View kerangka halaman.
     */
    abstract protected function viewName(): string;

    /**
     * Ambil data dari service. Method ini tidak pernah melempar exception.
     *
     * @param  array<string, mixed>  $filters
     */
    abstract protected function fetch(Request $request): DataSpasialResult;

    public function index(Request $request): View
    {
        return view($this->viewName(), [
            'title' => $this->pageTitle(),
            'result' => $this->fetch($request),
        ]);
    }
}
