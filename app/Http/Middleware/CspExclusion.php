<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class CspExclusion
{
    private array $excludeRoute = ['fm.tinymce5', 'fm.initialize', 'fm.content', 'fm.tree', 'cms.statistic.summary', 'presisi.index', 'presisi.kependudukan', 'laporan-bulanan.index', 'laporan-bulanan.filter', 'laporan-bulanan.export-excel', 'laporan-bulanan.detail-penduduk', 'laporan-bulanan.export-excel-detail'];

    public function handle(Request $request, Closure $next): Response
    {
        $currentRoute = Route::getCurrentRoute()?->getName() ?? '';

        if (in_array($currentRoute, $this->excludeRoute)) {
            config(['csp.enabled' => false]);
        }

        return $next($request);
    }
}
