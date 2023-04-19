<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller as BaseController;

class Controller extends BaseController
{
    protected function fractal(
        $data,
        null|callable|\League\Fractal\TransformerAbstract $transformer,
        null|string $resourceName = null,
    ): \Spatie\Fractal\Fractal {
        return fractal(
            $data,
            $transformer,
            \League\Fractal\Serializer\JsonApiSerializer::class
        )
            ->withResourceName($resourceName);
    }
}
