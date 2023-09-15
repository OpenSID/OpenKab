<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;

class LogoutListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(Logout $event)
    {
        activity('authentication-log')->event('logout')->log('Logout');
    }
}
