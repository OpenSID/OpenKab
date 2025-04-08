<?php

namespace App\Policies;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Policies\Basic;
use Symfony\Component\HttpFoundation\Response;

class CustomCSPPolicy extends Basic
{
    // exclude karena livewire tidak jalan ketika csp enable
    private $excludeRoute = ['fm.tinymce5', 'fm.initialize', 'fm.content', 'fm.tree', 'cms.statistic.summary', 'presisi.index', 'presisi.kependudukan'];

    private $hasTinyMCE = ['articles.create', 'articles.edit'];

    public function configure()
    {
        parent::configure();
        $currentRoute = Route::getCurrentRoute()->getName();
        if (in_array($currentRoute, $this->hasTinyMCE)) {
            $this->addDirective(Directive::IMG, ['blob:'])
                ->addDirective(Directive::STYLE, ['unsafe-inline']);
        }
        $this->addDirective(Directive::IMG, ['data:', 'https://tile.openstreetmap.org/'])
        ->addDirective(Directive::STYLE, [
            // 'unsafe-inline',
            'https://fonts.googleapis.com/',
            'https://fonts.bunny.net/',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css',
            'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
            'https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css',
            'https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.min.css',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
            'sha256-z7zcnw/4WalZqx+PrNaRnoeLz/G9WXuFqV1WCJ129sg=',
            'sha256-47DEQpj8HBSa+/TImW+5JCeuQeRkm5NMpJWZG3hSuFU=',
            'sha256-hIQQk/yoM15mwdqWhaRQ/qiDh22AXD54o7w5fUsss+w=',
            'sha256-wXDqcLlNCfwz7CniAXnDuBVLmG9xeJRAiHkMrCetfeQ=',
        ])->addDirective(Directive::SCRIPT, [
            // karena banyak yang menggunakan alpine js
            'unsafe-eval',
            'https://cdn.datatables.net/2.0.7/js/dataTables.min.js',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
        ])->addDirective(Directive::FONT, [
            Keyword::SELF,
            'data:',
            'https://fonts.bunny.net/',
            'https://fonts.gstatic.com/',
            'https://code.ionicframework.com/ionicons/2.0.1/fonts/',
        ])->addDirective(Directive::CONNECT, [
            config('app.serverPantau'),
            config('app.databaseGabunganUrl'),
        ]);
    }

    public function shouldBeApplied(Request $request, Response $response): bool
    {
        $currentRoute = Route::getCurrentRoute()?->getName() ?? '';

        if (in_array($currentRoute, $this->excludeRoute)) {
            config(['csp.enabled' => false]);
        }

        // jika mode debug aktif maka disable CSP
        if (env('APP_DEBUG')) {
            config(['csp.enabled' => false]);
        }

        return config('csp.enabled');
    }
}
