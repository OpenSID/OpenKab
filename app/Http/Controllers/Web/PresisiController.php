<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;

class PresisiController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        \Log::error(config('adminlte.menu'));
        Config::set('adminlte.menu',[]);
        \Log::error(config('adminlte.menu'));
        return view('presisi.index');
    }
}
