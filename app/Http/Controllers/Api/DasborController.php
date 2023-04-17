<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Api\Controller;
use App\Http\Repository\DasborRepository;
use App\Http\Transformers\RincianKeluargaTransformer;

class DasborController extends Controller
{
    public function __invoke(DasborRepository $dasbor)
    {
        return response()->json([
            'data' => $dasbor->listDasbor(),
            'message' => 'Berhasil mengambil data dasbor'
        ], Response::HTTP_OK);
    }
}
