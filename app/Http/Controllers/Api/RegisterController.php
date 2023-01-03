<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\RegisterMail;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Mail;
use Illuminate\Support\Facades\Validator;


class RegisterController extends Controller
{


    public function store(Request $request)
    {
        $error = 'yes';
        $errors = [];
        $message = 'something went wrong';

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'plan' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
        } else {
            $data = $request->only('email', 'name', 'phone', 'address', 'city', 'zipcode');
            $data['password'] = bcrypt($request->password);
            $data['token'] = str_random(80);
            $data['package_id'] = $request->plan;
            $user = User::create($data);
            $role = Role::find(3);
            $user->assignRole($role->name);

            Mail::to($request->email)->send(new RegisterMail($user));
            $error = 'no';
            $message = 'Please verify your account. check your email box';
        }

        return response()->json([
            'error' => $error,
            'errors' => $errors,
            'message' => $message
        ]);

    }

    public function verify($code)
    {
        $error = 'yes';
        $message = 'code is not correct';

        $data = explode('-', $code);
        $user = User::where('id', $data[1])->where('token', $data[0])->first();
        if ($user) {
            $user->email_verified_at = now();
            $user->save();
            $error = 'no';
            $message = 'Welcome! Account is verified';

        }

        return response()->json([
            'error' => $error,
            'message' => $message
        ]);
    }

}
