<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BaseApiService
{
    protected $settings;

    protected $useDatabaseGabungan;

    protected $header;

    protected $baseUrl;

    protected $kodeKecamatan;

    public function __construct()
    {
        $this->settings = Setting::whereIn('key', ['database_gabungan_api_key', ''])->pluck('value', 'key');
        $this->header = [
            'Accept' => 'application/ld+json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$this->settings['database_gabungan_api_key'] ?? '',
        ];
        $this->baseUrl = config('app.databaseGabunganUrl');
        $this->kodeKecamatan = str_replace('.', '', config('profil.kecamatan_id'));
    }

    protected function buildCacheKey(string $prefix, array $filters = []): string
    {
        if (empty($filters)) {
            return $prefix;
        }

        return $prefix.'_'.md5(json_encode($filters));
    }

    /**
     * General API Call Method.
     */
    protected function apiRequest(string $endpoint, array $params = [])
    {
        try {
            // Buat permintaan API dengan Header dan Parameter
            $response = Http::withHeaders($this->header)->get($this->baseUrl.$endpoint, $params);
            session()->forget('error_api');

            // Return JSON hasil
            return $response->json('data') ?? [];
        } catch (\Exception $e) {
            session()->flash('error_api', 'Gagal mendapatkan data'.$e->getMessage());
            Log::error('Failed get data in '.__FILE__.' function '.__METHOD__.' '.$e->getMessage());
        }

        return [];
    }
}
