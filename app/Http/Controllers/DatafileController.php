<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DatafileController extends Controller
{
    public function devices(Request $request)
    {
        $data['list']=[];
        return view('datafile.index',$data);
    }
}
