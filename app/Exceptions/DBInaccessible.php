<?php

namespace App\Exceptions;

use Exception;

class DBGabunganInaccessible extends Exception
{
    protected $message = '';

    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->message = $message;
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        if ($request->is('api/*')) {
            return response()->json([
                'message' => $this->message,
            ], 500);
        }

        return response($this->message, 500);
    }
}
