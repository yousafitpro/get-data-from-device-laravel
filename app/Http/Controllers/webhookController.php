<?php

namespace App\Http\Controllers;

use App\Models\paymentorder;
use Illuminate\Http\Request;

class webhookController extends Controller
{
    public function receive_payment_order_status(Request $request){
        $res=$request->getContent();
        $res=json_decode($res);

         $order=paymentorder::where('order_id',$res->data->id)->first();
         $order->status=$res->event;
         $order->save();

        return response()->json(['message'=>"Webhook operation successful"]);
    }
}
