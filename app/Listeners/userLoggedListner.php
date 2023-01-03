<?php

namespace App\Listeners;

use App\Events\userLogged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class userLoggedListner
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
     * @param  \App\Events\userLogged  $event
     * @return void
     */
    public function handle(userLogged $event)
    {
        //
    }
}
