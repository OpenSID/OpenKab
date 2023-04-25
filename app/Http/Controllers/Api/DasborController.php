<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\DasborRepository;
use Illuminate\Http\Response;

class DasborController extends Controller
{
    public function __invoke(DasborRepository $dasbor)
    {
        return response()->json([
            'data' => $dasbor->listDasbor(),
            'message' => 'Berhasil mengambil data dasbor',
        ], Response::HTTP_OK);
    }
}
