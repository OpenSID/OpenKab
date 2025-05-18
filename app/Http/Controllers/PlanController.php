<?php

namespace App\Http\Controllers;

use App\Enums\AccessTypeEnum;
use App\Models\Lokasi;
use App\Models\Point;

class PlanController extends Controller
{
    private int $tip = 3;

    public function index($parent = 0)
    {
        $data = ['tip' => $this->tip, 'parent' => $parent];
        $data['status'] = [
            AccessTypeEnum::LOCK->value() => 'Aktif',
            AccessTypeEnum::UNLOCK->value() => 'Tidak Aktif',
        ];
        $data['point'] = Point::root()->with(['children'])->where('sumber', 'OpenKab')->get();

        return view('peta.lokasi.index', $data);
    }

    public function ajax_lokasi_maps($parent, int $id)
    {
        $data['lokasi'] = Lokasi::with('point')->findOrFail($id)->toArray();
        $data['parent'] = $parent;
        $data['id'] = $id;

        return view('peta.lokasi.maps', $data);
    }

    public function show_ajax_lokasi_maps($parent, int $id)
    {
        $data['lokasi'] = Lokasi::with('point')->findOrFail($id)->toArray();
        $data['parent'] = $parent;
        $data['id'] = $id;

        return view('peta.lokasi.template_maps', $data);
    }
}
