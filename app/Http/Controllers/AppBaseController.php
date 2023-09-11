<?php

namespace App\Http\Controllers;

use InfyOm\Generator\Utils\ResponseUtil;

/**
 * @OA\Server(url="/api")
 *
 * @OA\Info(
 *   title="InfyOm Laravel Generator APIs",
 *   version="1.0.0"
 * )
 * This class should be parent class for other API controllers
 * Class AppBaseController
 */
class AppBaseController extends Controller
{
    public function sendResponse($result, $message)
    {
        return response()->json(ResponseUtil::makeResponse($message, $result));
    }

    public function sendError($error, $code = 404)
    {
        return response()->json(ResponseUtil::makeError($error), $code);
    }

    public function sendSuccess($message)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
        ], 200);
    }

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
