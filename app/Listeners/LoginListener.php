<?php

namespace App\Listeners;

use App\Models\Setting;
use Exception;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LoginListener
{
    public Request $request;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\authentication-log.events.login  $event
     *
     * @return void
     */
    public function handle(Login $event)
    {
        $presisiStatus = false;
        try {
            $url = config('app.databaseGabunganUrl').'/api/v1/setting-modul';
            $setting = Setting::where('key', 'database_gabungan_api_key')->first();
            $settingModul = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$setting->value ?? '',
            ])->get($url, [
                'filter[slug]' => 'data-presisi',
                'page[size]' => 1,
            ])->throw()
                ->json();
            
            $prodeskel = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$setting->value ?? '',
            ])->get($url, [
                'filter[slug]' => 'prodeskel',
                'page[size]' => 1,
            ])->throw()
                ->json();

            // Assuming the response contains a 'data' key with the status
            $presisiStatus = count($settingModul['data']) > 0 ? true : false;
            $prodeskelStatus = count($prodeskel['data']) > 0 ? true : false;
            
        } catch (Exception $e) {
            Log::error('Error fetching setting-modul: '.$e->getMessage());
        }
        session(['presisi_enabled' => $presisiStatus, 'prodeskel_enabled' => $prodeskelStatus]);

        activity('authentication-log')->event('login')->withProperties($this->request)->log('Login');
    }
}
