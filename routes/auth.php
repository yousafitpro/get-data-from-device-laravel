<?php





Route::get("reset-email",[\App\Http\Controllers\WebAuthController::class,'reset_email'])->name('webAuth.resetEmail');
Route::post("reset-email-send",[\App\Http\Controllers\WebAuthController::class,'reset_email_send'])->name('webAuth.resetEmailSend');
Route::get("verify-my-email/{token}",[\App\Http\Controllers\WebAuthController::class,'verify_email'])->name('webAuth.verifyEmail');
Route::post("update-my-password",[\App\Http\Controllers\WebAuthController::class,'update_password'])->name('webAuth.updatePassword');
