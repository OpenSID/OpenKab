<?php

namespace App\Http\Controllers;

use App\Enums\Modul;
use App\Models\Identitas;
use App\Models\Setting;
use App\Services\SummaryWebsiteService;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $identitas;

    protected $permission;

    protected $settings;

    public function __construct()
    {
        $this->settings = Setting::pluck('value', 'key');

        $this->identitas = Identitas::first();
        if ($this->identitas) {
            $this->kirimTrack();
        }
    }

    protected function generateListPermission()
    {
        $listPermission = [];
        if ($this->permission) {
            foreach (Modul::permision as $item) {
                $listPermission['can'.$item] = auth()->user()->can($this->permission.'-'.$item) ?? 0;
            }
        }

        return $listPermission;
    }

    protected function kirimTrack()
    {
        if (config('app.demo') == true) { // jika posisi demo, matikan tracking
            return;
        }

        if (cache()->get('track') != null && cache()->get('track') == date('Y m d')) {
            return;
        }

        $host_pantau = config('app.serverPantau');
        $summary = (new SummaryWebsiteService)->getSummaryData();
        $dataKirim = $summary['categoriesItems'] ?? [
            'desa' => ['value' => 0],
            'penduduk' => ['value' => 0],
            'keluarga' => ['value' => 0],
            'rtm' => ['value' => 0],
            'bantuan' => ['value' => 0],

        ];
        $data = [
            'url' => url('/'),
            'versi' => openkab_versi(),
            'kode_kab' => $this->identitas->kode_kabupaten,
            'nama_kab' => $this->identitas->nama_kabupaten,
            'kode_prov' => $this->identitas->kode_provinsi,
            'nama_prov' => $this->identitas->nama_provinsi,
            'nama_aplikasi' => $this->identitas->nama_aplikasi,
            'sebutan_kab' => $this->identitas->sebutan_kab,
            'jumlah_desa' => str_replace('.', '', $dataKirim['desa']['value']),
            'jumlah_penduduk' => str_replace('.', '', $dataKirim['penduduk']['value']),
            'jumlah_keluarga' => str_replace('.', '', $dataKirim['keluarga']['value']),
            'jumlah_rtm' => str_replace('.', '', $dataKirim['rtm']['value'] ?? 0),
            'jumlah_bantuan' => str_replace('.', '', $dataKirim['bantuan']['value']),
        ];

        try {
            $response = Http::withHeaders([
                'token' => config('app.tokenPantau'),
            ])->post($host_pantau.'/index.php/api/track/openkab?token='.config('app.tokenPantau'), $data);
            cache()->put('track', date('Y m d'), 60 * 60 * 24);

            return;
        } catch (Exception $e) {
            Log::notice($e);

            return;
        }
    }

    protected function isOpenKabSiapPakai()
    {
        return ($this->settings['OpenKab_SiapPakai'] ?? null) === '1';
    }
}
