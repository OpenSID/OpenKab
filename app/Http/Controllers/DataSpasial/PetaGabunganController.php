<?php

declare(strict_types=1);

namespace App\Http\Controllers\DataSpasial;

use App\Services\DataSpasialResult;
use Illuminate\Http\Request;

/**
 * Menu Peta Gabungan.
 *
 * Kerangka menu pada fase fondasi ini. Peta geospasial penuh menjadi lingkup
 * KAB-04 (OpenSID/OpenKab#1299).
 */
class PetaGabunganController extends DataSpasialController
{
    protected function pageTitle(): string
    {
        return 'Peta Gabungan';
    }

    protected function viewName(): string
    {
        return 'dataspasial.peta.index';
    }

    protected function fetch(Request $request): DataSpasialResult
    {
        return $this->dataspasial->geojsonGabungan($request->only([
            'sektor_id',
            'kode_kabupaten',
            'kode_kecamatan',
            'kode_desa',
        ]));
    }
}
