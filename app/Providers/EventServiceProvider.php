<?php

namespace App\Providers;

use App\Events\clientSubmittedThePaymentEvent;
use App\Events\newuserRegistered;
use App\Listeners\clientSubmittedThePaymentListner;
use App\Listeners\sendNoteToAdminNewUserRegistered;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        newuserRegistered::class => [
            sendNoteToAdminNewUserRegistered::class,
        ],
        clientSubmittedThePaymentEvent::class => [
            clientSubmittedThePaymentListner::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
