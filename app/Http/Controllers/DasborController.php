<?php

namespace App\Http\Controllers;

use App\Models\Identitas;

class DasborController extends Controller
{
    public function index()
    {
        $identitas = new Identitas();
        $data = $identitas->pengaturan();

        return view('dasbor.index', compact('data'));
    }
}
