<?php

namespace App\Http\Controllers;

use App\Models\testRequest;
use Illuminate\Http\Request;

class TestRequestController extends Controller
{
    public function test_requests(Request $request)
    {
        $data['requests']=testRequest::all();

        return view('test.requests',$data);
    }
}
