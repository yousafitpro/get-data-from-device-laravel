<?php

use App\Http\Controllers\AlertController;
use App\Models\Alert;
use App\Models\BankAccount;
use App\Models\etransfer_transaction;
use App\Models\gateway;
use App\Models\LocBankAccount;
use App\Models\User;
use App\Models\pakage;
use App\Models\chat_request;
use App\Models\chat_message;
use App\Models\website;
use App\Models\artical;
use App\Models\note;
use App\Models\department;
use App\Models\email_template;

use App\Notifications\billChargedFromCardInsteadWallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;
if ( ! function_exists('VC_pending_query')){
    function VC_pending_query($user=null)
    {
    if($user==null)
    {
       $user=auth()->user();
    }
        $item=\App\Models\VCQuery::where('user_id',$user->id)->latest()->first();
        return $item;
    }

}
if ( ! function_exists('app_get_ip')){
    function app_get_ip($request)
    {
          return $request->ip();
    }

}
if ( ! function_exists('app_get_mac_address')){
    function app_get_mac_address()
    {
        return substr(exec('getmac'), 0, 17);
    }

}
if ( ! function_exists('get_device_fingerprint')){
    function get_device_fingerprint()
    {

        ?>
        <script>
            // Initialize the agent at application startup.
            var fpPromise = import('https://openfpcdn.io/fingerprintjs/v3')
                .then(FingerprintJS => FingerprintJS.load())

            // Get the visitor identifier when you need it.
            fpPromise
                .then(fp => fp.get())
                .then(result => {
                    // This is the visitor identifier:
                    const visitorId = result.visitorId
                    document.cookie = "visitorId = " + visitorId ;
                    console.log(document.cookie);

                })
            //asdasdasasdas
        </script>

<?php
        $val=null;
        try {
            if(isset($_COOKIE['visitorId']))
            {
                //aSas
                $val= $_COOKIE['visitorId'];
            }else
            {
                $val=strval(app_get_ip(request()));
            }
        }catch (\Exception $e)
        {
            $val=strval(app_get_ip(request()));
        }
        return $val;
    }

}
