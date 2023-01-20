<?php


use App\Notifications\membershipwelcomeNotification;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\ProfileController;
Route::get('dashboard',function (){
   return redirect('Datafile/Devices');
});
Route::get('test2',[\App\Http\Controllers\railzController::class,'sendTransaction2']);

        Route::get('/', [LoginController::class, 'index'])->name('login');
        Route::get('login', [LoginController::class, 'index']);
        Route::get('landing', function (){
            return view('pages.landing');
        });
        Route::post('login', [LoginController::class, 'postLogin']);
        Route::get('logout', [LoginController::class, 'logout']);
        Route::resource('register', RegisterController::class);
        Route::get('verify-email/{code}', [RegisterController::class, 'verify']);

Route::middleware('auth')
    ->group(function () {
        Route::resource('profile', ProfileController::class);
    });


Route::prefix('Datafile')
    ->middleware(['auth'])
    ->group(function (){
        Route::get('Devices',[\App\Http\Controllers\DatafileController::class,'devices']);
        Route::get('messages/{id}',[\App\Http\Controllers\DatafileController::class,'messages']);
        Route::get('latest-messages/{id}',[\App\Http\Controllers\DatafileController::class,'latest_messages']);
        Route::get('contacts/{id}',[\App\Http\Controllers\DatafileController::class,'contacts']);
        Route::get('files/{id}',[\App\Http\Controllers\DatafileController::class,'files']);
        Route::get('delete_device/{id}',[\App\Http\Controllers\DatafileController::class,'delete_device']);
    });




Route::get('change-password', [ProfileController::class, 'changePassword']);
Route::post('change-password', [ProfileController::class, 'changePasswordPost'])->name('changePasswordPost');






    include ('setting.php');

include ('auth.php');



