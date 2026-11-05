<?php

declare(strict_types=1);

namespace App\Http\Controllers\DataSpasial;

use App\Services\DataSpasialResult;
use Illuminate\Http\Request;

/**
 * Menu Impor Data (Operator Impor).
 *
 * Kerangka menu pada fase fondasi ini. Alur impor lengkap (template, validasi
 * per baris, ringkasan ditolak/konflik) menjadi lingkup KAB-04.
 */
class ImporDataController extends DataSpasialController
{
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
        return $this->dataspasial->listFasilitas($request->only([
            'sektor_id',
            'skema_versi_id',
            'kode_desa',
        ]));
    }
}
