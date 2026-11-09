<?php

declare(strict_types=1);

namespace App\Http\Controllers\DataSpasial;

use App\Services\DataSpasialResult;
use Illuminate\Http\Request;

/**
 * Menu Skema DataSpasial (Admin Skema).
 *
 * Kerangka menu pada fase fondasi ini. Editor sektor/pemetaan skema menjadi
 * lingkup KAB-02 (OpenSID/OpenKab#1297) dan dibangun di atas service ini
 * tanpa mengubah kontraknya (FR-018).
 */
class SkemaDataSpasialController extends DataSpasialController
{
    protected function pageTitle(): string
    {
        return 'Skema DataSpasial';
    }

    protected function viewName(): string
    {
        return 'dataspasial.skema.index';
    }

    protected function fetch(Request $request): DataSpasialResult
    {
        $result = $this->dataspasial->listSkema($request->only([
            'sektor_id',
            'status',
            'page[number]',
            'page[size]',
            'sort',
        ]));

        return $result;
    }
}
