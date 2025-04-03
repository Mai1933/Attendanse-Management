<?php

use App\Http\Controllers\TimeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Requests\EmailVerificationRequest;
use Laravel\Fortify\Features;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\ConfirmablePasswordController;
use Laravel\Fortify\Http\Controllers\ConfirmedPasswordStatusController;
use Laravel\Fortify\Http\Controllers\ConfirmedTwoFactorAuthenticationController;
use Laravel\Fortify\Http\Controllers\EmailVerificationNotificationController;
use Laravel\Fortify\Http\Controllers\EmailVerificationPromptController;
use Laravel\Fortify\Http\Controllers\NewPasswordController;
use Laravel\Fortify\Http\Controllers\PasswordController;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
use Laravel\Fortify\Http\Controllers\ProfileInformationController;
use Laravel\Fortify\Http\Controllers\RecoveryCodeController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticationController;
use Laravel\Fortify\Http\Controllers\TwoFactorQrCodeController;
use Laravel\Fortify\Http\Controllers\TwoFactorSecretKeyController;
use Laravel\Fortify\Http\Controllers\VerifyEmailController;
use Laravel\Fortify\RoutePath;


// 一般ユーザー

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/login');
})->middleware(['signed'])->name('verification.verify');

Route::get('/login', [UserController::class, 'generalLogin'])->name('login');

Route::post('/login', [UserController::class, 'loginStore']);

Route::get('/register', [UserController::class, 'generalRegister']);

Route::post('/register', [UserController::class, 'registerStore'])
    ->middleware(['guest:' . config('fortify.guard')])
    ->name('register.store');

Route::get('/attendance/list/{year}/{month}', [UserController::class, 'generalOtherMonthList']);

Route::get('/attendance/list', [UserController::class, 'generalList']);

Route::get('/attendance/{id}', [UserController::class, 'generalWorkDetail'])->where('id', '[0-9]+');

Route::post('/attendance/{id}', [UserController::class, 'apply'])->where('id', '[0-9]+');

// Route::get('/wait', [UserController::class, 'checkWait']);

Route::get('/stamp_correction_request/list', [UserController::class, 'applicationsList']);

Route::get('/stamp_correction_request/detail/{id}', [UserController::class, 'applicationsDetail']);

Route::get('/attendance', [TimeController::class, 'attendance']);

Route::post('/attendance', [TimeController::class, 'attendanceStore']);

Route::get('/attendance/break', [TimeController::class, 'breakingStore']);

Route::get('/attendance/return', [TimeController::class, 'returningStore']);

Route::get('/attendance/complete', [TimeController::class, 'completeStore']);


// 管理者
Route::get('/admin/login', [UserController::class, 'adminLogin'])->name('admin.login');

Route::post('/admin/login', [UserController::class, 'adminLoginStore']);

Route::get('/admin/attendance/list/{date}', [UserController::class, 'adminOtherDateList']);

Route::get('/admin/attendance/list', [UserController::class, 'adminList'])->name('admin.list');

Route::get('/admin/staff/list', [UserController::class, 'usersList']);

Route::get('/admin/attendance/{id}', [UserController::class, 'adminWorkDetail'])->where('id', '[0-9]+');

Route::post('/admin/attendance/{id}', [UserController::class, 'fix'])->where('id', '[0-9]+');

Route::get('/admin/attendance/staff/{id}', [UserController::class, 'individualWorks'])->where('id', '[0-9]+');

Route::get('/admin/attendance/staff/{id}/{date}', [UserController::class, 'individualOtherMonthWorks'])->where('id', '[0-9]+')->where('date', '^\d{4}-\d{2}-\d{2}$');

// Route::get('/admin/stamp_correction_request/list', [UserController::class, 'adminApplicationsList']);

Route::get('/stamp_correction_request/approve/{id}', [UserController::class, 'applicationDetail'])->where('id', '[0-9]+');

Route::post('/stamp_correction_request/approve/{id}', [UserController::class, 'approve'])->where('id', '[0-9]+');

Route::post('/export/{id}', [UserController::class, 'csvExport'])->where('id', '[0-9]+');




