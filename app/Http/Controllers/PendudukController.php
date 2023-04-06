<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class PendudukController extends Controller
{
    public function index()
    {
        return view('penduduk.index');
    }

    public function show(Request $request, $id)
    {
        $penduduk = Http::withHeaders([
                'Accept' => 'application/json',
                'Cookie' => $request->header('cookie'),
                'Referer' => $request->header('referer'),
            ])
            ->get(config('app.url') . '/api/v1/penduduk', [
                'filter[id]' => $id,
            ])
            ->collect('data.0.attributes');
        
        if (empty($penduduk)) {
            abort(404);
        }

        return view('penduduk.detail', compact('penduduk'));
    }
}
