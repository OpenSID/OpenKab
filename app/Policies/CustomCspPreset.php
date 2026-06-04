<?php

namespace App\Policies;

use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Policy;
use Spatie\Csp\Preset;

class CustomCspPreset implements Preset
{
    public function configure(Policy $policy): void
    {
        $policy
            ->add(Directive::BASE, Keyword::SELF)
            ->add(Directive::OBJECT, Keyword::NONE)
            ->add(Directive::IMG, [Keyword::SELF, 'data:', 'https://tile.openstreetmap.org/', 'blob:'])
            ->add(Directive::STYLE, [
                Keyword::SELF,
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
            ])
            ->addNonce(Directive::STYLE)
            ->add(Directive::SCRIPT, [
                Keyword::SELF,
                'unsafe-eval',
                'https://cdn.datatables.net/2.0.7/js/dataTables.min.js',
                'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
            ])
            ->addNonce(Directive::SCRIPT)
            ->add(Directive::FONT, [
                Keyword::SELF,
                'data:',
                'https://fonts.bunny.net/',
                'https://fonts.gstatic.com/',
                'https://code.ionicframework.com/ionicons/2.0.1/fonts/',
            ])
            ->add(Directive::CONNECT, [
                Keyword::SELF,
                config('app.serverPantau'),
                config('app.databaseGabunganUrl'),
            ])
            ->add(Directive::FRAME, [
                Keyword::SELF,
                'https://www.youtube.com',
                'http://www.youtube.com',
            ]);
    }
}
