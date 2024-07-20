<?php

namespace App\Http\Controllers;

use Exception;
use App\Enums\Modul;
use App\Models\Bantuan;
use App\Models\Config;
use App\Models\Penduduk;
use App\Models\Identitas;
use App\Models\Keluarga;
use App\Models\Rtm;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $identitas;
    protected $permission;

    public function __construct() {
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
        $data = [
            'url' => url('/'),
            'versi' => openkab_versi(),
            'kode_kab' => $this->identitas->kode_kabupaten,
            'nama_kab' => $this->identitas->nama_kabupaten,
            'kode_prov' => $this->identitas->kode_provinsi,
            'nama_prov' => $this->identitas->nama_provinsi,
            'nama_aplikasi' => $this->identitas->nama_aplikasi,
            'sebutan_kab' => $this->identitas->sebutan_kab,
            'jumlah_desa' => Config::count(),
            'jumlah_penduduk' => Penduduk::status()->count(),
            'jumlah_keluarga' => Keluarga::status()->count(),
            'jumlah_rtm' => Rtm::status()->count(),
            'jumlah_bantuan' => Bantuan::count(),
        ];

        try {
            $response = Http::withHeaders([
                'token' => config('app.tokenPantau')
            ])->post($host_pantau . '/index.php/api/track/openkab?token=' . config('app.tokenPantau'), $data);
            cache()->put('track', date('Y m d'), 60 * 60 * 24);
            return;
        } catch (Exception $e) {
            Log::notice($e);
            return;
        }
    }
}
