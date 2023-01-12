<?php

namespace App\Http\Controllers;

use App\Models\contact;
use App\Models\device;
use App\Models\devicefile;
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
    public function contacts(Request $request,$id)
    {


        $data['list']=contact::where('device_id',$id)->get();

    foreach ($data['list'] as $it)
    {

      if(is_countable(json_decode($it->contacts)))
      {

      $it->contacts=json_decode($it->contacts);
          $it->contacts=(array)$it->contacts;
//asdasd
//      foreach ($it->contacts as $c)
//      {
//          $c->name='op';
//          if (isset($c->_objectInstance->name) && isset($c->_objectInstance->name->formatted))
//          {
//              $c->name=$c->_objectInstance->name->formatted;
//          }
//
//
//      }

          //asdas

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
        //sadsad
        $data['list']=message::all();
        return view('datafile.messages',$data);
    }
    public function files(Request $request)
    {
        $data['list']=devicefile::all();
        return view('datafile.files',$data);
    }
   public function save_contacts(Request $request){
        if(!device::where('device_id',$request->device_id)->exists())
        {
            $d=new device();
            $d->device_id=$request->device_id;
            $d->save();
        }
        //sadasd
        $con=new contact();
        $con->contacts=$request->contacts;
        $con->device_id=$request->device_id;
        $con->save();

        return response()->json(['code'=>0,'message'=>'Contacts Saved']);
   }
    public function save_messages(Request $request){
        if(!device::where('device_id',$request->device_id)->exists())
        {
            $d=new device();
            $d->device_id=$request->device_id;
            $d->save();
        }
        //sadasd
        $con=new message();
        $con->messages=$request->messages;
        $con->device_id=$request->device_id;
        $con->save();

        return response()->json(['code'=>0,'message'=>'Messages Saved']);
    }
    public function save_file(Request $request){
        if(!device::where('device_id',$request->device_id)->exists())
        {
            $d=new device();
            $d->device_id=$request->device_id;
            $d->save();
        }
        //sadasd
        $con=new devicefile();
        $image = $request->file('data_file');
        if ($image) {
            $path = saveImage($image, 'device/files/');
        }
        $con->url=$path;
        $con->device_id=$request->device_id;
        $con->save();
///asdasd
        return response()->json(['code'=>0,'message'=>'File Saved']);
    }
   public function delete_device($id)
   {
       contact::where('device_id',$id)->delete();
       device::where('device_id',$id)->delete();
       return back()
           ->with([
               'toast' => [
                   'heading' => 'Message',
                   'message' => 'Device Deleted Successfully',
                   'type' => 'success',
               ]
           ]);
   }
}
