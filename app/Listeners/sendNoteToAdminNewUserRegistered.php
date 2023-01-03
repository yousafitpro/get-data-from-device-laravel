<?php

namespace App\Listeners;

use App\Events\newuserRegistered;
use App\Http\Controllers\AlertController;
use App\Models\User;
use App\Notifications\billCanceled;
use App\Notifications\NewUserRegiatered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class sendNoteToAdminNewUserRegistered
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
     * @param  \App\Events\newuserRegistered  $event
     * @return void
     */
    public function handle(newuserRegistered $event)
    {
//        AlertController::create([
//            'message'=>"Hello admin new user with email ".$event->user->name." has been registered",
//            'title'=>"New User Registered",
//            'type'=>'newUser',
//            'receiver'=>7,
//            'sender'=>7
//        ]);
        $data['user']=$event->user;

        $admin=User::find(who_is_admin());

        Notification::route('mail',$admin->email)->notify(new NewUserRegiatered($data));

    }
}
