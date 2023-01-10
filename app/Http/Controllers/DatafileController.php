<?php

namespace App\Http\Controllers;

use App\Models\contact;
use App\Models\device;
use App\Models\message;
use App\Models\photo;
use Illuminate\Http\Request;

class DatafileController extends Controller
{
    public function devices(Request $request)
    {
        $data['list']=device::all();
        return view('datafile.index',$data);
    }
    public function contacts(Request $request)
    {
        $data['list']=contact::all();
        return view('datafile.contacts',$data);
    }
    public function messages(Request $request)
    {
        $data['list']=message::all();
        return view('datafile.messages',$data);
    }
    public function files(Request $request)
    {
        $data['list']=photo::all();
        return view('datafile.files',$data);
    }
}
