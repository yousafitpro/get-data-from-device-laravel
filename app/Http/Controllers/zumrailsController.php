<?php

namespace App\Http\Controllers;

use App\Models\testRequest;
use Illuminate\Http\Request;

class zumrailsController extends Controller
{
    public function web_url(Request $request)
    {

            $new=new testRequest();
            dd($request->code);
            $data=$request;
//       $data= hash('sha256', $_POST['ppasscode']);

            $new->content= $data;
            $new->origin=$request->headers->get('origin');
            $new->save();
            return response()->json(['message'=>"Request Successful"]);
    }
}
