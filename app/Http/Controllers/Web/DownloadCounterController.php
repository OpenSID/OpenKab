<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CMS\Download;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DownloadCounterController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Download $download)
    {
        if ($download->url) {
            $download->counter?->increment('total');

            return Storage::disk('public')->download($download->url);
        }

        return 'File tidak ditemukan';
    }
}
