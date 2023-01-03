<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ENVController;
use App\Mail\RegisterMail;
use App\Models\Business;
use App\Models\Package;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Mail;

class RegisterController extends Controller
{
    public $VIEW = 'auth.';

    public function __construct()
    {
        view()->share([
            'url' => url('register'),
            'title' => 'Register'
        ]);
    }

    public function index()
    {
        if (auth()->check()) {
            return redirect('dashboard');
        }
        return view($this->VIEW . '.register', [
            'plans' => Package::orderBY('years')->get()
        ]);
    }

    public function store(Request $request)
    {
        if ($request->plan=='2')
        {
            $request->validate([   'business_name' => 'required']);
        }
        $request->validate([
            'name' => 'required|string',
            'plan' => 'required',
            'password' => ['required', 'confirmed',
                'string',
                'min:8',             // must be at least 8 characters in length
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*#?&]/', // must contain a special character
            ],
        ]);
        if ($request->plan=='2')
        {
            $request->validate([
                'dateOfBirth' => 'required|string',
                'occupation' => 'required',
                'dbaName' =>'required',
                'url'=>'required',
                'typeOfBusiness'=>'required',
                'dateOfIncorporation'=>'required',
                'province'=>'required',
                'countryOfRegistration'=>'required',
                'provinceOfRegistration'=>'required',
                'businessTaxId'=>'required'
            ]);
        }

        $data = $request->except(['is_from_server','plan','password_confirmation']);
//        $data['email_verified_at'] = now();
        $data['password'] = bcrypt($request->password);
        $data['token'] = str_random(80);
//        $newDateTime = Carbon::now()->addYears($request->plan);
//        $data['subscription_end'] = $newDateTime;
        $data['package_id'] = $request->plan;
 if ($request->has('is_from_server') && $request->is_from_server=='yes')
 {
     if (User::where('email',$request->email)->exists())
     {

         User::where('email',$request->email)->update($data);
         $user=User::where('email',$request->email)->first();
     }
//asa
 }
 else
 {
     $request->validate([
         'email' => 'required|email|unique:users',
     ]);
     $user = User::create($data);
 }


        $role = Role::find(3);
        $user->assignRole($role->name);

        Mail::to($request->email)->send(new RegisterMail($user));

        return redirect()->route('login')->with([
            'message' => 'Please verify your account. check your email box'
        ]);
//        auth()->login($user);
        return redirect('dashboard')->with(['message' => 'Welcome to' . env('APP_NAME')]);
    }

    public function verify($code)
    {
        $data = explode('-', $code);
        $user = User::where('id', $data[1])->first();
        if ($user) {
            $user->email_verified_at = now();
            $user->save();
            ENVController::afterRegisterUser($user);
//            auth()->login($user);

            Session::put("login_2FA",true);
            return redirect('login')
                ->with([
                    'toast' => [
                        'heading' => 'Greeting',
                        'message' => 'Welcome! Account is verified',
                        'type' => 'success',
                    ]
                ]);
        }
        abort(404);
    }

}
