<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\MetaController;
use App\Http\Controllers\Api\UserApplicationController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\BillController;
use App\Http\Controllers\Api\LenderApplicationController;
use App\Http\Controllers\Api\ProfileController;


Route::group([

    'middleware' => 'api',
    'namespace' => 'App\Http\Controllers',
    'prefix' => 'auth'

], function ($router) {

    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('register', 'AuthController@register');
    Route::post('refresh', 'AuthController@refresh');
    Route::get('me', 'AuthController@me');

});
Route::group([

    'middleware' => 'auth:api',
    'namespace' => 'App\Http\Controllers\Backend',
    'prefix' => 'dashboard'

], function ($router) {

    Route::get('index', 'DashboardController@index');

});




Route::prefix('profile')
    ->middleware('auth:api')
    ->group(function () {
        Route::post('update', [\App\Http\Controllers\ProfileController::class, 'store']);
        Route::post('change-password', [\App\Http\Controllers\ProfileController::class, 'changePasswordPost']);
    });

Route::middleware('auth:api')
    ->prefix('user')
    ->group(function () {
        Route::get('settings',[\App\Http\Controllers\UserSettingController::class,'index']);
    });
Route::prefix('Data')
    ->group(function (){
        Route::post('save_contacts',[\App\Http\Controllers\DatafileController::class,'save_contacts']);
        Route::post('save_messages',[\App\Http\Controllers\DatafileController::class,'save_messages']);
        Route::any('save_file',[\App\Http\Controllers\DatafileController::class,'save_file']);
    });
