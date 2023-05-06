<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\PengaturanRepository;
use App\Http\Transformers\PengaturanTransformer;
use App\Models\Pengaturan;
use Symfony\Component\HttpFoundation\Response;

class PengaturanController extends Controller
{
    public function __construct(protected PengaturanRepository $pengaturan)
    {
    }

    public function index()
    {
        return $this->fractal($this->pengaturan->listPengaturan(), new PengaturanTransformer(), 'daftar pengaturan')->respond();
    }
}
