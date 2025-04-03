<?php

namespace App\Http\Controllers;

use App\Models\Work;
use App\Models\Breaking;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TimeController extends Controller
{
    public function attendance()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->withErrors(['login' => 'ユーザーが認証されていません。']);
        }
        $date = date('Y-m-d');
        $week = ['日', '月', '火', '水', '木', '金', '土'];
        $dayOfWeek = $week[date('w')];
        $time = date('H:i');

        $work = Work::where('date', $date)->where('user_id', $user->id)->first();
        if (!$work) {
            return view('work_before', compact('date', 'dayOfWeek', 'time'));
        }

        $breaks = Breaking::where('work_id', $work->id)->get();
        if (
            $breaks->isEmpty() &&
            $work->end_time === null
        ) {
            return view('work_after', compact('date', 'dayOfWeek', 'time'));
        } elseif (
            $breaks->isEmpty() &&
            $work->end_time !== null
        ) {
            return view('work_finish', compact('date', 'dayOfWeek', 'time'));
        }
        $firstBreak = $breaks->first();
        $lastBreak = $breaks->last();

        if ($work->start_time !== null) {
            if (
                $firstBreak === null &&
                $work->end_time === null
            ) {
                return view('work_after', compact('date', 'dayOfWeek', 'time'));
            } elseif (
                $lastBreak !== null &&
                $lastBreak->start_time !== null &&
                $lastBreak->end_time === null &&
                $work->end_time === null
            ) {
                return view('work_break', compact('date', 'dayOfWeek', 'time'));
            } elseif (
                $lastBreak !== null &&
                $lastBreak->start_time !== null &&
                $lastBreak->end_time === null &&
                $work->end_time === null
            ) {
                return view('work_break', compact('date', 'dayOfWeek', 'time'));
            } elseif (
                $lastBreak !== null &&
                $lastBreak->start_time !== null &&
                $lastBreak->end_time !== null &&
                $work->end_time === null
            ) {
                return view('work_after', compact('date', 'dayOfWeek', 'time'));
            } elseif (
                $lastBreak !== null &&
                $lastBreak->start_time !== null &&
                $lastBreak->end_time !== null &&
                $work->end_time !== null
            ) {
                return view('work_finish', compact('date', 'dayOfWeek', 'time'));
            }
        }
    }

    public function attendanceStore()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->withErrors(['login' => 'ユーザーが認証されていません。']);
        }

        $date = date('Y-m-d');
        $week = ['日', '月', '火', '水', '木', '金', '土'];
        $dayOfWeek = $week[date('w')];
        $time = date('H:i');

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
            return redirect()->route('login')->withErrors(['login' => 'ユーザーが認証されていません。']);
        }

        $date = date('Y-m-d');
        $week = ['日', '月', '火', '水', '木', '金', '土'];
        $dayOfWeek = $week[date('w')];
        $time = date('H:i');

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
            return redirect()->route('login')->withErrors(['login' => 'ユーザーが認証されていません。']);
        }

        $date = date('Y-m-d');
        $week = ['日', '月', '火', '水', '木', '金', '土'];
        $dayOfWeek = $week[date('w')];
        $time = date('H:i');

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
            return redirect()->route('login')->withErrors(['login' => 'ユーザーが認証されていません。']);
        }
        $date = date('Y-m-d');
        $week = ['日', '月', '火', '水', '木', '金', '土'];
        $dayOfWeek = $week[date('w')];
        $time = date('H:i');

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
