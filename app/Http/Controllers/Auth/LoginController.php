<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ENVController;
use App\Http\Controllers\railzController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    public function __construct()
    {
        view()->share([
            'url' => url('login'),
            'title' => 'Login'
        ]);
    }

    public function index()
    {
        if (auth()->check()) {
            return redirect('dashboard');
        }
        return view('auth.login');
    }

    public function postLogin(Request $request)
    {

        $message = 'Wrong Credentials';
        $data = $request->only('email', 'password');
        $email = $request->email;
        $user = User::where('email', $email)->first();
        if ($user && !$user->is_deleted) {

//            if ($user->email_verified_at) {
                if (auth()->attempt($data)) {


                    return redirect('dashboard',)->with([
                        'toast' => [
                            'heading' => 'Greeting',
                            'message' => 'welcome back',
                            'type' => 'success',
                        ]
                    ]);
                }
//            } else {
//                $message = 'Email is not verified';
//            }
        }
        return back()
            ->withInput()
            ->with([
                'message' => $message
            ]);
    }

    public function logout()
    {

        Session::put('login_2FA',false);
        Session::put('login_email_2FA',false);
        auth()->logout();
        return redirect()->route('login');
    }
}
