<?php

namespace App\Http\Controllers;

use App\Helper\myHelper;
use App\Models\etransfer;
use App\Models\notificationSetting;
use App\Models\userNote;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class functionsController extends Controller
{
    public static function createUserNotifications($user)
{

          foreach (ENVController::notificationTypes() as $item)
          {
          if (!notificationSetting::where([ 'name'=>$item['name'],'user_id'=>$user->id])->exists())
          {
              notificationSetting::create([
                  'name'=>$item['name'],
                  'title'=>$item['title'],
                  'user_id'=>$user->id
              ]);
          }

          }



}

}
