<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class convergeCOntroller extends Controller
{
    public function get_payment_token(Request $request)
    {
        $request->validate([
            'ssl_amount'=>'required|numeric|max:'.converge_max_amount()
        ]);
        $merchantID =config('myconfig.Converge.mid'); //Converge 6 or 7-Digit Account ID *Not the 10-Digit Elavon Merchant ID* dasdsa
        $merchantUserID = config('myconfig.Converge.uid'); //Converge User ID *MUST FLAG AS HOSTED API USER IN CONVERGE UI*
        $merchantPIN =config('myconfig.Converge.mpin') ; //Converge PIN (64 CHAR A/N)

        $url = config('myconfig.Converge.url')."/hosted-payments/transaction_token"; // URL to Converge demo session token server

// Read the following querystring variables

$firstname=$_POST['first_name']; //Post first name
$lastname=$_POST['last_name']; //Post last name
$amount= $_POST['ssl_amount']; //Post Tran Amount
$currency= $_POST['ssl_transaction_currency']; //Post Tran Amount

//        "&ssl_transaction_currency=$currency".
$ch = curl_init();    // initialize curl handle
curl_setopt($ch, CURLOPT_URL,$url); // set url to post to
curl_setopt($ch,CURLOPT_POST, true); // set POST method
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// Set up the post fields. If you want to add custom fields, you would add them in Converge, and add the field name in the curlopt_postfields string.
curl_setopt($ch,CURLOPT_POSTFIELDS,
    "ssl_merchant_id=$merchantID".
    "&ssl_user_id=$merchantUserID".
    "&ssl_pin=$merchantPIN".
    "&ssl_transaction_type=ccsale".
    "&ssl_first_name=$firstname".
    "&ssl_transaction_currency=$currency".
    "&ssl_last_name=$lastname".
    "&ssl_get_token=Y".
    "&ssl_add_token=Y".
    "&ssl_amount=$amount"
);
//asdasd


curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_VERBOSE, true);
// curl_setopt($ch, CURLOPT_PROXY, "http://your.proxy.server:port"); // proxy server config

$result = curl_exec($ch); // run the curl process
curl_close($ch); // Close cURL

echo $result;  //shows the session token.


 }
}
