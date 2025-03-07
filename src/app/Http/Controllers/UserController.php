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

    public function adminApplicationsList()
    {
        return view('admin_applications');
    }

    public function applicationDetail()
    {
        return view('approve');
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

    public function generalWorkDetail()
    {
        return view('general_detail');
    }

    public function checkWait()
    {
        return view('general_detail-wait');
    }

    public function applicationsList()
    {
        return view('general_applications');
    }

    public function attendance()
    {
        return view('work_before');
    }

    public function attendance2()
    {
        return view('work_after');
    }

    public function attendance3()
    {
        return view('work_break');
    }

    public function attendance4()
    {
        return view('work_finish');
    }
}
