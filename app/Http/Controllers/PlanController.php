<?php

namespace App\Http\Controllers;

use App\Enums\AccessTypeEnum;
use App\Models\Lokasi;
use App\Models\Point;
use App\Services\PemetaanService;

class PlanController extends Controller
{
    private int $tip = 3;

    public function __construct(private PemetaanService $peta){}

    public function index($parent = 0)
    {
        $data = ['tip' => $this->tip, 'parent' => $parent];
        $data['status'] = [
            AccessTypeEnum::LOCK->value() => 'Aktif',
            AccessTypeEnum::UNLOCK->value() => 'Tidak Aktif',
        ];
        // $data['point'] = Point::root()->with(['children'])->where('sumber', 'OpenKab')->get();

        $data['point'] = $this->peta->getAllPoint();

        return view('peta.lokasi.index', $data);
    }

    public function ajax_lokasi_maps($parent, int $id)
    {
        // $data['lokasi'] = Lokasi::with('point')->findOrFail($id)->toArray();

        $data['lokasi'] = $this->peta->getAllPlan([
            'filter[id]' => $id
        ]);
        
        $data['parent'] = $parent;
        $data['id'] = $id;

        return view('peta.lokasi.maps', $data);
    }

    public function show_ajax_lokasi_maps($parent, int $id)
    {
        $data['lokasi'] = $this->peta->getAllPlan([
            'filter[id]' => $id
        ]);
        $data['parent'] = $parent;
        $data['id'] = $id;

        return view('peta.lokasi.template_maps', $data);
    }
}
