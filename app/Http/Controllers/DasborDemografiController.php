<?php

namespace App\Http\Controllers;

use App\Models\Enums\StatistikPendudukEnum;

class DasborDemografiController extends Controller
{
    public function index()
    {
        $statistikUrl = 'api/v1/statistik/penduduk';
        $chartItems = [
            ['key' => StatistikPendudukEnum::RENTANG_UMUR['slug'], 'text' => StatistikPendudukEnum::RENTANG_UMUR['label']],
            ['key' => StatistikPendudukEnum::STATUS_PERKAWINAN['slug'], 'text' => StatistikPendudukEnum::STATUS_PERKAWINAN['label']],
            ['key' => StatistikPendudukEnum::PENDIDIKAN_KK['slug'], 'text' => StatistikPendudukEnum::PENDIDIKAN_KK['label']],
            ['key' => StatistikPendudukEnum::GOLONGAN_DARAH['slug'], 'text' => StatistikPendudukEnum::GOLONGAN_DARAH['label']],
            ['key' => StatistikPendudukEnum::PENYAKIT_MENAHUN['slug'], 'text' => StatistikPendudukEnum::PENYAKIT_MENAHUN['label']],
            ['key' => StatistikPendudukEnum::AGAMA['slug'], 'text' => StatistikPendudukEnum::AGAMA['label']],
            ['key' => StatistikPendudukEnum::JENIS_KELAMIN['slug'], 'text' => StatistikPendudukEnum::JENIS_KELAMIN['label']],
            ['key' => 'suku', 'text' => StatistikPendudukEnum::SUKU_ETNIS['label']],
            ['key' => StatistikPendudukEnum::PENYANDANG_CACAT['slug'], 'text' => StatistikPendudukEnum::PENYANDANG_CACAT['label']],
        ];
        return view('demografi.index', compact('chartItems', 'statistikUrl'));
    }
}
