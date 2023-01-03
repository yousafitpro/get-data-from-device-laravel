<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class airwallexController extends Controller
{
    public static function get_token()
    {
        try {
            $req=Http::withHeaders([
                'x-client-id'=>config("myconfig.Airwallex.cid"),
                'x-api-key'=>config("myconfig.Airwallex.key")
            ])->post(config("myconfig.Airwallex.url").'/authentication/login',[]);
            if ($req->status()=='200' || $req->status()=='201')
            {
                $data=$req->json();
                return $data['token'];
            }else
            {
                return false;
            }
        }catch (\Exception $exception)
        {
            return false;
        }
    }
}
