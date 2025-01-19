<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Repository\PlanRepository;
use App\Http\Transformers\PlanTransformer;
use App\Models\Lokasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class PlanController extends Controller
{
    public function __construct(protected PlanRepository $plan)
    {
    }

    public function index()
    {
        $planData = $this->plan->listPlan();

        return fractal($planData, new PlanTransformer())
            ->addMeta(['message' => 'daftar tipe lokasi'])
            ->respond();
    }

    public function getListCoordinate($parrent, $id)
    {
        $coordinate = Lokasi::where('id', $id)->get();

        return $coordinate->toJson();
    }
}
