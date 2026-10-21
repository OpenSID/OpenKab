<?php

declare(strict_types=1);

namespace App\Http\Controllers\DataSpasial;

use App\Services\DataSpasialResult;
use Illuminate\Http\Request;

/**
 * Menu Monitoring.
 *
 * Kerangka menu pada fase fondasi ini. Daftar entri audit lengkap menjadi
 * lingkup KAB-08.
 */
class MonitoringController extends DataSpasialController
{
    protected function pageTitle(): string
    {
        return 'Monitoring';
    }

    protected function viewName(): string
    {
        return 'dataspasial.monitoring.index';
    }

    protected function fetch(Request $request): DataSpasialResult
    {
        return $this->dataspasial->adopsi($request->only([
            'sektor_id',
            'skema_versi_id',
            'status_adopsi',
            'kode_kabupaten',
            'kode_kecamatan',
            'kode_desa',
        ]));
    }
}
