<?php

namespace App\Http\Controllers;

use App\Jobs\addFundToWalletUsingCardJob;
use App\Jobs\RemoveRestrictionAfter24HoursJob;
use App\Models\appcountry;
use App\Models\ipaddress;
use App\Models\User;
use App\Models\visitor;
use App\Notifications\globalMessage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

class ipController extends Controller
{
    public function index(Request $request)
    {
        $data['list']=ipaddress::where('deleted_at',null)->get();

          return view('ip.index',$data);
    }
    public function users_on_map(Request $request)
    {
        $query=visitor::where('deleted_at',null);
        if($request->has('mdate') && $request->mdate!=null)
        {
//asdas
            $query=$query->where('created_at', '>=', Carbon::parse($request->mdate));
        }
        if($request->type=="users")
        {
            $query=$query->where('user_id', '!=',null);
        }
        if($request->type=="guests")
        {
            $query=$query->where('user_id',null);
        }
        $data['list']=$query->get();
        return view('ip.usersOnMap',$data);
    }
    public function visitors(Request $request)
    {
        $data['list']=visitor::where('deleted_at',null)->latest('added_at')->get();

        return view('ip.visitors',$data);
    }
    public function add(Request $request)
    {
        $data=$request->except('_token');
        ipaddress::create($data);
        return redirect()->back()
            ->with([
                'toast' => [
                    'heading' => 'Success!',
                    'message' =>"IP successfully added",
                    'type' => 'success',
                ]
            ]);
    }
    public function remove(Request $request,$id)
    {
        $data=$request->except('_token');
        ipaddress::where('id',$id)->update([
            'deleted_at'=>today_date()
        ]);
        return redirect()->back()
            ->with([
                'toast' => [
                    'heading' => 'Success!',
                    'message' =>"IP successfully Removed",
                    'type' => 'success',
                ]
            ]);
    }

    public static function save_visitor_info($ip)
    {

        $device_fingerprint=get_device_fingerprint();
        $previous=null;
        $user_info=null;
        $oldprevious=null;
        $newprevious=null;
        if(visitor::where('query',$ip)->where('deleted_at',null)->exists())
        {
            $previous=visitor::where('query',$ip)->where('deleted_at',null)->first();
            $oldprevious=visitor::where('query',$ip)->where('deleted_at',null)->first();
        }
        //asdasd
        if(visitor::where('device_fingerprint',$device_fingerprint)->where('deleted_at',null)->exists())
        {
            $previous=visitor::where('device_fingerprint',$device_fingerprint)->where('deleted_at',null)->first();
            $oldprevious=visitor::where('device_fingerprint',$device_fingerprint)->where('deleted_at',null)->first();
        }
        if(auth()->check() && visitor::where('user_id',auth()->id())->where('deleted_at',null)->exists())
        {
            $previous=visitor::where('user_id',auth()->id())->where('deleted_at',null)->first();
            $oldprevious=visitor::where('user_id',auth()->id())->where('deleted_at',null)->first();

        }

        if ($previous!=null)
        {

//            dd($previous);
            $startTime=Carbon::parse($previous->added_at,Config::get('app.timezone'));
            $finishTime=Carbon::now(Config::get('app.timezone'));
            $diff = $startTime->diffInMinutes($finishTime);
            if (auth()->check())
            {
                $previous->user_id=auth()->id();
                $previous->save();
            }
            //asdasd

//            if ($diff>0)
//            {
                $req=Http::get('http://ip-api.com/json/'.$ip);
                if ($req->status()=='200' && $req->json()['status']=='success')
                {
                    $data=$req->collect()->only([
                        "query",
                        "country",
                        "countryCode",
                        "region",
                        "regionName",
                        "city",
                        "zip",
                        "lat",
                        "lon",
                        "timezone",
                        "isp",
                        "org",
                        "as"
                    ]);
                    $data=$data->toArray();
                    $data['added_at']=time_now();
                    if (auth()->check())
                    {

                        $data['user_id']=auth()->id();
                    }
                    $data['lat']=strval($data['lat']);
                    $data['lon']=strval($data['lon']);
                    $data['device_fingerprint']=$device_fingerprint;

                    visitor::where('id',$previous->id)->update($data);
                    $previous=visitor::find($previous->id);

                }
//            }

        }
        else
        {

            $req=Http::get('http://ip-api.com/json/'.$ip);
            if ($req->status()=='200' && $req->json()['status']=='success')
            {
                $data=$req->collect()->only([
                    "query",
                    "country",
                    "countryCode",
                    "region",
                    "regionName",
                    "city",
                    "zip",
                    "lat",
                    "lon",
                    "timezone",
                    "isp",
                    "org",
                    "as"
                ]);
                $data=$data->toArray();
                if (auth()->check())
                {

                    $data['user_id']=auth()->id();
                }
                $data['added_at']=time_now();
                $data['lat']=strval($data['lat']);
                $data['lon']=strval($data['lon']);
                $data['device_fingerprint']=$device_fingerprint;
                $data['query']=$ip;
              $previous= visitor::create($data);


            }

//            dd($previous);
        }
        // if country is different but device is same
        if (!auth()->check() && $oldprevious && visitor::where('device_fingerprint',$device_fingerprint)->where('deleted_at',null)->exists())
        {

            if($oldprevious->countryCode!=$previous->countryCode)
            {


                $ip= ipaddress::create([
                    'device_fingerprint'=>$device_fingerprint,
                    'user_id'=>auth()->check()?auth()->id():null,
                    'ip'=>$ip
                ]);
                $tempData['id']=$ip->id;
                RemoveRestrictionAfter24HoursJob::dispatch($tempData)->delay(Carbon::now(config('app.timezone'))->addHours(24));

                visitor::where('device_fingerprint',$device_fingerprint)->where('deleted_at',null)->update([
                    'deleted_at'=>time_now()
                ]);

            }
        }
        // check same account with different device and ip in different country
        if (auth()->check() && $oldprevious)
        {

                $startTime=Carbon::parse($oldprevious->added_at,Config::get('app.timezone'));
                $finishTime=Carbon::parse($previous->added_at,Config::get('app.timezone'));
                $diff = $startTime->diffInHours($finishTime);

                if($oldprevious->countryCode!=$previous->countryCode && $diff<2)
                {

                    $user=User::find(auth()->id());
                    $tempData['user']=$user;
                    $tempData['subject']="Account Restricted Due To Suspicious Activity";
                    $tempData['message']="Your account has been blocked due to suspicious activities. For support please contact us at support@zpayd.com";
                    Notification::route('mail', $user->email)->notify(new globalMessage($tempData));
                    $user->status="Deleted";
                    $user->save();
                    $ip= ipaddress::create([
                        'device_fingerprint'=>$device_fingerprint,
                        'user_id'=>auth()->check()?auth()->id():null,
                        'ip'=>$ip
                    ]);
                    $tempData['id']=$ip->id;
                    RemoveRestrictionAfter24HoursJob::dispatch($tempData)->delay(Carbon::now(config('app.timezone'))->addHours(24));
                    visitor::where('user_id',auth()->id())->where('deleted_at',null)->update([
                        'deleted_at'=>time_now()
                    ]);
                }

                //zX


        }


    }
    public function block_ip(Request $request)
    {
        //asdasd

        $ip= ipaddress::create([
            'user_id'=>auth()->check()?auth()->id():null,
            'ip'=>app_get_ip($request)
        ]);
        $tempData['id']=$ip->id;
        RemoveRestrictionAfter24HoursJob::dispatch($tempData)->delay(Carbon::now(config('app.timezone'))->addHours(24));

    }

}
