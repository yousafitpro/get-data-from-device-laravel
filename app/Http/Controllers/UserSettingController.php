<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserSettingController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(user_setting(auth()->user()->id));
    }
}
