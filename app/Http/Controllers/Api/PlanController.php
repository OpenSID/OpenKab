<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Repository\PlanRepository;
use App\Http\Transformers\PlanTransformer;
use App\Models\Lokasi;

class PlanController extends Controller
{
    protected $kabupaten;

    protected $kecamatan;

    protected $desa;

    protected $kategori;

    protected $kata_kunci;

    public function __construct(protected PlanRepository $plan)
    {
        $this->kecamatan = request()->input('filter')['kecamatan'] ?? null;
        $this->kabupaten = request()->input('filter')['kabupaten'] ?? null;
        $this->desa = request()->input('filter')['desa'] ?? null;
        $this->kategori = request()->input('filter')['kategori'] ?? null;
        $this->kata_kunci = request()->input('filter')['kata_kunci'] ?? null;
    }

    public function index()
    {
        $planData = $this->plan->listPlan();

        return fractal($planData, new PlanTransformer())
            ->addMeta(['message' => 'daftar tipe lokasi'])
            ->respond();
    }

    public function getListCoordinate($parent = null, $id = null)
    {
        // Mulai query dasar
        $coordinate = Lokasi::query();

        // Filter berdasarkan parent
        if ($parent) {
            $coordinate->whereHas('point', function ($query) use ($parent) {
                $query->where('parrent', $parent); // Pastikan kolom bernama 'parent'
                $query->where('sumber', 'OpenKab'); // Pastikan kolom bernama 'parent'
            })->with('point');
        }
        // Filter berdasarkan ID
        elseif ($id) {
            $coordinate->where('id', $id)->with('point');
        }
        // Jika tidak ada filter parent atau ID, muat relasi
        else {
            $coordinate->with(['point', 'config']);
        }

        // Filter tambahan berdasarkan relasi 'config'
        $coordinate->whereHas('config', function ($query) {
            if (! empty($this->kabupaten)) {
                $query->where('kode_kabupaten', $this->kabupaten);
            }
            if (! empty($this->kecamatan)) {
                $query->where('kode_kecamatan', $this->kecamatan);
            }
            if (! empty($this->desa)) {
                $query->where('kode_desa', $this->desa);
            }
        });

        $coordinate->whereHas('point', function ($query) {
            $query->where('sumber', 'OpenKab'); // Pastikan kolom bernama 'parent'

            if (! empty($this->kategori)) {
                $query->where('parrent', $this->kategori)->orWhere('id', $this->kategori);
            }
        });

        if (! empty($this->kata_kunci)) {
            $coordinate->where(function ($query) {
                $query->where('nama', 'LIKE', '%'.$this->kata_kunci.'%')
                      ->orWhere('desk', 'LIKE', '%'.$this->kata_kunci.'%');
            });
        }

        // Eksekusi query
        $result = $coordinate->get();

        // Kembalikan hasil dalam format JSON
        return response()->json($result);
    }
}
