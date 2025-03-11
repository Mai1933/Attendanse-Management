<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
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
Route::get('/login', [UserController::class, 'generalLogin'])->name('login');

Route::post('/login', [UserController::class, 'loginStore']);

Route::get('/register', [UserController::class, 'generalRegister']);

Route::post('/register', [UserController::class, 'registerStore'])
    ->middleware(['guest:' . config('fortify.guard')])
    ->name('register.store');

Route::get('/attendance/list', [UserController::class, 'generalList']);

Route::get('/attendance/id', [UserController::class, 'generalWorkDetail']);

Route::get('/wait', [UserController::class, 'checkWait']);

Route::get('/stamp_correction_request/list', [UserController::class, 'applicationsList']);

Route::get('/attendance', [UserController::class, 'attendance']);

Route::get('/attendance2', [UserController::class, 'attendance2']);

Route::get('/attendance3', [UserController::class, 'attendance3']);

Route::get('/attendance4', [UserController::class, 'attendance4']);

// 管理者
Route::get('/admin/login', [UserController::class, 'adminLogin'])->name('admin.login');

Route::post('/admin/login', [UserController::class, 'adminLoginStore']);

Route::get('/admin/attendance/list', [UserController::class, 'adminList'])->name('admin.list');

Route::get('/admin/staff/list', [UserController::class, 'usersList']);

Route::get('/admin/attendance/staff/id', [UserController::class, 'individualWorks']);

Route::get('/admin/attendance/id', [UserController::class, 'adminWorkDetail']);

Route::get('/admin/stamp_correction_request/list', [UserController::class, 'adminApplicationsList']);

Route::get('/stamp_correction_request/approve', [UserController::class, 'applicationDetail']);



