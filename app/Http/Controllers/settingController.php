<?php

namespace App\Http\Controllers;

use App\Models\notificationSetting;
use Illuminate\Http\Request;

class settingController extends Controller
{
    public function index()
    {
        if (Request::capture()->expectsJson())
        {
            $list=notificationSetting::where('user_id',auth()->user()->id)->get();

            return $list;
        }
        return view('setting.notifications');
    }
    public function update_column(Request $request)
    {
        notificationSetting::where('user_id',auth()->user()->id)->where('name',$request->name)->update([
            $request->column=>strtolower($request->value)
        ]);
        if (Request::capture()->expectsJson())
        {
            return response()->json(['message'=>"updated"]);
        }

    }
}
