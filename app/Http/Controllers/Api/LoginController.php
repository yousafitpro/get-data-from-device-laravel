<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class LoginController extends Controller
{

    public function postLogin(Request $request)
    {
        $error = 'yes';
        $message = 'Wrongs Credentials';
        $data = $request->only('email', 'password');
        $email = $request->email;
        $user = User::where('email', $email)->with('roles')->first();
        if ($user && !$user->is_deleted) {
            if ($user->email_verified_at) {
                if (auth()->attempt($data)) {
                    $error = 'no';
                    $message = 'success';
                }
            } else {
                $message = 'Email is not verified';
            }
        }

        return response()
            ->json([
                'error' => $error,
                'message' => $message,
                'data' => $user
            ]);
    }

    public function logout()
    {
        auth()->logout();
        return redirect()->route('login');
    }

}
