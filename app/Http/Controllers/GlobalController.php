<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\VendorBill;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Yajra\DataTables\DataTables;
use Mail;

class GlobalController extends Controller
{
    public static function Create_Counterparty($name,$data)
    {
            $response =Http::withBasicAuth(config('myconfig.MT.oid'), config('myconfig.MT.key'))
                ->asJson()
                ->post(config('myconfig.MT.url').'/api/counterparties',[
                    'name'=>$name,
                    'accounts'=>json_decode($data)
                ]);

          return $response;
    }
    public static function Create_Ledger($name,$currency)
    {
        $response =Http::withBasicAuth(config('myconfig.MT.oid'), config('myconfig.MT.key'))
            ->asJson()
            ->post(config('myconfig.MT.url').'/api/ledgers',[
                'name'=>$name,
                'currency'=>$currency
            ]);

        return $response;
    }
    public static function Create_ledger_Account($name,$normal_balance,$ledger_id)
    {
        $response =Http::withBasicAuth(config('myconfig.MT.oid'), config('myconfig.MT.key'))
            ->asJson()
            ->post(config('myconfig.MT.url').'/api/ledger_accounts',[
                'name'=>$name,
                'normal_balance'=>$normal_balance,
                'ledger_id'=>$ledger_id
            ]);

        return $response;
    }
}
