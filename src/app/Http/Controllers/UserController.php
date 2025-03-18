<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use DateTime;
use Carbon\Carbon;
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
use App\Models\Work;
use App\Models\Breaking;
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
use Illuminate\Http\Request;
use App\Responses\RegisterResponse;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;



class UserController extends Controller
{
    public function adminLogin()
    {
        return view('admin_login');
    }

    public function adminLoginStore(LoginRequest $request)
    {
        $user = AdminUser::where('email', $request->email)->first();
        if (!$user) {
            return redirect()->route('admin.login')->withErrors(['adminLogin' => 'ログイン情報が登録されていません']);
        }

        if (!Hash::check($request->password, $user->password)) {
            return redirect()->route('admin.login')->withErrors(['adminLogin' => 'パスワードが間違っています']);
        }

        // if ($user && !$user->hasVerifiedEmail()) {
        //     return redirect()->route('login')->withErrors(['login' => 'メール認証が必要です。メールを確認してください。']);
        // }

        $this->loginPipeline($request);

        return redirect()->route('admin.list');
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

    public function loginStore(LoginRequest $request)
    {
        // Log::info('ログイン試行: ' . $request->email);
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            // Log::info('ログイン成功: ' . $request->email);
            return $this->loginPipeline($request)->then(function ($request) {
                // Log::info('ログインパイプライン成功: ' . $request->email);
                return app(LoginResponse::class);
            });
            // return redirect()->intended('/attendance');
        }

        // Log::warning('ログイン失敗: ' . $request->email);
        return redirect()->route('login')->withErrors(['login' => 'ログイン情報が間違っています']);

        // if ($user && !$user->hasVerifiedEmail()) {
        //     return redirect()->route('login')->withErrors(['login' => 'メール認証が必要です。メールを確認してください。']);
        // }
    }

    protected $guard;

    public function __construct(StatefulGuard $guard)
    {
        $this->guard = $guard;
    }

    protected function loginPipeline(LoginRequest $request)
    {
        // Log::info('ログインパイプライン開始');
        if (Fortify::$authenticateThroughCallback) {
            return (new Pipeline(app()))->send($request)->through(array_filter(
                call_user_func(Fortify::$authenticateThroughCallback, $request)
            ));
        }

        if (is_array(config('fortify.pipelines.login'))) {
            // Log::info('Fortifyパイプラインを使用しています。');
            return (new Pipeline(app()))->send($request)->through(array_filter(
                config('fortify.pipelines.login')
            ));
        }

        // Log::info('デフォルトのパイプラインを使用中');
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

            $workStartTime = strtotime($work->start_time);
            $workEndTime = strtotime($work->end_time);
            $totalWorkTime = ($workEndTime - $workStartTime) - $totalBreakTimes[$work->id];
            $workHours = floor($totalWorkTime / 3600);
            $workMinutes = floor(($totalWorkTime % 3600) / 60);
            $formattedWorkTimes[$work->id] = sprintf('%2d:%02d', $workHours, $workMinutes);
        }

        return view('general_attendance', compact('month', 'year', 'previousMonth', 'nextMonth', 'dayOfWeek', 'date', 'works', 'workDayOfWeek', 'formattedBreakTimes', 'formattedWorkTimes'));
    }

    public function generalOtherMonthList($year, $month)
    {
        $user = Auth::user();
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

            $workStartTime = strtotime($work->start_time);
            $workEndTime = strtotime($work->end_time);
            $totalWorkTime = ($workEndTime - $workStartTime) - $totalBreakTimes[$work->id];
            $workHours = floor($totalWorkTime / 3600);
            $workMinutes = floor(($totalWorkTime % 3600) / 60);
            $formattedWorkTimes[$work->id] = sprintf('%2d:%02d', $workHours, $workMinutes);
        }

        return view('general_attendance', compact('month', 'year', 'previousMonth', 'nextMonth', 'dayOfWeek', 'date', 'works', 'workDayOfWeek', 'formattedBreakTimes', 'formattedWorkTimes'));
    }

    public function generalWorkDetail($id)
    {
        $user = Auth::user();
        $work = Work::where('id', $id)->first();
        $year = Carbon::parse($work->date)->format('Y年');
        $date = Carbon::parse($work->date)->format('n月j日');

        $breakings = Breaking::where('work_id', $id)->get();
        return view('general_detail', compact('work', 'year', 'date', 'breakings'));
    }

    public function checkWait()
    {
        return view('general_detail-wait');
    }

    public function applicationsList()
    {
        return view('general_applications');
    }


}
