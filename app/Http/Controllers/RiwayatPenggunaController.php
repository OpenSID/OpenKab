<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\Controller;
use App\Http\Repository\ActivityRepository;
use App\Http\Transformers\ActivityTransformer;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class RiwayatPenggunaController extends Controller
{
    public function __construct(protected ActivityRepository $activity)
    {
    }
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->fractal($this->activity->listActivity(), new ActivityTransformer, 'activity')->respond();
        }
        return view('riwayat_pengguna.index');
    }

    public function show(Activity $riwayat)
    {
        return view('riwayat_pengguna.detail', compact('riwayat'));
    }
}
