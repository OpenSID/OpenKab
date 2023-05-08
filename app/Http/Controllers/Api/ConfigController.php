<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Repository\ConfigRepository;
use App\Http\Transformers\ConfigTransformer;

class ConfigController extends Controller
{
    public function __construct(protected ConfigRepository $config)
    {
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->fractal($this->config->desa(), new ConfigTransformer(), 'config')->respond();
    }
}
