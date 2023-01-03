<?php
Route::prefix('user')
    ->middleware('auth:api')
    ->group(function () {
        Route::any('delete-account', [\App\Http\Controllers\UserController::class, 'delete_account']);

    });
