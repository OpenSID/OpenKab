<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BantuanController extends Controller
{
    public function index()
    {
        return view('bantuan.index');
    }

    public function show($id)
    {
        return view('bantuan.show', compact('id'));
    }
}
