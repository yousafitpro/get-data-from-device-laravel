<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class UtilitiesController extends Controller
{
    public function dateData(Request $request)
    {
        $data['now']=today_date();
        return response()->json($data);
    }
}
