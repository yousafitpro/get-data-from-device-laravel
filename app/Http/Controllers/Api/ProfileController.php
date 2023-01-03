<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\User;
use App\Models\VendorBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Mail;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public $SRC = 'images/profile/';

    public function changePasswordPost(Request $request)
    {
        $message = 'Old password is incorrect';
        $error = 'yes';

        $user = User::find($request->user_id);

        if ($user && Hash::check($request->old_password, $user->password)) {
            $user->password = bcrypt($request->password);
            $user->save();
            $error = 'no';
            $message = "Password successfully changed";
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
            'record' => []
        ]);

    }

    public function profileUpdate(Request $request)
    {
        $user = User::find($request->user_id);

        $data = $request->only('name', 'phone', 'city', 'address', 'zipcode', 'about', 'bank_account_number');
        $image = $request->file('avatar');

        if ($image) {
            $data['avatar'] = saveImage($image, $this->SRC);
        }

        $user->update($data);

        return response()->json([
            'error' => 'no',
            'message' => 'Profile Updated',
            'record' => $user
        ]);
    }

}
