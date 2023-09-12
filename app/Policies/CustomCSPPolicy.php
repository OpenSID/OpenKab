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
    private $excludeRoute = [];

    // contoh generate sha256 nama function
    // echo -n 'showBatalBayarModal(this)' | openssl sha256 -binary | openssl base64
    public function configure()
    {
        parent::configure();

        $this->addDirective(Directive::IMG, ['data:;'])
        ->addDirective(Directive::STYLE, [
            // 'unsafe-inline',
            'https://fonts.googleapis.com/',
            'https://fonts.bunny.net/',
            'sha256-z7zcnw/4WalZqx+PrNaRnoeLz/G9WXuFqV1WCJ129sg=',
            'sha256-47DEQpj8HBSa+/TImW+5JCeuQeRkm5NMpJWZG3hSuFU=',
            'sha256-hIQQk/yoM15mwdqWhaRQ/qiDh22AXD54o7w5fUsss+w=',
            // 'sha256-TsVIN7SQps98aly1gmseL0Zta8mas2ihwfacnZ8U8oc=',
            // debugbar
            // 'sha256-mT0LzWo7jBuKW1L9vBycTgNsK1MOZ7UYxTECKp064I0='
        ])->addDirective(Directive::SCRIPT, [
            'unsafe-eval',
            // debugbar
            // 'sha256-FhudaH+D1DhcOfC3dGgEcvkNWiujsnNBXvpOnYT+asw=',
            // 'sha256-oBnKxExdoFf5vSDBcXrSGKL8XqKENXywdyGOWTtcDWg=',
            // 'sha256-nPBmwyxKQjiS904cW3n9Xh3ihVOPuWLLO11gRFWnnRM=',
            // 'sha256-H1en7E/ZhgBswieGw1Zri1PWW+/m/QMDwwBbkXq8xTY=',
            // 'sha256-4EUx5PEnUfDZ2Ru+KoJ9J5B9MwlceuHsfGscJ/FMRv8=',
            // 'sha256-47DEQpj8HBSa+/TImW+5JCeuQeRkm5NMpJWZG3hSuFU=',
            // 'sha256-TsVIN7SQps98aly1gmseL0Zta8mas2ihwfacnZ8U8oc='
        ])->addDirective(Directive::FONT, [
            Keyword::SELF,
            'data:',
            'https://fonts.bunny.net/',
            'https://fonts.gstatic.com/',
        ])->addDirective(Directive::CONNECT, [
            config('app.serverPantau'),
        ]);
    }

    public function shouldBeApplied(Request $request, Response $response): bool
    {
        $currentRoute = Route::getCurrentRoute()->getName();
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
