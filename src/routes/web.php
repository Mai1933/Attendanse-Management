<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// 管理者
Route::get('/admin/login', [UserController::class, 'adminLogin']);

Route::get('/admin/attendance/list', [UserController::class, 'adminList']);

Route::get('/admin/staff/list', [UserController::class, 'usersList']);

Route::get('/admin/attendance/staff/{id}', [UserController::class, 'individualWorks']);

Route::get('/attendance/{id}', [UserController::class, 'adminWorkDetail']);

Route::get('/stamp_correction_request/approve', [UserController::class, 'applicationDetail']);

// 一般ユーザー
Route::get('/login', [UserController::class, 'generalLogin']);

Route::get('/register', [UserController::class, 'generalRegister']);

Route::get('/attendance/list', [UserController::class, 'generalList']);

