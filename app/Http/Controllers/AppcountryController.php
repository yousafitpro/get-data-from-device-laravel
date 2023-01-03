<?php

namespace App\Http\Controllers;

use App\Models\appcountry;
use App\Models\ipaddress;
use App\Models\visitor;
use Illuminate\Http\Request;

class AppcountryController extends Controller
{
    public function index(Request $request)
    {
        $data['list']=appcountry::where('deleted_at',null)->get();

        return view('country.index',$data);
    }
    public function add(Request $request)
    {
        $data=$request->except('_token');
        appcountry::create($data);
        return redirect()->back()
            ->with([
                'toast' => [
                    'heading' => 'Success!',
                    'message' =>"Country successfully added",
                    'type' => 'success',
                ]
            ]);
    }
    public function remove(Request $request,$id)
    {
        $data=$request->except('_token');
        appcountry::where('id',$id)->update([
            'deleted_at'=>today_date()
        ]);
        return redirect()->back()
            ->with([
                'toast' => [
                    'heading' => 'Success!',
                    'message' =>"Country successfully Removed",
                    'type' => 'success',
                ]
            ]);
    }
}
