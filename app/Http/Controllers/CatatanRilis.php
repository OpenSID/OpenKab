<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CatatanRilis extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $file = file_get_contents(base_path('catatan_rilis.md'));
        return '<div class="justify-content">'.\Str::markdown($file).'</div>';
    }
}
