<?php


use App\Notifications\membershipwelcomeNotification;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\ProfileController;






Route::get('test2',[\App\Http\Controllers\railzController::class,'sendTransaction2']);

Route::group(function (){
        Route::get('/', [LoginController::class, 'index'])->name('login');
        Route::get('login', [LoginController::class, 'index']);
        Route::get('landing', function (){
            return view('pages.landing');
        });
        Route::post('login', [LoginController::class, 'postLogin']);
        Route::get('logout', [LoginController::class, 'logout']);
        Route::resource('register', RegisterController::class);
        Route::get('verify-email/{code}', [RegisterController::class, 'verify']);
    });
Route::middleware('auth')
    ->group(function () {
        Route::resource('profile', ProfileController::class);
    });


Route::get('change-password', [ProfileController::class, 'changePassword']);
Route::post('change-password', [ProfileController::class, 'changePasswordPost'])->name('changePasswordPost');






    include ('setting.php');





//include ('mybills.php');
//include ('payee.php');
//include ('contact.php');
//include ('shared-bills.php');
include ('auth.php');



