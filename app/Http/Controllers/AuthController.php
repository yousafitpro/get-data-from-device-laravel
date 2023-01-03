<?php

namespace App\Http\Controllers;

use App\Mail\RegisterMail;
use App\Models\etransfer;
use App\Models\notificationSetting;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }

    /**
     * Get a JWT token via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {

if (User::where('email',$request->email)->exists())
{
    return response()->json(['message'=>"This Email Already Registered."],200);
}


        try {
            $data = $request->only('email', 'name', 'phone','business_name', 'address', 'city', 'zipcode','package_id');
//        $data['email_verified_at'] = now();
            $data['password'] = bcrypt($request->password);
            $user = User::create($data);

            $role = Role::find(3);
            $user->assignRole($role->name);
            \Mail::to($request->email)->send(new RegisterMail($user));

//            mailController::sendMail(Config::get('myconfig.mail.admin_email'),"New User Registered successfully",$request,'emails.admin.newUserSignup');
//            mailController::sendMail($request->email,"Congratulation! successfully registered",$request,'emails.user.signup');
            return response()->json(['message'=>"Please verify your account. check your email box"],200);
        }
        catch (\Exception $e) {
            return response()->json(['error'=>$e->getMessage()],409);
        }

        ;


    }
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if ($token = Auth::guard('api')->attempt($credentials)) {
            ENVController::beforeLogin(\auth('api')->user());

            return $this->respondWithToken($token);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {

        return response()->json(self::userInfo());
    }

    /**
     * Log the user out (Invalidate the token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        Auth::guard('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(Auth::guard('api')->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' =>self::userInfo(),
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60
        ]);
    }
public static function userInfo($gaurd='api')
{
    $user=auth($gaurd)->user();
    $user->image=$user->avatar();
    $user->setting=UserSetting::where('user_id',$user->id)->first();
    $user->etransfer=etransfer::where('user_id',$user->id)->first();
//    $user->notificationSetting=notificationSetting::where('user_id',$user->id)->get();
    return $user;
}
    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */

}
