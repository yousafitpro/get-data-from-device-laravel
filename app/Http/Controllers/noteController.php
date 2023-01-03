<?php

namespace App\Http\Controllers;

use App\Models\userNote;
use Illuminate\Http\Request;

class noteController extends Controller
{
    public function getUserNote(Request $request)
    {

        $value=userNote::where('user_id',auth()->user()->id)->value($request->collumn_name);
        userNote::where('user_id',auth()->user()->id)->update([
            $request->collumn_name=>'true'
        ]);
        return response()->json($value);
    }
    public function setUserNote(Request $request)
    {
        $value=userNote::where('user_id',auth()->user()->id)->update([
            $request->collumn_name=>$request->collumn_value
        ]);
        return response()->json(['message'=>"Updated"]);
    }
}
