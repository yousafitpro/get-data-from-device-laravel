<?php

namespace App\Http\Controllers;

use App\Models\waitlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class WaitlistController extends Controller
{
    public function index()
    {
        $data['list']=waitlist::where('deleted_at',null)->get();
        return view('waitlist.index',$data);
    }
    public function add()
    {
        $data['code']=rand(3456,9999);
        Session::put('captcha-code',$data['code']);
        return view('waitlist.add',$data);
    }
    public function save(Request $request)
    {
        if (Session::get('captcha-code')!=$request->code)
        {
            return redirect()->back()->with([
                    'message' =>"Captcha Code is not valid"
                ]);

        }
        if (waitlist::where('email',$request->email)->exists())
        {
            return redirect()->back()->with([
                'message' =>"Email already existed."
            ]);

        }
        $data=$request->except(['_token']);
        waitlist::create($data);

        return redirect()->back()->with([
            'successMessage' =>"Your Are successfully Added in waiting list."
        ]);
    }
    public function delete($id)
    {
        $obj=waitlist::find($id);
        $obj->deleted_at=time_now();
        $obj->save();
        return redirect()->back()->with(['message'=>"User successfully removed"]);
    }
}
