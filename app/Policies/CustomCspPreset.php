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
                Keyword::UNSAFE_INLINE,
                'https://fonts.googleapis.com/',
                'https://fonts.bunny.net/',
                'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css',
                'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css',
                'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
                'https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css',
                'https://cdn.datatables.net/2.0.7/css/dataTables.dataTables.min.css',
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
            ])
            ->add(Directive::SCRIPT, [
                Keyword::SELF,
                Keyword::UNSAFE_EVAL,
                'https://cdn.datatables.net/2.0.7/js/dataTables.min.js',
                'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
                'https://cdn.jsdelivr.net/npm/sweetalert2@11',
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
