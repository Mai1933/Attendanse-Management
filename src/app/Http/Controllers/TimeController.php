<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Work;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Contracts\RegisterViewResponse;
use Laravel\Fortify\Fortify;
use App\Actions\Fortify\CreateNewUser;
use App\Models\GeneralUser;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\ExhibitionRequest;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Category;
use App\Models\User;
use App\Models\Purchase;
use App\Http\Requests\AddressRequest;
use App\Http\Requests\ProfileRequest;
use Laravel\Fortify\Contracts\LoginResponse;
use App\Responses\AdminLoginResponse;
use Illuminate\Routing\Pipeline;
use Laravel\Fortify\Actions\AttemptToAuthenticate;
use Laravel\Fortify\Actions\CanonicalizeUsername;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Features;
use App\Responses\RegisterResponse;



class TimeController extends Controller
{
    public function attendance()
    {
        $user = Auth::user();
        if (!$user) {
            Log::info('ログインに失敗しました');
        } else {
            Log::info('ユーザーが認証されました: ' . $user->email);
        }
        $date = date('Y年m月d日');
        $week = ['日', '月', '火', '水', '木', '金', '土'];
        $dayOfWeek = $week[date('w')];
        $time = date('H:i');

        return view('work_before', compact('date', 'dayOfWeek', 'time'));
    }

    public function attendanceStore()
    {
        $user = Auth::user();
        if (!$user) {
            Log::info('ログインに失敗しました');
        }

        $date = session('date');
        $dayOfWeek = session('dayOfWeek');
        $time = session('time');

        $work = new Work();

        $work->user_id = $user->id;
        $work->date = date('Y-m-d');
        $work->start_time = date('H:i:s');

        $work->save();
        return view('work_after', compact('date', 'dayOfWeek', 'time'));
    }

    public function attendance2()
    {
        $date = session('date');
        $dayOfWeek = session('dayOfWeek');
        $time = session('time');
        return view('work_after', compact('date', 'dayOfWeek', 'time'));
    }

    public function attendance3()
    {
        $date = session('date');
        $dayOfWeek = session('dayOfWeek');
        $time = session('time');
        return view('work_break', compact('date', 'dayOfWeek', 'time'));
    }

    public function attendance4()
    {
        $date = session('date');
        $dayOfWeek = session('dayOfWeek');
        $time = session('time');
        return view('work_finish', compact('date', 'dayOfWeek', 'time'));
    }
}
