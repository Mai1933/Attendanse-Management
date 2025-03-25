<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ApplyRequest;
use DateTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Contracts\RegisterViewResponse;
use Laravel\Fortify\Fortify;
use App\Actions\Fortify\CreateNewUser;
use App\Models\User;
use App\Models\Work;
use App\Models\Breaking;
use App\Models\BreakingApplication;
use App\Models\WorkingApplication;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\ExhibitionRequest;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Category;
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
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;



class UserController extends Controller
{
    public function __invoke(Request $request)
    {
        return $request->user()->hasVerifiedEmail()
            ? app(RedirectAsIntended::class, ['name' => 'email-verification'])
            : app(VerifyEmailViewResponse::class);
    }

    public function adminLogin()
    {
        return view('admin_login');
    }

    public function adminLoginStore(LoginRequest $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            if ($user->role !== 'admin') {
                Auth::logout();
                return redirect('/login')->withErrors(['login' => '管理者以外はログインできません。']);
            }
            return $this->loginPipeline($request)->then(function ($request) {
                return redirect()->route('admin.list');
            });
        }

        return redirect()->route('login')->withErrors(['login' => 'ログイン情報が間違っています']);

    }

    public function adminList()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->withErrors(['login' => 'ユーザーが認証されていません。']);
        }
        if ($user->role !== 'admin') {
            Auth::logout();
            return redirect('/login')->withErrors(['login' => '管理者以外はログインできません。']);
        }
        $date = date('Y/m/d');
        $previousDate = new DateTime($date)->modify('-1 day')->format('Y-m-d');
        $nextDate = new DateTime($date)->modify('+1 day')->format('Y-m-d');

        $todaysWorks = Work::where('date', date('Y-m-d'))->get();
        $user = [];
        $totalBreakTimes = [];
        $formattedBreakTimes = [];
        $formattedWorkTimes = [];

        foreach ($todaysWorks as $todaysWork) {
            $user[$todaysWork->id] = User::where('id', $todaysWork->user_id)->first();
            $breakings = Breaking::where('work_id', $todaysWork->id)->get();

            $totalBreakTime = 0;
            $totalBreakTimes[$todaysWork->id] = 0;
            foreach ($breakings as $breaking) {
                if ($breaking->start_time && $breaking->end_time) {
                    $startTime = strtotime($breaking->start_time);
                    $endTime = strtotime($breaking->end_time);
                    $totalBreakTime += ($endTime - $startTime);
                }
            }
            $totalBreakTimes[$todaysWork->id] = $totalBreakTime;
            $breakHours = floor($totalBreakTimes[$todaysWork->id] / 3600);
            $breakMinutes = floor(($totalBreakTimes[$todaysWork->id] % 3600) / 60);
            $formattedBreakTimes[$todaysWork->id] = sprintf('%2d:%02d', $breakHours, $breakMinutes);

            if ($todaysWork->start_time && $todaysWork->end_time) {
                $workStartTime = strtotime($todaysWork->start_time);
                $workEndTime = strtotime($todaysWork->end_time);
                $totalWorkTime = ($workEndTime - $workStartTime) - $totalBreakTimes[$todaysWork->id];
                $workHours = floor($totalWorkTime / 3600);
                $workMinutes = floor(($totalWorkTime % 3600) / 60);
                $formattedWorkTimes[$todaysWork->id] = sprintf('%2d:%02d', $workHours, $workMinutes);
            } else {
                $formattedWorkTimes[$todaysWork->id] = '';
            }
        }

        return view('admin_attendance', compact('date', 'previousDate', 'nextDate', 'user', 'todaysWorks', 'formattedBreakTimes', 'formattedWorkTimes'));
    }

    public function adminOtherDateList($date)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->withErrors(['login' => 'ユーザーが認証されていません。']);
        }
        if ($user->role !== 'admin') {
            Auth::logout();
            return redirect('/login')->withErrors(['login' => '管理者以外は閲覧できません。']);
        }
        $previousDate = new DateTime($date)->modify('-1 day')->format('Y-m-d');
        $nextDate = new DateTime($date)->modify('+1 day')->format('Y-m-d');

        $todaysWorks = Work::where('date', $date)->get();
        $user = [];
        $totalBreakTimes = [];
        $formattedBreakTimes = [];
        $formattedWorkTimes = [];


        foreach ($todaysWorks as $todaysWork) {
            $user[$todaysWork->id] = User::where('id', $todaysWork->user_id)->first();
            $breakings = Breaking::where('work_id', $todaysWork->id)->get();

            $totalBreakTime = 0;
            foreach ($breakings as $breaking) {
                if ($breaking->start_time && $breaking->end_time) {
                    $startTime = strtotime($breaking->start_time);
                    $endTime = strtotime($breaking->end_time);
                    $totalBreakTime += ($endTime - $startTime);
                }
            }
            $totalBreakTimes[$todaysWork->id] = $totalBreakTime;
            $breakHours = floor($totalBreakTimes[$todaysWork->id] / 3600);
            $breakMinutes = floor(($totalBreakTimes[$todaysWork->id] % 3600) / 60);
            $formattedBreakTimes[$todaysWork->id] = sprintf('%2d:%02d', $breakHours, $breakMinutes);

            if ($todaysWork->start_time && $todaysWork->end_time) {
                $workStartTime = strtotime($todaysWork->start_time);
                $workEndTime = strtotime($todaysWork->end_time);
                $totalWorkTime = ($workEndTime - $workStartTime) - $totalBreakTimes[$todaysWork->id];
                $workHours = floor($totalWorkTime / 3600);
                $workMinutes = floor(($totalWorkTime % 3600) / 60);
                $formattedWorkTimes[$todaysWork->id] = sprintf('%2d:%02d', $workHours, $workMinutes);
            } else {
                $formattedWorkTimes[$todaysWork->id] = '';
            }
        }

        return view('admin_attendance', compact('date', 'previousDate', 'nextDate', 'user', 'todaysWorks', 'formattedBreakTimes', 'formattedWorkTimes'));
    }

    public function adminWorkDetail($id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->withErrors(['login' => 'ユーザーが認証されていません。']);
        }
        if ($user->role !== 'admin') {
            Auth::logout();
            return redirect('/login')->withErrors(['login' => '管理者以外は閲覧できません。']);
        }
        $work = Work::where('id', $id)->first();
        $user = User::where('id', $work->user_id)->first();
        $year = Carbon::parse($work->date)->format('Y年');
        $date = Carbon::parse($work->date)->format('n月j日');

        $breakings = Breaking::where('work_id', $id)->get();
        return view('admin_detail', compact('user', 'work', 'year', 'date', 'breakings'));
    }

    public function fix(ApplyRequest $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->withErrors(['login' => 'ユーザーが認証されていません。']);
        }
        if ($user->role !== 'admin') {
            Auth::logout();
            return redirect('/login')->withErrors(['login' => '管理者以外は閲覧できません。']);
        }
        $year = trim(str_replace('年', '', $request->year));
        $date = trim(str_replace('月', '-', str_replace('日', '', $request->date)));
        $applyDate = date('Y-m-d', strtotime($year . '-' . $date));

        $work = Work::where('id', $id)->first();
        $work->date = $applyDate;
        $work->start_time = $request->work_start;
        $work->end_time = $request->work_end;
        $work->remarks = $request->remarks;
        $work->save();

        $date = date('Y-m-d', strtotime($work->date));

        $breakings = Breaking::where('work_id', $id)->get();
        foreach ($breakings as $index => $breaking) {
            if (isset($request->break_start[$index]) && isset($request->break_end[$index])) {
                $breaking->start_time = $request->break_start[$index];
                $breaking->end_time = $request->break_end[$index];
                $breaking->save();
            }
        }
        return redirect('/admin/attendance/list/' . $date);
    }

    public function usersList()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->withErrors(['login' => 'ユーザーが認証されていません。']);
        }
        if ($user->role !== 'admin') {
            Auth::logout();
            return redirect('/login')->withErrors(['login' => '管理者以外は閲覧できません。']);
        }
        $users = User::where('role', null)->get();
        return view('users_list', compact('users'));
    }

    public function individualWorks($id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->withErrors(['login' => 'ユーザーが認証されていません。']);
        }
        if ($user->role !== 'admin') {
            Auth::logout();
            return redirect('/login')->withErrors(['login' => '管理者以外は閲覧できません。']);
        }
        $user = User::where('id', $id)->first();
        $month = date('Y-m');
        $date = date('Y/m/d');
        $previousMonth = (new DateTime($month . '-01'))->modify('-1 month')->format('Y-m-d');
        $nextMonth = (new DateTime($month . '-01'))->modify('+1 month')->format('Y-m-d');
        $week = ['日', '月', '火', '水', '木', '金', '土'];

        $workData = Work::where('user_id', $id)->get();
        $works = $workData->filter(function ($item) use ($month) {
            return strpos($item->date, $month) === 0;
        })->values();
        Log::info('Works: ' . $works);

        $workDayOfWeek = [];
        $formattedBreakTimes = [];
        $formattedWorkTimes = [];
        $totalBreakTimes = [];
        $totalWorkTimes = [];

        foreach ($works as $work) {
            $workDayOfWeek[$work->id] = $week[date('w', strtotime($work->date))];
            $breakings = Breaking::where('work_id', $work->id)->get();

            $totalBreakTime = 0;
            foreach ($breakings as $breaking) {
                if ($breaking->start_time && $breaking->end_time) {
                    $startTime = strtotime($breaking->start_time);
                    $endTime = strtotime($breaking->end_time);
                    $totalBreakTime += ($endTime - $startTime);
                }
            }
            $totalBreakTimes[$work->id] = $totalBreakTime;
            $breakHours = floor($totalBreakTimes[$work->id] / 3600);
            $breakMinutes = floor(($totalBreakTimes[$work->id] % 3600) / 60);
            $formattedBreakTimes[$work->id] = sprintf('%2d:%02d', $breakHours, $breakMinutes);

            if ($work->start_time && $work->end_time) {
                $workStartTime = strtotime($work->start_time);
                $workEndTime = strtotime($work->end_time);
                $totalWorkTime = ($workEndTime - $workStartTime) - $totalBreakTimes[$work->id];
                $workHours = floor($totalWorkTime / 3600);
                $workMinutes = floor(($totalWorkTime % 3600) / 60);
                $formattedWorkTimes[$work->id] = sprintf('%2d:%02d', $workHours, $workMinutes);
            } else {
                $formattedWorkTimes[$work->id] = '';
            }
        }

        return view('users_attendance_list', compact('user', 'date', 'previousMonth', 'nextMonth', 'works', 'workDayOfWeek', 'formattedBreakTimes', 'formattedWorkTimes'));
    }

    public function individualOtherMonthWorks($id, $date)
    {
        $userData = Auth::user();
        if (!$userData) {
            return redirect()->route('login')->withErrors(['login' => 'ユーザーが認証されていません。']);
        }
        if ($userData->role !== 'admin') {
            Auth::logout();
            return redirect('/login')->withErrors(['login' => '管理者以外は閲覧できません。']);
        }
        $user = User::where('id', $id)->first();
        $month = date('Y-m', strtotime($date));
        $previousMonth = (new DateTime($month . '-01'))->modify('-1 month')->format('Y-m-d');
        $nextMonth = (new DateTime($month . '-01'))->modify('+1 month')->format('Y-m-d');
        $week = ['日', '月', '火', '水', '木', '金', '土'];

        $workData = Work::where('user_id', $id)->get();
        $works = $workData->filter(function ($item) use ($month) {
            return strpos($item->date, $month) === 0;
        })->values();

        $workDayOfWeek = [];
        $formattedBreakTimes = [];
        $formattedWorkTimes = [];
        $totalBreakTimes = [];
        $totalWorkTimes = [];

        foreach ($works as $work) {
            $workDayOfWeek[$work->id] = $week[date('w', strtotime($work->date))];
            $breakings = Breaking::where('work_id', $work->id)->get();

            $totalBreakTime = 0;
            foreach ($breakings as $breaking) {
                if ($breaking->start_time && $breaking->end_time) {
                    $startTime = strtotime($breaking->start_time);
                    $endTime = strtotime($breaking->end_time);
                    $totalBreakTime += ($endTime - $startTime);
                }
            }
            $totalBreakTimes[$work->id] = $totalBreakTime;
            $breakHours = floor($totalBreakTimes[$work->id] / 3600);
            $breakMinutes = floor(($totalBreakTimes[$work->id] % 3600) / 60);
            $formattedBreakTimes[$work->id] = sprintf('%2d:%02d', $breakHours, $breakMinutes);

            if ($work->start_time && $work->end_time) {
                $workStartTime = strtotime($work->start_time);
                $workEndTime = strtotime($work->end_time);
                $totalWorkTime = ($workEndTime - $workStartTime) - $totalBreakTimes[$work->id];
                $workHours = floor($totalWorkTime / 3600);
                $workMinutes = floor(($totalWorkTime % 3600) / 60);
                $formattedWorkTimes[$work->id] = sprintf('%2d:%02d', $workHours, $workMinutes);
            } else {
                $formattedWorkTimes[$work->id] = '';
            }
        }

        return view('users_attendance_list', compact('user', 'date', 'previousMonth', 'nextMonth', 'works', 'workDayOfWeek', 'formattedBreakTimes', 'formattedWorkTimes'));
    }

    public function applicationDetail($id)
    {
        $userData = Auth::user();
        if (!$userData) {
            return redirect()->route('login')->withErrors(['login' => 'ユーザーが認証されていません。']);
        }
        if ($userData->role !== 'admin') {
            Auth::logout();
            return redirect('/login')->withErrors(['login' => '管理者以外は閲覧できません。']);
        }
        $application = WorkingApplication::where('id', $id)->first();
        $user = User::where('id', $application->user_id)->first();
        $breakingApplications = BreakingApplication::where('work_id', $application->work_id)->get();
        return view('approve', compact('application', 'user', 'breakingApplications'));
    }

    public function approve(Request $request, $id)
    {
        $userData = Auth::user();
        if (!$userData) {
            return redirect()->route('login')->withErrors(['login' => 'ユーザーが認証されていません。']);
        }
        if ($userData->role !== 'admin') {
            Auth::logout();
            return redirect('/login')->withErrors(['login' => '管理者以外は閲覧できません。']);
        }
        $year = trim(str_replace('年', '', $request->year));
        $date = trim(str_replace('月', '-', str_replace('日', '', $request->date)));
        $applyDate = date('Y-m-d', strtotime($year . '-' . $date));

        $work = Work::where('id', $id)->first();
        $work->date = $applyDate;
        $work->start_time = $request->work_start;
        $work->end_time = $request->work_end;
        $work->remarks = $request->remarks;
        $work->save();

        $application = WorkingApplication::where('work_id', $id)->first();
        $application->status = '承認済み';
        $application->save();

        $date = date('Y-m-d', strtotime($work->date));

        $breakings = Breaking::where('work_id', $id)->get();
        foreach ($breakings as $index => $breaking) {
            if (isset($request->break_start[$index]) && isset($request->break_end[$index])) {
                $breaking->start_time = $request->break_start[$index];
                $breaking->end_time = $request->break_end[$index];
                $breaking->save();
            }
        }
        return redirect('/admin/attendance/list/' . $date);
    }

    public function csvExport(Request $request, $id)
    {
        $user = User::where('id', $id)->first();
        $month = $request->date;
        $week = ['日', '月', '火', '水', '木', '金', '土'];

        $workData = Work::where('user_id', $id)->get();
        $works = $workData->filter(function ($item) use ($month) {
            return strpos($item->date, $month) === 0;
        })->values();

        $csvOutput = "日付,出勤,退勤,休憩,合計\n";

        $workDayOfWeek = [];
        $formattedBreakTimes = [];
        $formattedWorkTimes = [];
        $totalBreakTimes = [];
        $totalWorkTimes = [];

        foreach ($works as $work) {
            $date = date('m/d', strtotime($work->date));
            $workDayOfWeek[$work->id] = $week[date('w', strtotime($work->date))];

            $breakings = Breaking::where('work_id', $work->id)->get();
            $totalBreakTime = 0;
            foreach ($breakings as $breaking) {
                if ($breaking->start_time && $breaking->end_time) {
                    $startTime = strtotime($breaking->start_time);
                    $endTime = strtotime($breaking->end_time);
                    $totalBreakTime += ($endTime - $startTime);
                }
            }
            $totalBreakTimes[$work->id] = $totalBreakTime;
            $breakHours = floor($totalBreakTimes[$work->id] / 3600);
            $breakMinutes = floor(($totalBreakTimes[$work->id] % 3600) / 60);
            $formattedBreakTimes[$work->id] = sprintf('%2d:%02d', $breakHours, $breakMinutes);

            if ($work->start_time && $work->end_time) {
                $workStartTime = strtotime($work->start_time);
                $workEndTime = strtotime($work->end_time);
                $totalWorkTime = ($workEndTime - $workStartTime) - $totalBreakTimes[$work->id];
                $workHours = floor($totalWorkTime / 3600);
                $workMinutes = floor(($totalWorkTime % 3600) / 60);
                $formattedWorkTimes[$work->id] = sprintf('%2d:%02d', $workHours, $workMinutes);
                $workStartTime = date('H:i', strtotime($work->start_time));
                $workEndTime = date('H:i', strtotime($work->end_time));
            } else {
                $formattedWorkTimes[$work->id] = '';
                $workStartTime = '';
                $workEndTime = '';
            }
            $csvOutput .= "{$date}({$workDayOfWeek[$work->id]}),{$workStartTime},{$workEndTime},{$formattedBreakTimes[$work->id]},{$formattedWorkTimes[$work->id]}\n";
        }

        $fileName = "attendance_" . $user->name . $month . ".csv";

        return response()->stream(
            function () use ($csvOutput) {
                echo $csvOutput;
            },
            200,
            [
                "Content-Type" => "text/csv",
                "Content-Disposition" => "attachment; filename=\"$fileName\"",
            ]
        );
    }

    //一般ユーザー
    public function generalLogin()
    {
        return view('general_login');
    }

    public function loginStore(LoginRequest $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            if (!$user->hasVerifiedEmail()) {
                Auth::logout();
                return redirect()->route('login')->withErrors(['login' => 'メール認証が必要です。メールを確認してください。']);
            }

            return $this->loginPipeline($request)->then(function ($request) {
                return app(LoginResponse::class);
            });
        }

        return redirect()->route('login')->withErrors(['login' => 'ログイン情報が間違っています']);
    }

    protected $guard;

    public function __construct(StatefulGuard $guard)
    {
        $this->guard = $guard;
    }

    protected function loginPipeline(LoginRequest $request)
    {
        if (Fortify::$authenticateThroughCallback) {
            return (new Pipeline(app()))->send($request)->through(array_filter(
                call_user_func(Fortify::$authenticateThroughCallback, $request)
            ));
        }

        if (is_array(config('fortify.pipelines.login'))) {
            return (new Pipeline(app()))->send($request)->through(array_filter(
                config('fortify.pipelines.login')
            ));
        }

        return (new Pipeline(app()))->send($request)->through(array_filter([
            config('fortify.limiters.login') ? null : EnsureLoginIsNotThrottled::class,
            config('fortify.lowercase_usernames') ? CanonicalizeUsername::class : null,
            Features::enabled(Features::twoFactorAuthentication()) ? RedirectIfTwoFactorAuthenticatable::class : null,
            AttemptToAuthenticate::class,
            PrepareAuthenticatedSession::class,
        ]));
    }

    public function generalRegister()
    {
        return view('general_register');
    }

    public function registerStore(RegisterRequest $request, CreatesNewUsers $creator): RegisterResponse
    {
        if (config('fortify.lowercase_usernames')) {
            $request->merge([
                Fortify::username() => Str::lower($request->{Fortify::username()}),
            ]);
        }

        event(new Registered($user = $creator->create($request->all())));

        $this->guard->login($user, $request->boolean('remember'));

        return new RegisterResponse();
    }


    public function generalList()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->withErrors(['login' => 'ユーザーが認証されていません。']);
        }
        $month = date('Y/m');
        $year = date('Y');
        $previousMonth = (new DateTime($month . '/01'))->modify('-1 month')->format('m');
        $nextMonth = (new DateTime($month . '/01'))->modify('+1 month')->format('m');
        $week = ['日', '月', '火', '水', '木', '金', '土'];
        $dayOfWeek = $week[date('w')];
        $date = date('m/d');

        $workData = Work::where('user_id', $user->id)->get();
        $todayMonth = date('Y-m');
        $works = $workData->filter(function ($item) use ($todayMonth) {
            return strpos($item->date, $todayMonth) === 0;
        })->values();

        $workDayOfWeek = [];
        $formattedBreakTimes = [];
        $formattedWorkTimes = [];
        $totalBreakTimes = [];
        $totalWorkTimes = [];

        foreach ($works as $work) {
            $workDayOfWeek[$work->id] = $week[date('w', strtotime($work->date))];
            $breakings = Breaking::where('work_id', $work->id)->get();

            $totalBreakTime = 0;
            foreach ($breakings as $breaking) {
                if ($breaking->start_time && $breaking->end_time) {
                    $startTime = strtotime($breaking->start_time);
                    $endTime = strtotime($breaking->end_time);
                    $totalBreakTime += ($endTime - $startTime);
                }
            }
            $totalBreakTimes[$work->id] = $totalBreakTime;
            $breakHours = floor($totalBreakTimes[$work->id] / 3600);
            $breakMinutes = floor(($totalBreakTimes[$work->id] % 3600) / 60);
            $formattedBreakTimes[$work->id] = sprintf('%2d:%02d', $breakHours, $breakMinutes);

            if ($work->start_time && $work->end_time) {
                $workStartTime = strtotime($work->start_time);
                $workEndTime = strtotime($work->end_time);
                $totalWorkTime = ($workEndTime - $workStartTime) - $totalBreakTimes[$work->id];
                $workHours = floor($totalWorkTime / 3600);
                $workMinutes = floor(($totalWorkTime % 3600) / 60);
                $formattedWorkTimes[$work->id] = sprintf('%2d:%02d', $workHours, $workMinutes);
            } else {
                $formattedWorkTimes[$work->id] = '';
            }
        }

        return view('general_attendance', compact('month', 'year', 'previousMonth', 'nextMonth', 'dayOfWeek', 'date', 'works', 'workDayOfWeek', 'formattedBreakTimes', 'formattedWorkTimes'));
    }

    public function generalOtherMonthList($year, $month)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->withErrors(['login' => 'ユーザーが認証されていません。']);
        }
        $month = $month;
        $year = $year;
        $previousMonth = (new DateTime($month . '/01'))->modify('-1 month')->format('m');
        $nextMonth = (new DateTime($month . '/01'))->modify('+1 month')->format('m');
        $week = ['日', '月', '火', '水', '木', '金', '土'];
        $dayOfWeek = $week[date('w')];
        $date = date('m/d');

        $workData = Work::where('user_id', $user->id)->get();
        $monthString = $year . '-' . $month;
        // $todayMonth = date('Y-m');
        $works = $workData->filter(function ($item) use ($monthString) {
            return strpos($item->date, $monthString) === 0;
        })->values();

        $workDayOfWeek = [];
        $formattedBreakTimes = [];
        $formattedWorkTimes = [];
        $totalBreakTimes = [];
        $totalWorkTimes = [];

        foreach ($works as $work) {
            $workDayOfWeek[$work->id] = $week[date('w', strtotime($work->date))];
            $breakings = Breaking::where('work_id', $work->id)->get();

            $totalBreakTime = 0;
            foreach ($breakings as $breaking) {
                if ($breaking->start_time && $breaking->end_time) {
                    $startTime = strtotime($breaking->start_time);
                    $endTime = strtotime($breaking->end_time);
                    $totalBreakTime += ($endTime - $startTime);
                }
            }
            $totalBreakTimes[$work->id] = $totalBreakTime;
            $breakHours = floor($totalBreakTimes[$work->id] / 3600);
            $breakMinutes = floor(($totalBreakTimes[$work->id] % 3600) / 60);
            $formattedBreakTimes[$work->id] = sprintf('%2d:%02d', $breakHours, $breakMinutes);

            if ($work->start_time && $work->end_time) {
                $workStartTime = strtotime($work->start_time);
                $workEndTime = strtotime($work->end_time);
                $totalWorkTime = ($workEndTime - $workStartTime) - $totalBreakTimes[$work->id];
                $workHours = floor($totalWorkTime / 3600);
                $workMinutes = floor(($totalWorkTime % 3600) / 60);
                $formattedWorkTimes[$work->id] = sprintf('%2d:%02d', $workHours, $workMinutes);
            } else {
                $formattedWorkTimes[$work->id] = '';
            }
        }

        return view('general_attendance', compact('month', 'year', 'previousMonth', 'nextMonth', 'dayOfWeek', 'date', 'works', 'workDayOfWeek', 'formattedBreakTimes', 'formattedWorkTimes'));
    }

    public function generalWorkDetail($id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->withErrors(['login' => 'ユーザーが認証されていません。']);
        }
        $work = Work::where('id', $id)->first();
        $application = WorkingApplication::where('work_id', $id)->where('status', '承認待ち')->first();
        $year = Carbon::parse($work->date)->format('Y年');
        $date = Carbon::parse($work->date)->format('n月j日');

        $breakings = Breaking::where('work_id', $id)->get();
        return view('general_detail', compact('user', 'work', 'application', 'year', 'date', 'breakings'));
    }

    public function apply(ApplyRequest $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->withErrors(['login' => 'ユーザーが認証されていません。']);
        }
        $workData = Work::where('id', $id)->first();
        $year = trim(str_replace('年', '', $request->year));
        $date = trim(str_replace('月', '-', str_replace('日', '', $request->date)));
        $applyDate = date('Y-m-d', strtotime($year . '-' . $date));

        $workingApplication = new WorkingApplication();
        $workingApplication->user_id = $user->id;
        $workingApplication->work_id = $id;
        $workingApplication->date = $applyDate;
        $workingApplication->start_time = $request->work_start;
        $workingApplication->end_time = $request->work_end;
        $workingApplication->remarks = $request->remarks;
        $workingApplication->status = '承認待ち';
        $workingApplication->save();

        if ($request->has('break_start') && $request->has('break_end')) {
            foreach ($request->break_start as $index => $breakStart) {
                if (!empty($breakStart) && isset($request->break_end[$index])) {
                    $breakingApplication = new BreakingApplication();
                    $breakingApplication->user_id = $user->id;
                    $breakingApplication->work_id = $id;
                    $breakingApplication->start_time = $breakStart;
                    $breakingApplication->end_time = $request->break_end[$index];
                    $breakingApplication->save();
                }
            }
        }

        return redirect('/attendance/list');
    }

    public function applicationsList()
    {
        $userData = Auth::user();
        if (!$userData) {
            return redirect()->route('login')->withErrors(['login' => 'ユーザーが認証されていません。']);
        }

        if ($userData->role === 'admin') {
            $waitingWorkings = WorkingApplication::where('status', '承認待ち')->get();
            $completedWorkings = WorkingApplication::where('status', '承認済み')->get();
        } else {
            $waitingWorkings = WorkingApplication::where('user_id', $userData->id)->where('status', '承認待ち')->get();
            $completedWorkings = WorkingApplication::where('user_id', $userData->id)->where('status', '承認済み')->get();
        }

        $waitingOldWork = [];
        foreach ($waitingWorkings as $waitingWorking) {
            $waitingOldWork[$waitingWorking->work_id] = Work::where('id', $waitingWorking->work_id)->first();
            $user[$waitingWorking->work_id] = User::where('id', $waitingOldWork[$waitingWorking->work_id]->user_id)->first();
        }

        $completedOldWork = [];
        foreach ($completedWorkings as $completedWorking) {
            $completedOldWork[$completedWorking->work_id] = Work::where('id', $completedWorking->work_id)->first();
            $user[$completedWorking->work_id] = User::where('id', $completedOldWork[$completedWorking->work_id]->user_id)->first();
        }

        if ($userData->role === 'admin') {
            return view('admin_applications', compact('user', 'waitingWorkings', 'waitingOldWork', 'completedWorkings', 'completedOldWork'));
        } else {
            return view('general_applications', compact('user', 'waitingWorkings', 'waitingOldWork', 'completedWorkings', 'completedOldWork'));
        }
    }

    public function applicationsDetail($id)
    {
        $work = WorkingApplication::where('id', $id)->first();
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->withErrors(['login' => 'ユーザーが認証されていません。']);
        }
        $breakings = BreakingApplication::where('work_id', $work->work_id)->get();
        $year = Carbon::parse($work->date)->format('Y年');
        $date = Carbon::parse($work->date)->format('n月j日');
        return view('application_detail', compact('user', 'year', 'date', 'work', 'breakings'));
    }
}
