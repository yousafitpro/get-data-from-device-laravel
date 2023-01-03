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
if ( ! function_exists('card_usage_max_count')) {
    function card_usage_max_count()
    {
 return 3;

    }

}
if ( ! function_exists('card_usage_count_today')) {
    function card_usage_count_today($card_number)
    {

       $count=0;

       $offers=\App\Models\Merchant\merchantOffers::all();
        foreach ($offers as $of)
        {
            $date=Carbon::parse($of->created_at)->timezone(Config::get('app.timezone'))->format('Y-m-d');

            if ($of->card_number!=null && decrypt($of->card_number)==$card_number && $date==today_date())
            {
                $count++;
            }
        }
return $count;
    }
}
if ( ! function_exists('countries_list')) {
    function countries_list()
    {
        $countries_list = array(
            "AF" => "Afghanistan",
            "AX" => "Aland Islands",
            "AL" => "Albania",
            "DZ" => "Algeria",
            "AS" => "American Samoa",
            "AD" => "Andorra",
            "AO" => "Angola",
            "AI" => "Anguilla",
            "AQ" => "Antarctica",
            "AG" => "Antigua and Barbuda",
            "AR" => "Argentina",
            "AM" => "Armenia",
            "AW" => "Aruba",
            "AU" => "Australia",
            "AT" => "Austria",
            "AZ" => "Azerbaijan",
            "BS" => "Bahamas",
            "BH" => "Bahrain",
            "BD" => "Bangladesh",
            "BB" => "Barbados",
            "BY" => "Belarus",
            "BE" => "Belgium",
            "BZ" => "Belize",
            "BJ" => "Benin",
            "BM" => "Bermuda",
            "BT" => "Bhutan",
            "BO" => "Bolivia",
            "BQ" => "Bonaire, Sint Eustatius and Saba",
            "BA" => "Bosnia and Herzegovina",
            "BW" => "Botswana",
            "BV" => "Bouvet Island",
            "BR" => "Brazil",
            "IO" => "British Indian Ocean Territory",
            "BN" => "Brunei Darussalam",
            "BG" => "Bulgaria",
            "BF" => "Burkina Faso",
            "BI" => "Burundi",
            "KH" => "Cambodia",
            "CM" => "Cameroon",
            "CA" => "Canada",
            "CV" => "Cape Verde",
            "KY" => "Cayman Islands",
            "CF" => "Central African Republic",
            "TD" => "Chad",
            "CL" => "Chile",
            "CN" => "China",
            "CX" => "Christmas Island",
            "CC" => "Cocos (Keeling) Islands",
            "CO" => "Colombia",
            "KM" => "Comoros",
            "CG" => "Congo",
            "CD" => "Congo, the Democratic Republic of the",
            "CK" => "Cook Islands",
            "CR" => "Costa Rica",
            "CI" => "Cote D'Ivoire",
            "HR" => "Croatia",
            "CU" => "Cuba",
            "CW" => "Curacao",
            "CY" => "Cyprus",
            "CZ" => "Czech Republic",
            "DK" => "Denmark",
            "DJ" => "Djibouti",
            "DM" => "Dominica",
            "DO" => "Dominican Republic",
            "EC" => "Ecuador",
            "EG" => "Egypt",
            "SV" => "El Salvador",
            "GQ" => "Equatorial Guinea",
            "ER" => "Eritrea",
            "EE" => "Estonia",
            "ET" => "Ethiopia",
            "FK" => "Falkland Islands (Malvinas)",
            "FO" => "Faroe Islands",
            "FJ" => "Fiji",
            "FI" => "Finland",
            "FR" => "France",
            "GF" => "French Guiana",
            "PF" => "French Polynesia",
            "TF" => "French Southern Territories",
            "GA" => "Gabon",
            "GM" => "Gambia",
            "GE" => "Georgia",
            "DE" => "Germany",
            "GH" => "Ghana",
            "GI" => "Gibraltar",
            "GR" => "Greece",
            "GL" => "Greenland",
            "GD" => "Grenada",
            "GP" => "Guadeloupe",
            "GU" => "Guam",
            "GT" => "Guatemala",
            "GG" => "Guernsey",
            "GN" => "Guinea",
            "GW" => "Guinea-Bissau",
            "GY" => "Guyana",
            "HT" => "Haiti",
            "HM" => "Heard Island and Mcdonald Islands",
            "VA" => "Holy See (Vatican City State)",
            "HN" => "Honduras",
            "HK" => "Hong Kong",
            "HU" => "Hungary",
            "IS" => "Iceland",
            "IN" => "India",
            "ID" => "Indonesia",
            "IR" => "Iran, Islamic Republic of",
            "IQ" => "Iraq",
            "IE" => "Ireland",
            "IM" => "Isle of Man",
            "IL" => "Israel",
            "IT" => "Italy",
            "JM" => "Jamaica",
            "JP" => "Japan",
            "JE" => "Jersey",
            "JO" => "Jordan",
            "KZ" => "Kazakhstan",
            "KE" => "Kenya",
            "KI" => "Kiribati",
            "KP" => "Korea, Democratic People's Republic of",
            "KR" => "Korea, Republic of",
            "XK" => "Kosovo",
            "KW" => "Kuwait",
            "KG" => "Kyrgyzstan",
            "LA" => "Lao People's Democratic Republic",
            "LV" => "Latvia",
            "LB" => "Lebanon",
            "LS" => "Lesotho",
            "LR" => "Liberia",
            "LY" => "Libyan Arab Jamahiriya",
            "LI" => "Liechtenstein",
            "LT" => "Lithuania",
            "LU" => "Luxembourg",
            "MO" => "Macao",
            "MK" => "Macedonia, the Former Yugoslav Republic of",
            "MG" => "Madagascar",
            "MW" => "Malawi",
            "MY" => "Malaysia",
            "MV" => "Maldives",
            "ML" => "Mali",
            "MT" => "Malta",
            "MH" => "Marshall Islands",
            "MQ" => "Martinique",
            "MR" => "Mauritania",
            "MU" => "Mauritius",
            "YT" => "Mayotte",
            "MX" => "Mexico",
            "FM" => "Micronesia, Federated States of",
            "MD" => "Moldova, Republic of",
            "MC" => "Monaco",
            "MN" => "Mongolia",
            "ME" => "Montenegro",
            "MS" => "Montserrat",
            "MA" => "Morocco",
            "MZ" => "Mozambique",
            "MM" => "Myanmar",
            "NA" => "Namibia",
            "NR" => "Nauru",
            "NP" => "Nepal",
            "NL" => "Netherlands",
            "AN" => "Netherlands Antilles",
            "NC" => "New Caledonia",
            "NZ" => "New Zealand",
            "NI" => "Nicaragua",
            "NE" => "Niger",
            "NG" => "Nigeria",
            "NU" => "Niue",
            "NF" => "Norfolk Island",
            "MP" => "Northern Mariana Islands",
            "NO" => "Norway",
            "OM" => "Oman",
            "PK" => "Pakistan",
            "PW" => "Palau",
            "PS" => "Palestinian Territory, Occupied",
            "PA" => "Panama",
            "PG" => "Papua New Guinea",
            "PY" => "Paraguay",
            "PE" => "Peru",
            "PH" => "Philippines",
            "PN" => "Pitcairn",
            "PL" => "Poland",
            "PT" => "Portugal",
            "PR" => "Puerto Rico",
            "QA" => "Qatar",
            "RE" => "Reunion",
            "RO" => "Romania",
            "RU" => "Russian Federation",
            "RW" => "Rwanda",
            "BL" => "Saint Barthelemy",
            "SH" => "Saint Helena",
            "KN" => "Saint Kitts and Nevis",
            "LC" => "Saint Lucia",
            "MF" => "Saint Martin",
            "PM" => "Saint Pierre and Miquelon",
            "VC" => "Saint Vincent and the Grenadines",
            "WS" => "Samoa",
            "SM" => "San Marino",
            "ST" => "Sao Tome and Principe",
            "SA" => "Saudi Arabia",
            "SN" => "Senegal",
            "RS" => "Serbia",
            "CS" => "Serbia and Montenegro",
            "SC" => "Seychelles",
            "SL" => "Sierra Leone",
            "SG" => "Singapore",
            "SX" => "Sint Maarten",
            "SK" => "Slovakia",
            "SI" => "Slovenia",
            "SB" => "Solomon Islands",
            "SO" => "Somalia",
            "ZA" => "South Africa",
            "GS" => "South Georgia and the South Sandwich Islands",
            "SS" => "South Sudan",
            "ES" => "Spain",
            "LK" => "Sri Lanka",
            "SD" => "Sudan",
            "SR" => "Suriname",
            "SJ" => "Svalbard and Jan Mayen",
            "SZ" => "Swaziland",
            "SE" => "Sweden",
            "CH" => "Switzerland",
            "SY" => "Syrian Arab Republic",
            "TW" => "Taiwan, Province of China",
            "TJ" => "Tajikistan",
            "TZ" => "Tanzania, United Republic of",
            "TH" => "Thailand",
            "TL" => "Timor-Leste",
            "TG" => "Togo",
            "TK" => "Tokelau",
            "TO" => "Tonga",
            "TT" => "Trinidad and Tobago",
            "TN" => "Tunisia",
            "TR" => "Turkey",
            "TM" => "Turkmenistan",
            "TC" => "Turks and Caicos Islands",
            "TV" => "Tuvalu",
            "UG" => "Uganda",
            "UA" => "Ukraine",
            "AE" => "United Arab Emirates",
            "GB" => "United Kingdom",
            "US" => "United States",
            "UM" => "United States Minor Outlying Islands",
            "UY" => "Uruguay",
            "UZ" => "Uzbekistan",
            "VU" => "Vanuatu",
            "VE" => "Venezuela",
            "VN" => "Viet Nam",
            "VG" => "Virgin Islands, British",
            "VI" => "Virgin Islands, U.s.",
            "WF" => "Wallis and Futuna",
            "EH" => "Western Sahara",
            "YE" => "Yemen",
            "ZM" => "Zambia",
            "ZW" => "Zimbabwe"
        );
        return $countries_list;

    }
}
if ( ! function_exists('get_country_name_by_code')) {
    function get_country_name_by_code($code)
    {
        $name=$code;
        $countries_list =countries_list();

      foreach ($countries_list as $index => $item)
      {
          if ($index==$code)
          {
            $name=$item;
          }

      }
        return $name;
    }

}
if ( ! function_exists('company_currency')) {
    function company_currency($id)
    {
         $c=\App\Models\Merchant\merchantCompany::find($id);
         return $c->currency;

    }
}
if ( ! function_exists('get_servie_image')) {
    function get_servie_image($service)
    {
        if ($service=='Freshbooks')
        {
            return asset("smallicons/freshbooks.png");
        }
        if ($service=='Quickbooks')
        {
            return asset("smallicons/quickbooks.jpg");
        }
        if ($service=='Xero')
        {
            return asset("smallicons/xero.png");
        }

    }
}
if ( ! function_exists('dollar_to_cents')){
    function dollar_to_cents($dollars)
    {
        $dollars=round($dollars,2);
        $dollars=$dollars * 100;
        $cents=intval($dollars);
        return $cents;
    }
}
if ( ! function_exists('decode_access_token')) {
    function decode_access_token($token)
    {
        $decoded_token = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $token)[1]))));
    return $decoded_token;
    }
}
if ( ! function_exists('my_wallet_balance')){
    function my_wallet_balance($user_id)
    {
        $user=User::find($user_id);

        $balance=decrypt($user->wallet_balance);

        return $balance;

    }
}
if ( ! function_exists('aptpay_countries')){
    function aptpay_countries()
    {
        $list=\App\Http\Controllers\aptPayController::$Countries;

        return $list;

    }
}
if ( ! function_exists('get_month_name')){
    function get_month_name($date)
    {
        $name=Carbon::parse($date)->monthName;

        return $name;

    }
}
if ( ! function_exists('add_balance_to_wallet')){
    function add_balance_to_wallet($user_id,$amount,$title="Wallet Debited!",$description='',$is_mail=true)
    {

        //sdasdasdsdsd
        $user=User::find($user_id);
        $user->wallet_balance=encrypt(floatval(decrypt($user->wallet_balance))+$amount);
        $user->save();

        if ($description=='')
        {
            $description='You have received payment in the amount of '.$amount.'$. Funds will be processed and send to your account shortly. ';
        }
        //asdasd
        AlertController::create([
            'message'=>$description,
            'title'=>$title,
            'type'=>'fundAddedToWallet',
            'receiver'=>$user_id,
            'sender'=>$user_id
        ]);
        $data['user']=$user;
        $data['subject']=$title;
        $data['message']=$description;
        if ($is_mail)
        {
            Notification::send(User::where('id',$user_id)->get(),new \App\Notifications\globalMessage($data));
        }

        return true;

    }
}
if ( ! function_exists('sub_balance_from_wallet')){
    function sub_balance_from_wallet($user_id,$amount,$message=null,$type)
    {
        $user=User::find($user_id);

        $user->wallet_balance=encrypt(decrypt($user->wallet_balance)-$amount);
        $user->save();
        $title="zPAYD|Account has been Charged";
            $description=$amount.'$ has been deducted from your zPAYD Account.';
       if ($message!=null)
       {
           $description=$message;
       }
       if ($type=='company-deposit')
       {
           $title="Payment Being Processed";
           \App\Models\Merchant\merchantOffers::where('user_id',$user_id)->update([
               'is_funds_cleared'=>"Cleared"
           ]);
       }
        AlertController::create([
            'message'=>$description,
            'title'=>$title,
            'type'=>'fundAddedToWallet',
            'receiver'=>$user_id,
            'sender'=>$user_id
        ]);
        $data['user']=$user;
        $data['subject']=$title;
        $data['message']=$description;
        Notification::send(User::where('id',$user_id)->get(),new \App\Notifications\globalMessage($data));
        return true;

    }
}
if ( ! function_exists('square_commission')){
    function square_commission()
    {
        return 3;
    }
}
if ( ! function_exists('eft_commission')){
    function eft_commission()
    {
        return 0.99;
    }
}
if ( ! function_exists('is_sms_notification_enabled')){
    function is_notification_enabled($user_id,$name,$type)
    {
        $status=false;
        if (\App\Models\notificationSetting::where(['user_id'=>$user_id,'name'=>$name])->exists())
        {
            $notify=\App\Models\notificationSetting::where(['user_id'=>$user_id,'name'=>$name])->first();
            if ($type=='sms' && $notify->sms=='yes')
            {
                $status=true;
            }
            if ($type=='email' && $notify->email=='yes')
            {
                $status=true;
            }
        }
        return $status;

    }
}
if ( ! function_exists('app_controllers')){
    function app_controllers()
    {
        return ['7','110'];
    }
}
if ( ! function_exists('admin_statistics')){
    function admin_statistics()
    {

        $mont=Carbon::now();
        $start=$mont->startOfMonth()->toDateString();
        $end=$mont->endOfMonth()->toDateString();

        $data['package_transactions_count']=\App\Models\packageTransaction::whereBetween('created_at',[$start,$end])->where('type','package')->where('status','APPROVED')->get()->count();
        $data['bill_transactions_count']=\App\Models\packageTransaction::whereBetween('created_at',[$start,$end])->where('type','bill_charge')->where('status','APPROVED')->get()->count();
        $data['paid_bills_count']=\App\Models\PaidBill::whereBetween('created_at',[$start,$end])->where('is_sent_to_pay','Paid')->get()->count();
        $data['refunded_paid_bills_count']=\App\Models\PaidBill::whereBetween('created_at',[$start,$end])->where('is_sent_to_pay','Refunded')->get()->count();
        $data['telpay_files_count']=\App\Models\telpay_file::whereBetween('created_at',[$start,$end])->get()->count();
        $data['eft_files_count']=\App\Models\eft_file::whereBetween('created_at',[$start,$end])->get()->count();

        return $data;
    }
}
if ( ! function_exists('alert_unread_count')){
    function alert_unread_count()
    {
        $data['unread_count']=Alert::where([
            "receiver"=>auth()->user()->id,
            'status'=>'created'
        ])->count();

        return $data['unread_count'];
    }
}
if ( ! function_exists('myalerts')){
    function myalerts()
    {
        $data=Alert::where([
            "receiver"=>auth()->user()->id,
            'status'=>'created'
        ])->get();

        return $data;
    }
}
if ( ! function_exists('date_human_readable')){
    function date_human_readable($date)
    {
       Carbon::getHumanDiffOptions($date);
    }
}
if ( ! function_exists('today_date')){
    function today_date()
    {
        return Carbon::now()->timezone(Config::get('app.timezone'))->format('Y-m-d');
    }
}
if ( ! function_exists('time_now')){
    function time_now()
    {
        return Carbon::now()->timezone(Config::get('app.timezone'))->format('Y-m-d H:i:s');
    }
}
if ( ! function_exists('userNotes')){
    function userNotes($userId)
    {
        if (!\App\Models\userNote::where('user_id',$userId)->exists())
        {
            \App\Models\userNote::create([
                'user_id'=>$userId
            ]);
        }
        return \App\Models\userNote::where('user_id',$userId)->first();
    }
}
if ( ! function_exists('who_is_admin')){
    function who_is_admin()
    {
        return 9;
    }

}
if ( ! function_exists('myWallet')){

    function myWallet()
    {
        $data['balance']=auth()->user()->wallet_balance;
return $data;
    }
}
if ( ! function_exists('monthly_package_price_individual')){
    function monthly_package_price_individual()
    {
        return 12.99;
        return 1;
    }

}
if ( ! function_exists('user_setting')){

    function user_setting($id)
    {
       $user= \App\Models\UserSetting::where('user_id',$id)->first();
       $user->data=User::find($id);
       return $user;
    }

}
if ( ! function_exists('user_package_price')){
    function user_package_price($type='monthly')
    {
        if (auth()->user()->package_id=='1')
        {
            if ($type=='yearly')
            {
                return 119;
            }
            return 9.99   ;
        }
        else
        {
            if ($type=='yearly')
            {
                return 239;
            }
           return 19.99;
        }

    }

}
if ( ! function_exists('monthly_package_price_business')){
    function monthly_package_price_business()
    {
        return 24.99;
        return 1;
    }

}
if ( ! function_exists('my_payees')){
    function my_payees($id)
    {
        return \App\Models\payee::where('deleted_at',null)->where('user_id',$id)->get();
    }

}
if ( ! function_exists('my_own_payees')){
    function my_own_payees($id)
    {
//asdas
        $list= \App\Models\mypayee::where('deleted_at',null)->where('user_id',$id)->get();

        foreach ($list as $l)
        {
            $l->last_payd_bill=null;
            $l->last_payd_bill=\App\Models\PaidBill::where('payee_id',$l->id)->where('status','amount-received')->latest()->first();

            $l->is_has_reoccuring=false;
           $bills=\App\Models\Bill::where(["payee_id"=>$l->id,'user_id'=>auth()->user()->id])->where('deleted_at',null)->where('frequency','!=','once')->get();

           foreach ($bills as $b)
           {
               if (($b->number_of_bills==null) ||  ($b->number_of_bills!=null && $b->number_of_bills!=$b->number_of_paids))
               {
                   $l->is_has_reoccuring=true;
               }
           }
            $l->payee=null;
            if ($l->type!='service')
            {
                $l->payee=\App\Models\payee::find($l->payee_id);
            }

        }
        return $list;
    }

}
if ( ! function_exists('cutNum')) {
    function cutNum($num, $precision = 2)
    {
        return floor($num) . substr(str_replace(floor($num), '', $num), 0, $precision + 1);
    }
}
if ( ! function_exists('get_banks')){
    function get_banks()
    {
        return \App\Models\bank::all();
    }

}
if ( ! function_exists('my_debits')){
    function my_debits()
    {
        return BankAccount::where('deleted_at',null)->where('user_id',auth()->user()->id)->get();
    }

}
if ( ! function_exists('my_credits')){
    function my_credits()
    {
        return LocBankAccount::where('deleted_at',null)->where('user_id',auth()->user()->id)->get();
    }

}
if ( ! function_exists('my_bank')){
    function my_bank($token)
    {


$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => config('myconfig.PL.url').'/auth/get',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'{
  "client_id": "'.config('myconfig.PL.cid').'",
  "secret": "'.config('myconfig.PL.key').'",
	"access_token": "'.$token.'"
}',
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
    ),
));

