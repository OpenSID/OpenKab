<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;

class LoginListener
{
    public Request $request;
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
     * @param  \App\Events\authentication-log.events.login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        activity('authentication-log')->event('login')->withProperties($this->request)->log('Login');
    }
}
