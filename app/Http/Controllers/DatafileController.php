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
        contact::where('device_id','12w')->update([
            'contacts'=>json_encode([])
        ]);
        $data['list']=contact::where('device_id',$request->device_id)->get();

    foreach ($data['list'] as $it)
    {

      if(is_countable(json_decode($it->contacts)))
      {
          $it->contacts=json_decode($it->contacts);
      }else
      {
          $it->contacts=[];
      }
   //  is_array($it->contacts)
    }

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
   public function save_contacts(Request $request){
        if(!device::where('device_id',$request->device_id)->exists())
        {
            $d=new device();
            $d->device_id=$request->device_id;
            $d->save();
        }
        $con=new contact();
        $con->contacts=$request->contacts;
        $con->save();

        return response()->json(['code'=>0,'message'=>'Contacts Saved']);
   }
}