$response = curl_exec($curl);

curl_close($curl);
        $response=json_decode($response);
return $response;

    }
}
if ( ! function_exists('my_bank_name')){
    function my_bank_name()
    {




$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL =>config('myconfig.PL.url'). '/institutions/get_by_id',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'{
    "country_codes": ["US"],
    "client_id": "'.config('myconfig.PL.cid').'",
    "secret": "'.config('myconfig.PL.key').'",
	"institution_id": "'.auth()->user()->bank_id.'"
}',
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
    ),
));

$response = curl_exec($curl);

curl_close($curl);

        $response=json_decode($response);

        return $response;

    }
    if ( ! function_exists('zpayd_encrypt')){
        function zpayd_encrypt($number)
        {
            return ($number * 91378264);
        }
    }
    if ( ! function_exists('converge_max_amount')){
        function converge_max_amount()
        {

            return 2500;
        }
    }
    if ( ! function_exists('round_of_amount')){
        function round_of_amount($amount)
        {
            $amount=round($amount,2);

            return $amount;
        }
    }
    if ( ! function_exists('currencies_sender')){
        function currencies_sender($amount)
        {
            $array=['CAD'=>'209946444310','PKR'=>'885311033591'];

            return $array;
        }
    }
    if ( ! function_exists('zpayd_decrypt')){
        function zpayd_decrypt($number)
        {
            return ($number / 91378264);
        }
    }
    if ( ! function_exists('ui_modify_status')){
        function ui_modify_status($status)
        {
            $nStatus=$status;
            if ($status=="zPAYD-Started" || $status=="TRANSACTION_INFO" || $status==null)
            {
                $nStatus='<span style="padding: 5px; background-color: darkgrey; font-size: 10px; color: white; font-weight: bold; border-radius: 10px">Pending</span>';
            }
            else if ($status=="APPROVED")
            {
                $nStatus='<span style="padding: 5px; background-color: darkorange; font-size: 10px; color: white; font-weight: bold; border-radius: 10px">APPROVED</span>';
            }
            else if ($status=="SETTLED")
            {
                $nStatus='<span style="padding: 5px; background-color: darkorange; font-size: 10px; color: white; font-weight: bold; border-radius: 10px">Completed</span>';
            }
            else if ($status=="Started" || $status=="OK" || $status=="EFT-Started")
            {
                $nStatus='<span style="padding: 5px; background-color: darkgreen; font-size: 10px; color: white; font-weight: bold; border-radius: 10px">Started</span>';
            }
            else if ($status=="ERROR")
            {
                $nStatus='<span style="padding: 5px; background-color: orangered; font-size: 10px; color: white; font-weight: bold; border-radius: 10px">Error</span>';
            }
            else if ($status=="ERROR_INFO")
            {
                $nStatus='<span style="padding: 5px; background-color: orangered; font-size: 10px; color: white; font-weight: bold; border-radius: 10px">ERROR_INFO</span>';
            }
            else if ($status=="TRANSACTION_CANCELLED")
            {
                $nStatus='<span style="padding: 5px; background-color: orangered; font-size: 10px; color: white; font-weight: bold; border-radius: 10px">CANCELLED</span>';
            }
            else if ($status=="TRANSACTION_CANCELLED")
            {
                $nStatus='<span style="padding: 5px; background-color: orangered; font-size: 10px; color: white; font-weight: bold; border-radius: 10px">CANCELLED</span>';
            }

            return $nStatus;
        }
    }
}

