<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Work;
use App\Models\Breaking;
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
            Log::info('Authにおいてログインに失敗しました');
        } else {
            Log::info('ユーザーが認証されました: ' . $user->email);
        }
        $date = date('Y年m月d日');
        $week = ['日', '月', '火', '水', '木', '金', '土'];
        $dayOfWeek = $week[date('w')];
        $time = date('H:i');

        session(['date' => $date]);
        session(['dayOfWeek' => $dayOfWeek]);
        session(['time' => $time]);

        return view('work_before', compact('date', 'dayOfWeek', 'time'));
    }

    public function attendanceStore()
    {
        $user = Auth::user();
        if (!$user) {
            Log::info('Authにおいてログインに失敗しました');
        }

        $date = session('date');
        $dayOfWeek = session('dayOfWeek');
        $time = session('time');

        $workData = Work::where('user_id', $user->id)->get();
        $today = date('Y-m-d');
        $work = $workData->filter(function ($item) use ($today) {
            return $item->date === $today;
        })->values();

        if ($work->isEmpty()) {
            $newWork = new Work();

            $newWork->user_id = $user->id;
            $newWork->date = $today;
            $newWork->start_time = date('H:i:s');
            $newWork->save();
        } elseif ($work->first()->start_time !== null) {
            return redirect('/attendance')->withErrors(['attendance' => '本日は出勤済みです。']);
        } else {
            $newWork = new Work();

            $newWork->user_id = $user->id;
            $newWork->date = $today;
            $newWork->start_time = date('H:i:s');
            $newWork->save();
        }

        return view('work_after', compact('date', 'dayOfWeek', 'time'));
    }

    public function breakingStore()
    {
        $user = Auth::user();
        if (!$user) {
            Log::info('Authにおいてログインに失敗しました');
        }

        $date = session('date');
        $dayOfWeek = session('dayOfWeek');
        $time = session('time');

        $workData = Work::where('user_id', $user->id)->get();
        $today = date('Y-m-d');
        $work = $workData->filter(function ($item) use ($today) {
            return $item->date === $today;
        })->values();

        if ($work->isEmpty()) {
            Log::info('仕事の開始時間が登録されていません');
        }


        $breaking = new Breaking();

        $breaking->user_id = $user->id;
        $breaking->work_id = $work->first()->id;
        $breaking->start_time = date('H:i:s');

        $breaking->save();
        return view('work_break', compact('date', 'dayOfWeek', 'time'));
    }

    public function returningStore()
    {
        $user = Auth::user();
        if (!$user) {
            Log::info('Authにおいてログインに失敗しました');
        }

        $date = session('date');
        $dayOfWeek = session('dayOfWeek');
        $time = session('time');

        $breakingData = Breaking::where('user_id', $user->id)->get();
        $breaking = $breakingData->filter(function ($item) {
            return $item->end_time === null;
        })->first();

        $breaking->end_time = date('H:i:s');
        $breaking->save();
        return view('work_after', compact('date', 'dayOfWeek', 'time'));
    }

    public function completeStore()
    {
        $user = Auth::user();
        if (!$user) {
            Log::info('Authにおいてログインに失敗しました');
        }

        $date = session('date');
        $dayOfWeek = session('dayOfWeek');
        $time = session('time');

        $workData = Work::where('user_id', $user->id)->get();
        $today = date('Y-m-d');
        $work = $workData->filter(function ($item) use ($today) {
            return $item->date === $today;
        })->first();

        $work->end_time = date('H:i:s');
        $work->save();
        return view('work_finish', compact('date', 'dayOfWeek', 'time'));
    }
}
