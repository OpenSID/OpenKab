<?php

namespace App\Http\Controllers\Api;

use League\Fractal\Manager;
use Spatie\Fractal\Fractal;
use App\Http\Repository\RtmRepository;
use League\Fractal\Resource\Collection;
use App\Http\Controllers\Api\Controller;
use Spatie\Fractalistic\ArraySerializer;
use App\Http\Transformers\RtmTransformer;
use App\Http\Transformers\BantuanTransformer;
use App\Http\Transformers\GrafikRtmTransformer;

class RtmController extends Controller
{
    public function __construct(protected RtmRepository $rtm)
    {
    }

    public function index()
    {
        // return $this->fractal($this->rtm->listRtm(), new RtmTransformer(), 'rtm')->respond();
    }

    public function grafik()
    {
        $data = [
            [
                'id' => 2,
                'nama' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '555-1234',
            ],
            [
                'id' => 3,
                'nama' => 'Jane Doe',
                'email' => 'jane@example.com',
                'phone' => '555-5678',
            ],
        ];

        return $this->fractal($data, new GrafikRtmTransformer(), 'grafik')->respond();
    }
}
