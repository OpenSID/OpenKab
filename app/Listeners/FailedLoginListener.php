<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Http\Request;

class FailedLoginListener
{
    protected $request;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(Failed $event)
    {
        activity('authentication-log')->event('failed login')->withProperties($this->request)->log('Failed Login');
    }
}
