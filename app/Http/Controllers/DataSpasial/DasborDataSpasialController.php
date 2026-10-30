<?php

declare(strict_types=1);

namespace App\Http\Controllers\DataSpasial;

use App\Services\DataSpasialResult;
use Illuminate\Http\Request;

/**
 * Menu Dasbor DataSpasial.
 *
 * Kerangka menu pada fase fondasi ini. Agregat dan visualisasi penuh
 * menjadi lingkup KAB-08 (OpenSID/OpenKab#1304).
 */
class DasborDataSpasialController extends DataSpasialController
{
    protected function pageTitle(): string
    {
        return 'Dasbor DataSpasial';
    }

    protected function viewName(): string
    {
        return 'dataspasial.dasbor.index';
    }

    protected function fetch(Request $request): DataSpasialResult
    {
        return $this->dataspasial->ringkasan($request->only([
            'sektor_id',
            'periode',
            'kode_kabupaten',
            'kode_kecamatan',
            'kode_desa',
        ]));
    }
}
