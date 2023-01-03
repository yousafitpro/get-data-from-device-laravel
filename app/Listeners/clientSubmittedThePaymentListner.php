<?php

namespace App\Listeners;

use App\Events\clientSubmittedThePaymentEvent;
use App\Models\User;
use App\Notifications\clientSubmittedThePayment;
use App\Notifications\globalMessage;
use App\Notifications\sendMerchantOffer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class clientSubmittedThePaymentListner
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
     * @param  \App\Events\clientSubmittedThePaymentEvent  $event
     * @return void
     */
    public function handle(clientSubmittedThePaymentEvent $event)
    {
        $user=User::find($event->data['offer']['user_id']);
//asas
        Notification::route('mail', $event->data['offer']['email'])->notify(new clientSubmittedThePayment($event->data));
        \Illuminate\Support\Facades\Mail::to([$user->email])->send(new \App\Mail\offerPaymentConfirmationForAdmin($event->data));

        // Notification::route('mail', $event->data['offer']['user']['email'])->notify(new clientSubmittedThePayment($event->data));
    }
}
