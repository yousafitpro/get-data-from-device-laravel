<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\User;
use App\Notifications\passwordChangedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

class ProfileController extends Controller
{
    public $VIEW = 'profile';
    public $TITLE = 'Profile';
    public $URL = 'profile';
    public $SRC = 'images/profile/';

    public function __construct()
    {
        view()->share([
            'title' => $this->TITLE,
            'url' => url($this->URL),
        ]);
    }

    public function index(Request $request)
    {
        return view($this->VIEW . '.index', [
            'record' => auth()->user()
        ]);
    }

    public function changePassword(Request $request)
    {
        return view($this->VIEW . '.change-password', [
            'record' => auth()->user(),
            'title'=>'Change Password',
        ]);
    }

    public function changePasswordPost(Request $request)
    {
        $user = auth()->user();

        if (Hash::check($request->old_password, $user->password)) {
            $user->password = bcrypt($request->password);
            $user->save();

            if (Request::capture()->expectsJson())
            {
                return response()->json(['message'=>'Password successfully changed']);
            }
            $data['user']=$user;
            Notification::send(User::where('id',auth()->user()->id)->get(),new passwordChangedNotification($data));
//            auth()->logout();
            return redirect()->route('login')->with('message', 'Password successfully changed');
        }

        return back()
            ->withInput()
            ->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => 'Old password is incorrect',
                    'type' => 'error',
                ]
            ]);
    }

    public function store(Request $request)
    {

        $user = auth()->user();
        $data = $request->only('name', 'phone', 'city', 'address', 'zipcode', 'about');
        $image = $request->file('avatar');
        if ($image) {
            $data['avatar'] = saveImage($image, $this->SRC);
        }
        $user->update($data);
        if (Request::capture()->expectsJson())
        {
            return response()->json(['message'=>'Profile Updated']);
        }
        return redirect($this->URL)
            ->with([
                'toast' => [
                    'heading' => 'Message',
                    'message' => $this->TITLE . ' is updated',
                    'type' => 'success',
                ]
            ]);
    }
    public function updateBankInfo(Request $request)
    {
        $user=auth()->user();
        $user->bank_id=$request->bank;
        $user->save();
        if (User::where('id',$user->id)->where('bank_account_number',$request->bank_account_number)->exists())
        {
            return redirect($this->URL)
                ->with([
                    'toast' => [
                        'heading' => 'Message',
                        'message' =>  'Bank Info is Already Same',
                        'type' => 'success',
                    ]
                ]);
        }
        $account='[
      {
        "account_type": "checking",
        "routing_details": [
          {
            "routing_number_type": "aba",
            "routing_number": "121141822"
          }
        ],
        "account_details": [
          {
            "account_number":"'.$request->bank_account_number.'"
          }
        ]
      }
     ]';
        $counterparty=GlobalController::Create_Counterparty( $user->name,$account);
      if ($counterparty->status()==201)
      {
          $data=$counterparty->json();
        $user->counterparty_id=$data['accounts'][0]['id'];
        $user->bank_account_number=$request->bank_account_number;

          $user->bank_id=$request->bank;
          $user->save();
          return redirect($this->URL)
              ->with([
                  'toast' => [
                      'heading' => 'Message',
                      'message' =>  'Bank Info is updated',
                      'type' => 'success',
                  ]
              ]);
//          $data=$counterparty->json();
//        $user->counterparty_id=$data['accounts'][0]['id'];
//        $user->bank_account_number=$request->bank_account_number;
//
//          $ledger=GlobalController::Create_Ledger( $user->name."'s Bill Ledger","USD");
//          if ($ledger->status()==201)
//          {
//              $user->ledger_id=$ledger['id'];
//              $ledger_account=GlobalController::Create_ledger_Account( $user->name."'s Ledger Account","credit",$ledger['id']);
//              if ($ledger_account->status()==201)
//              {
//                  $user->ledger_account_id=$ledger_account['id'];
//
//                                $user->save();
//              return redirect($this->URL)
//                  ->with([
//                      'toast' => [
//                          'heading' => 'Message',
//                          'message' =>  'Bank Info is updated',
//                          'type' => 'success',
//                      ]
//                  ]);
//              }
//
//          }


      }


    }
    public function show($id)
    {

    }

    public function edit($id)
    {

    }

    public function update(Request $request, $id)
    {

    }

    public function destroy($id)
    {

    }

}
