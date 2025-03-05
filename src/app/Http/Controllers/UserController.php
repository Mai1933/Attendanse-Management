<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function adminLogin()
    {
        return view('admin_login');
    }

    public function adminList()
    {
        return view('admin_attendance');
    }

    public function usersList()
    {
        return view('users_list');
    }

    public function individualWorks()
    {
        return view('users_attendance_list');
    }

    public function adminWorkDetail()
    {
        return view('admin_detail');
    }

    public function applicationDetail()
    {
        return view('admin_applications');
    }

    public function generalLogin()
    {
        return view('general_login');
    }

    public function generalRegister()
    {
        return view('general_register');
    }

    public function generalList()
    {
        return view('general_attendance');
    }
}
