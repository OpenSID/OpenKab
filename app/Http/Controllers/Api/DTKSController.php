<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\DTKSRepository;
use App\Http\Transformers\DTKSTransformer;

class DTKSController extends Controller
{
    public function __construct(protected DTKSRepository $dtks)
    {
    }

    public function __invoke()
    {
        return $this->fractal($this->dtks->index(), new DTKSTransformer, 'dtks')->respond();
    }
}
