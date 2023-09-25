<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CatatanRilis extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $file = file_get_contents(base_path('catatan_rilis.md'));

        return str_replace('<a', '<a class="text-primary"', Str::markdown($file));
    }
}
