<?php

Route::middleware('auth')
->prefix('setting/notifications')
->group(function () {
Route::get('index',[\App\Http\Controllers\settingController::class,'index'])->name("setting.notification.index");
Route::post('update-column',[\App\Http\Controllers\settingController::class,'update_column'])->name("setting.notification.update_column");
});
