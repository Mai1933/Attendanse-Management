<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// 一般ユーザー
Route::get('/login', [UserController::class, 'generalLogin']);

Route::get('/register', [UserController::class, 'generalRegister']);

Route::get('/attendance/list', [UserController::class, 'generalList']);

Route::get('/attendance/id', [UserController::class, 'generalWorkDetail']);

Route::get('/wait', [UserController::class, 'checkWait']);

Route::get('/stamp_correction_request/list', [UserController::class, 'applicationsList']);

Route::get('/attendance', [UserController::class, 'attendance']);

Route::get('/attendance2', [UserController::class, 'attendance2']);

Route::get('/attendance3', [UserController::class, 'attendance3']);

Route::get('/attendance4', [UserController::class, 'attendance4']);

// 管理者
Route::get('/admin/login', [UserController::class, 'adminLogin']);

Route::get('/admin/attendance/list', [UserController::class, 'adminList']);

Route::get('/admin/staff/list', [UserController::class, 'usersList']);

Route::get('/admin/attendance/staff/id', [UserController::class, 'individualWorks']);

Route::get('/admin/attendance/id', [UserController::class, 'adminWorkDetail']);

Route::get('/admin/stamp_correction_request/list', [UserController::class, 'adminApplicationsList']);

Route::get('/stamp_correction_request/approve', [UserController::class, 'applicationDetail']);



