<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\ApplyRequest;
use App\Http\Requests\EmailVerificationRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\Work;
use App\Models\Breaking;
use App\Models\WorkingApplication;
use App\Models\BreakingApplication;
use Tests\TestCase;

class AllControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }
    // // [認証機能(一般ユーザー)]
    // // 名前が未入力の場合、バリデーションメッセージが表示される
    // // メールアドレスが未入力の場合、バリデーションメッセージが表示される
    // // パスワードが8文字未満の場合、バリデーションメッセージが表示される
    // // パスワードが一致しない場合、バリデーションメッセージが表示される
    // // パスワードが未入力の場合、バリデーションメッセージが表示される
    #[\PHPUnit\Framework\Attributes\DataProvider('RegisterDataproviderValidation')]
    public function testRegisterValidationCheck(array $params, array $messages, bool $expect): void
    {
        $request = new RegisterRequest();
        $rules = $request->rules();
        $validator = Validator::make($params, $rules);
        $validator = $validator->setCustomMessages($request->messages());
        $result = $validator->passes();
        $this->assertEquals($expect, $result);
        $this->assertSame($messages, $validator->errors()->messages());
    }
    /**
     * バリデーションチェック用データ
     */
    public static function RegisterDataproviderValidation()
    {
        return [
            'name null' => [
                [
                    'name' => null,
                    'email' => 'test@example.com',
                    'password' => 'password123',
                    'password_confirmation' => 'password123',
                ],
                [
                    'name' => [
                        'お名前を入力してください',
                    ],
                ],
                false
            ],
            'email null' => [
                [
                    'name' => 'テストユーザー',
                    'email' => null,
                    'password' => 'password123',
                    'password_confirmation' => 'password123',
                ],
                [
                    'email' => [
                        'メールアドレスを入力してください',
                    ],
                ],
                false
            ],
            'password short' => [
                [
                    'name' => 'テストユーザー',
                    'email' => 'test@example.com',
                    'password' => 'abc1234',
                    'password_confirmation' => 'abc1234',
                ],
                [
                    'password' => [
                        'パスワードは8文字以上で入力してください',
                    ],
                ],
                false
            ],
            'password_confirm different' => [
                [
                    'name' => 'テストユーザー',
                    'email' => 'test@example.com',
                    'password' => 'password123',
                    'password_confirmation' => 'abc1234',
                ],
                [
                    'password' => [
                        'パスワードと一致しません',
                    ],
                ],
                false
            ],
            'password null' => [
                [
                    'name' => 'テストユーザー',
                    'email' => 'test@example.com',
                    'password' => null,
                    'password_confirmation' => null,
                ],
                [
                    'password' => [
                        'パスワードを入力してください',
                    ],
                    'password_confirmation' => [
                        '確認用パスワードを入力してください',
                    ],
                ],
                false
            ],
        ];
    }

    // フォームに内容が入力されていた場合、データが正常に保存される
    #[\PHPUnit\Framework\Attributes\Test]
    public function registrationSuccess()
    {
        $params = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->post('/register', $params);
        $response->assertStatus(302);
        $response->assertRedirect('/login');

        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
        ]);
    }

    // [ログイン認証機能（一般ユーザー）]
    // メールアドレスが未入力の場合、バリデーションメッセージが表示される
    // パスワードが未入力の場合、バリデーションメッセージが表示される
    #[\PHPUnit\Framework\Attributes\DataProvider('LoginDataproviderValidation')]
    public function testLoginValidationCheck(array $params, array $messages, bool $expect): void
    {
        $request = new LoginRequest();
        $rules = $request->rules();
        $validator = Validator::make($params, $rules);
        $validator = $validator->setCustomMessages($request->messages());
        $result = $validator->passes();
        $this->assertEquals($expect, $result);
        $this->assertSame($messages, $validator->errors()->messages());
    }

    /**
     * バリデーションチェック用データ
     */
    public static function LoginDataproviderValidation()
    {
        return [
            'email null' => [
                [
                    'email' => null,
                    'password' => 'password123',
                ],
                [
                    'email' => [
                        'メールアドレスを入力してください',
                    ],
                ],
                false
            ],
            'password null' => [
                [
                    'email' => 'test@example.com',
                    'password' => null,
                ],
                [
                    'password' => [
                        'パスワードを入力してください',
                    ],
                ],
                false
            ],
        ];
    }

    // 登録内容と一致しない場合、バリデーションメッセージが表示される
    public function testWrongLogin()
    {
        $wrongUser = [
            'email' => 'wrong@example.com',
            'password' => 'wrongpass123',
        ];
        $response = $this->post('/login', $wrongUser);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['login' => 'ログイン情報が登録されていません']);
    }

    //[ログイン認証機能（管理者）]
    //メールアドレスが未入力の場合、バリデーションメッセージが表示される
    //パスワードが未入力の場合、バリデーションメッセージが表示される
    #[\PHPUnit\Framework\Attributes\DataProvider('AdminLoginDataproviderValidation')]
    public function testAdminLoginValidationCheck(array $params, array $messages, bool $expect): void
    {
        $request = new LoginRequest();
        $rules = $request->rules();
        $validator = Validator::make($params, $rules);
        $validator = $validator->setCustomMessages($request->messages());
        $result = $validator->passes();
        $this->assertEquals($expect, $result);
        $this->assertSame($messages, $validator->errors()->messages());
    }

    /**
     * バリデーションチェック用データ
     */
    public static function AdminLoginDataproviderValidation()
    {
        return [
            'email null' => [
                [
                    'email' => null,
                    'password' => 'password123',
                ],
                [
                    'email' => [
                        'メールアドレスを入力してください',
                    ],
                ],
                false
            ],
            'password null' => [
                [
                    'email' => 'test@example.com',
                    'password' => null,
                ],
                [
                    'password' => [
                        'パスワードを入力してください',
                    ],
                ],
                false
            ],
        ];
    }
    //登録内容と一致しない場合、バリデーションメッセージが表示される
    public function testWrongAdminLogin()
    {
        $wrongUser = [
            'email' => 'wrong@example.com',
            'password' => 'wrongpass123',
        ];
        $response = $this->post('/admin/login', $wrongUser);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['login' => 'ログイン情報が登録されていません']);
    }

    //[日時取得機能]
    //現在の日時情報がUIと同じ形式で出力されている
    public function testCurrentTimeIsDisplayed()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);
        $dateTime = now()->format('H:i');
        $response = $this->get('/attendance');
        $response->assertSee($dateTime);
    }

    //[ステータス確認機能]
    //勤務外の場合、勤怠ステータスが正しく表示される
    public function testOutOfWorkStatus()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);
        $response = $this->get('/attendance');
        $response->assertSee('勤務外');
    }

    //出勤中の場合、勤怠ステータスが正しく表示される
    public function testAtWorkStatus()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);
        $work = Work::create([
            'user_id' => $user->id,
            'date' => today(),
            'start_time' => now()
        ]);
        $response = $this->get('/attendance');
        $response->assertSee('勤務中');
    }

    public function testHaveBreakOutOfWorkStatus()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);
        $work = Work::create([
            'user_id' => $user->id,
            'date' => today(),
            'start_time' => now()
        ]);
        $break = Breaking::create([
            'user_id' => $user->id,
            'work_id' => $work->id,
            'start_time' => $work->start_time->addHours(1),
            'end_time' => $work->start_time->addHours(2)
        ]);
        $response = $this->get('/attendance');
        $response->assertSee('勤務中');
    }
    //休憩中の場合、勤怠ステータスが正しく表示される
    public function testAtBreakStatus()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);
        $work = Work::create([
            'user_id' => $user->id,
            'date' => today(),
            'start_time' => now()
        ]);
        $break = Breaking::create([
            'user_id' => $user->id,
            'work_id' => $work->id,
            'start_time' => $work->start_time->addHours(1)
        ]);
        $response = $this->get('/attendance');
        $response->assertSee('休憩中');
    }
    //退勤済の場合、勤怠ステータスが正しく表示される
    public function testFinishWorkStatus()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);
        $work = Work::create([
            'user_id' => $user->id,
            'date' => today(),
            'start_time' => now()->subHours(8),
            'end_time' => now()
        ]);
        $response = $this->get('/attendance');
        $response->assertSee('退勤済');
    }
    public function testHaveBreakFinishWorkStatus()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);
        $work = Work::create([
            'user_id' => $user->id,
            'date' => today(),
            'start_time' => now(),
            'end_time' => now()->addHours(8)
        ]);
        $break = Breaking::create([
            'user_id' => $user->id,
            'work_id' => $work->id,
            'start_time' => $work->start_time->addHours(1),
            'end_time' => $work->start_time->addHours(2),
        ]);
        $response = $this->get('/attendance');
        $response->assertSee('退勤済');
    }

    //[出勤機能]
    //出勤ボタンが正しく機能する

    public function testAttendanceButton()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);
        $response = $this->get('/attendance');
        $response->assertSee('出勤');
        $response = $this->post('/attendance');
        $response->assertStatus(200);
        $response->assertViewIs('work_after');
        $response->assertSee('勤務中');
        $this->assertDatabaseHas('works', [
            'user_id' => $user->id,
            'date' => today(),
            'start_time' => date('H:i:s')
        ]);
    }
    //出勤は一日一回のみできる
    public function testOnceAttendanceButton()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);
        $work = Work::create([
            'user_id' => $user->id,
            'date' => today(),
            'start_time' => now(),
            'end_time' => now()->addHours(8)
        ]);
        $response = $this->get('/attendance');
        $response->assertDontSee('<button type="submit">出勤</button>');
    }
    //出勤時刻が管理画面で確認できる
    public function testAttendanceTimeStore()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);
        $response = $this->get('/attendance');
        $response->assertSee('出勤');
        $response = $this->post('/attendance');
        $response->assertStatus(200);
        $response->assertViewIs('work_after');
        $this->assertDatabaseHas('works', [
            'user_id' => $user->id,
            'date' => today(),
            'start_time' => date('H:i:s')
        ]);

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertViewIs('general_attendance');
        $response->assertSee(date('H:i'), today());
    }

    //[休憩機能]
    //休憩ボタンが正しく機能する
    public function testBreakStartButton()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);
        $work = Work::create([
            'user_id' => $user->id,
            'date' => today(),
            'start_time' => now(),
        ]);
        $response = $this->get('/attendance');
        $response->assertViewIs('work_after');
        $response->assertSee('休憩入');

        $response = $this->get('/attendance/break');
        $response->assertViewIs('work_break');
        $response->assertSee('休憩中');
        $this->assertDatabaseHas('breakings', [
            'user_id' => $user->id,
            'work_id' => $work->id,
            'start_time' => date('H:i:s')
        ]);
    }
    //休憩は一日に何回でもできる
    public function testManyTimeBreakStartButton()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);
        $work = Work::create([
            'user_id' => $user->id,
            'date' => today(),
            'start_time' => now(),
        ]);
        $break = Breaking::create([
            'user_id' => $user->id,
            'work_id' => $work->id,
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(2),
        ]);
        $response = $this->get('/attendance');
        $response->assertViewIs('work_after');
        $response->assertSee('休憩入');
    }
    //休憩戻ボタンが正しく機能する
    public function testBreakEndButton()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);
        $work = Work::create([
            'user_id' => $user->id,
            'date' => today(),
            'start_time' => now(),
        ]);

        $response = $this->get('/attendance/break');
        $response->assertViewIs('work_break');
        $response->assertSee('休憩中');
        $response->assertSee('休憩戻');

        $response = $this->get('/attendance/return');
        $response->assertViewIs('work_after');
        $response->assertSee('勤務中');

        $this->assertDatabaseHas('breakings', [
            'user_id' => $user->id,
            'work_id' => $work->id,
            'start_time' => date('H:i:s'),
            'end_time' => date('H:i:s')
        ]);
    }
    //休憩戻は一日に何回でもできる
    public function testManyTimeBreakEndButton()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);
        $work = Work::create([
            'user_id' => $user->id,
            'date' => today(),
            'start_time' => now(),
        ]);
        $break = Breaking::create([
            'user_id' => $user->id,
            'work_id' => $work->id,
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(2),
        ]);

        $response = $this->get('/attendance/break');
        $response->assertViewIs('work_break');
        $response->assertSee('休憩中');
        $response->assertSee('休憩戻');
    }
    //休憩時刻が管理画面で確認できる
    public function testBreakTimeStore()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);
        $work = Work::create([
            'user_id' => $user->id,
            'date' => today(),
            'start_time' => now(),
        ]);

        $response = $this->get('/attendance/break');
        $response = $this->get('/attendance/return');

        $this->assertDatabaseHas('breakings', [
            'user_id' => $user->id,
            'work_id' => $work->id,
            'start_time' => date('H:i:s'),
            'end_time' => date('H:i:s'),
        ]);

        $response = $this->get('/attendance/' . $work->id);
        $response->assertStatus(200);
        $response->assertSee(date('H:i'));
    }

    //[退勤機能]
    //退勤ボタンが正しく機能する
    public function testCompleteButton()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);
        $work = Work::create([
            'user_id' => $user->id,
            'date' => today(),
            'start_time' => '09:00:00',
        ]);
        $response = $this->get('/attendance');
        $response->assertSee('退勤');
        $response->assertViewIs('work_after');
        $response->assertSee('勤務中');

        $response = $this->get('/attendance/complete');
        $response->assertViewIs('work_finish');
        $response->assertSee('退勤済');
        $this->assertDatabaseHas('works', [
            'user_id' => $user->id,
            'date' => today(),
            'start_time' => '09:00:00',
            'end_time' => date('H:i:s')
        ]);
    }
    //退勤時刻が管理画面で確認できる
    public function testCompleteTimeStore()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $response = $this->post('/attendance');
        $response = $this->get('/attendance/complete');

        $this->assertDatabaseHas('works', [
            'user_id' => $user->id,
            'date' => date('Y-m-d'),
            'start_time' => date('H:i:s'),
            'end_time' => date('H:i:s'),
        ]);

        $work = Work::where('user_id', $user->id)->first();
        $response = $this->get('/attendance/' . $work->id);
        $response->assertStatus(200);
        $response->assertSee(date('H:i'), date('Y-m-d'));
    }

    //[勤怠一覧情報取得機能（一般ユーザー）]
    //自分が行った勤怠情報が全て表示されている
    public function testAllGeneralAttendanceInformation()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $work1 = Work::create([
            'user_id' => $user->id,
            'date' => '2025-04-01',
            'start_time' => now(),
            'end_time' => now()->addHours(8),
        ]);
        $break1 = Breaking::create([
            'user_id' => $user->id,
            'work_id' => $work1->id,
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(2),
        ]);

        $work2 = Work::create([
            'user_id' => $user->id,
            'date' => '2025-04-02',
            'start_time' => now(),
            'end_time' => now()->addHours(8),
        ]);
        $break2 = Breaking::create([
            'user_id' => $user->id,
            'work_id' => $work2->id,
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(5),
        ]);

        $response = $this->get('/attendance/list/2025/04');
        $response->assertStatus(200);

        $expectedStartTime1 = date('H:i', strtotime($work1->start_time));
        $expectedEndTime1 = date('H:i', strtotime($work1->end_time));
        $expectedBreakTime1 = '1:00';
        $expectedWorkTime1 = '7:00';

        $response->assertSee('04/01(火)');
        $response->assertSee($expectedStartTime1);
        $response->assertSee($expectedEndTime1);
        $response->assertSee($expectedBreakTime1);
        $response->assertSee($expectedWorkTime1);

        $expectedStartTime2 = date('H:i', strtotime($work2->start_time));
        $expectedEndTime2 = date('H:i', strtotime($work2->end_time));
        $expectedBreakTime2 = '4:00';
        $expectedWorkTime2 = '4:00';

        $response->assertSee('04/02(水)');
        $response->assertSee($expectedStartTime2);
        $response->assertSee($expectedEndTime2);
        $response->assertSee($expectedBreakTime2);
        $response->assertSee($expectedWorkTime2);
    }
    //勤怠一覧画面に遷移した際に現在の月が表示される
    public function testThisMonthDisplayed()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $response = $this->get('/attendance/list');
        $response->assertSee(date('Y/m', strtotime(today())));
    }
    //「前月」を押下した時に表示月の前月の情報が表示される
    public function testPreviousMonthAttendanceInformation()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $work1 = Work::create([
            'user_id' => $user->id,
            'date' => '2025-04-01',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ]);
        $break1 = Breaking::create([
            'user_id' => $user->id,
            'work_id' => $work1->id,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);

        $work2 = Work::create([
            'user_id' => $user->id,
            'date' => '2025-03-31',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ]);
        $break2 = Breaking::create([
            'user_id' => $user->id,
            'work_id' => $work2->id,
            'start_time' => '10:00:00',
            'end_time' => '13:00:00',
        ]);
        $expectedStartTime = date('H:i', strtotime($work2->start_time));
        $expectedEndTime = date('H:i', strtotime($work2->end_time));

        $response = $this->get('/attendance/list/2025/03');
        $response->assertStatus(200);

        $response->assertSee('03/31(月)');
        $response->assertSee($expectedStartTime);
        $response->assertSee($expectedEndTime);
        $response->assertSee('3:00');
        $response->assertSee('5:00');
    }
    //「翌月」を押下した時に表示月の翌月の情報が表示される
    public function testNextMonthAttendanceInformation()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $work1 = Work::create([
            'user_id' => $user->id,
            'date' => '2025-04-01',
            'start_time' => now(),
            'end_time' => now()->addHours(8),
        ]);
        $break1 = Breaking::create([
            'user_id' => $user->id,
            'work_id' => $work1->id,
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(2),
        ]);

        $work2 = Work::create([
            'user_id' => $user->id,
            'date' => '2025-03-31',
            'start_time' => now(),
            'end_time' => now()->addHours(6),
        ]);
        $break2 = Breaking::create([
            'user_id' => $user->id,
            'work_id' => $work2->id,
            'start_time' => now()->subMonth()->addHours(1),
            'end_time' => now()->subMonth()->addHours(2),
        ]);
        $expectedStartTime = date('H:i', strtotime($work1->start_time));
        $expectedEndTime = date('H:i', strtotime($work1->end_time));

        $response = $this->get('/attendance/list/2025/04');
        $response->assertStatus(200);

        $response->assertSee('04/01(火)');
        $response->assertSee($expectedStartTime);
        $response->assertSee($expectedEndTime);
        $response->assertSee('1:00');
        $response->assertSee('7:00');
    }
    //「詳細」を押下すると、その日の勤怠詳細画面に遷移する
    public function testGeneralAttendanceDetail()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $work = Work::create([
            'user_id' => $user->id,
            'date' => '2025-04-01',
            'start_time' => now(),
            'end_time' => now()->addHours(8),
        ]);
        $break = Breaking::create([
            'user_id' => $user->id,
            'work_id' => $work->id,
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(2),
        ]);

        $response = $this->get('/attendance/' . $work->id);
        $response->assertStatus(200);
        $response->assertViewIs('general_detail');
    }
    //[勤怠詳細情報取得機能（一般ユーザー）]
    //勤怠詳細画面の「名前」がログインユーザーの氏名になっている
    public function testGeneralAttendanceDetailsName()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $work = Work::create([
            'user_id' => $user->id,
            'date' => '2025-04-01',
            'start_time' => now(),
            'end_time' => now()->addHours(8),
        ]);
        $break = Breaking::create([
            'user_id' => $user->id,
            'work_id' => $work->id,
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(2),
        ]);

        $response = $this->get('/attendance/' . $work->id);
        $response->assertStatus(200);
        $response->assertSee('test');
    }
    //勤怠詳細画面の「日付」が選択した日付になっている
    public function testGeneralAttendanceDetailsDate()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $work = Work::create([
            'user_id' => $user->id,
            'date' => '2025-04-01',
            'start_time' => now(),
            'end_time' => now()->addHours(8),
        ]);
        $break = Breaking::create([
            'user_id' => $user->id,
            'work_id' => $work->id,
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(2),
        ]);

        $expectedYear = date('Y年', strtotime($work->date));
        $expectedDate = date('n月j日', strtotime($work->date));

        $response = $this->get('/attendance/' . $work->id);
        $response->assertStatus(200);

        $response->assertSee($expectedYear);
        $response->assertSee($expectedDate);
    }
    //「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
    public function testGeneralAttendanceDetailsWorkTime()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $work = Work::create([
            'user_id' => $user->id,
            'date' => '2025-04-01',
            'start_time' => now(),
            'end_time' => now()->addHours(8),
        ]);
        $break = Breaking::create([
            'user_id' => $user->id,
            'work_id' => $work->id,
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(2),
        ]);

        $expectedWorkStartTime = date('H:i', strtotime($work->start_time));
        $expectedWorkEndTime = date('H:i', strtotime($work->end_time));

        $response = $this->get('/attendance/' . $work->id);
        $response->assertStatus(200);

        $response->assertSee($expectedWorkStartTime);
        $response->assertSee($expectedWorkEndTime);
    }
    //「休憩」にて記されている時間がログインユーザーの打刻と一致している
    public function testGeneralAttendanceDetailsBreakTime()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $work = Work::create([
            'user_id' => $user->id,
            'date' => '2025-04-01',
            'start_time' => now(),
            'end_time' => now()->addHours(8),
        ]);
        $break = Breaking::create([
            'user_id' => $user->id,
            'work_id' => $work->id,
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(2),
        ]);

        $expectedBreakStartTime = date('H:i', strtotime($break->start_time));
        $expectedBreakEndTime = date('H:i', strtotime($break->end_time));

        $response = $this->get('/attendance/' . $work->id);
        $response->assertStatus(200);

        $response->assertSee($expectedBreakStartTime);
        $response->assertSee($expectedBreakEndTime);
    }

    //[勤怠詳細情報修正機能（一般ユーザー）]
    //出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    //備考欄が未入力の場合のエラーメッセージが表示される
    #[\PHPUnit\Framework\Attributes\DataProvider('ApplyDataproviderValidation')]
    public function testApplyValidationCheck(array $params, array $messages, bool $expect): void
    {
        $request = new ApplyRequest();
        $rules = $request->rules();
        $validator = Validator::make($params, $rules);
        $validator = $validator->setCustomMessages($request->messages());
        $result = $validator->passes();
        $this->assertEquals($expect, $result);
        $this->assertSame($messages, $validator->errors()->messages());
    }
    /**
     * バリデーションチェック用データ
     */
    public static function ApplyDataproviderValidation()
    {
        $now = now()->setDate(2025, 4, 1)->setTime(9, 0, 0);
        return [
            'work start after work end' => [
                [
                    'year' => '2025年',
                    'date' => '4月1日',
                    'work_start' => $now->copy()->addHours(8),
                    'work_end' => $now->copy()->addHours(7),
                    'remarks' => 'test'
                ],
                [
                    'work_end' => [
                        '出勤時刻もしくは退勤時刻が不適切な値です',
                    ],
                ],
                false
            ],
            'remarks null' => [
                [
                    'year' => '2025年',
                    'date' => '4月1日',
                    'work_start' => $now,
                    'work_end' => $now->copy()->addHours(8),
                    'remarks' => null
                ],
                [
                    'remarks' => [
                        '備考を記入してください',
                    ],
                ],
                false
            ],
        ];
    }
    //休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
    //休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function testApplyBreakStartValidationCheck()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $work = Work::create([
            'user_id' => $user->id,
            'date' => '2025-04-01',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ]);

        $data = [
            'year' => '2025年',
            'date' => '4月1日',
            'work_start' => '09:00',
            'work_end' => '17:00',
            'break_start' => '18:00',
            'break_end' => '19:00',
            'remarks' => 'テスト'
        ];
        $response = $this->post('/attendance/' . $work->id, $data);
        $response->assertRedirect('/attendance/' . $work->id);
        $response->assertSessionHasErrors(['break_start' => '休憩時間が勤務時間外です']);
    }
    //修正申請処理が実行される
    public function testApply()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $work = Work::create([
            'user_id' => $user->id,
            'date' => '2025-04-01',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ]);

        $data = [
            'year' => '2025年',
            'date' => '4月4日',
            'work_start' => '11:00',
            'work_end' => '19:00',
            'break_start' => '16:00',
            'break_end' => '17:00',
            'remarks' => 'テスト'
        ];
        $response = $this->post('/attendance/' . $work->id, $data);
        $this->assertDatabaseHas('working_applications', [
            'user_id' => $user->id,
            'work_id' => $work->id,
        ]);

        $adminUser = User::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'admin'
        ]);
        $this->actingAs($adminUser);
        $this->assertDatabaseHas('users', [
            'id' => $adminUser->id,
            'name' => 'admin',
            'role' => 'admin'
        ]);
        $this->assertDatabaseHas('breaking_applications', [
            'work_id' => $work->id,
            'start_time' => '16:00:00',
            'end_time' => '17:00:00'
        ]);

        $waitingWorking = WorkingApplication::where('user_id', $user->id)->first();
        $response = $this->get('/stamp_correction_request/approve/' . $waitingWorking->id);
        $response->assertViewIs('approve');
        $response->assertSee('test');
        $response->assertSee('2025年');
        $response->assertSee('4月4日');
        $response->assertSee('11:00');
        $response->assertSee('19:00');
        $response->assertSee('16:00');
        $response->assertSee('17:00');
        $response->assertSee('テスト');

        $response = $this->get('/stamp_correction_request/list');
        $response->assertSee('承認待ち');
        $response->assertSee('test');
        $response->assertSee('2025/04/01');
        $response->assertSee('テスト');
        $response->assertSee('2025/04/04');
    }
    //「承認待ち」にログインユーザーが行った申請が全て表示されていること
    public function testWaitingApplicationsDisplayed()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $work = Work::create([
            'user_id' => $user->id,
            'date' => '2025-04-01',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ]);

        $workApplication = WorkingApplication::create([
            'user_id' => $user->id,
            'work_id' => $work->id,
            'date' => '2025-04-04',
            'start_time' => '11:00:00',
            'end_time' => '19:00:00',
            'remarks' => 'テスト',
            'status' => '承認待ち'
        ]);
        $response = $this->get('/stamp_correction_request/list');
        $response->assertStatus(200);
        $response->assertViewIs('general_applications');
        $response->assertSee('承認待ち');
        $response->assertSee('test');
        $response->assertSee('2025/04/01');
        $response->assertSee('テスト');
        $response->assertSee('2025/04/04');
    }
    //「承認済み」に管理者が承認した修正申請が全て表示されている
    public function testCompletedApplicationsDisplayed()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $work = Work::create([
            'user_id' => $user->id,
            'date' => '2025-04-01',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ]);

        $workApplication = WorkingApplication::create([
            'user_id' => $user->id,
            'work_id' => $work->id,
            'date' => '2025-04-04',
            'start_time' => '11:00:00',
            'end_time' => '19:00:00',
            'remarks' => 'テスト',
            'status' => '承認済み'
        ]);
        $response = $this->get('/stamp_correction_request/list');
        $response->assertStatus(200);
        $response->assertViewIs('general_applications');
        $response->assertSee('承認済み');
        $response->assertSee('test');
        $response->assertSee('2025/04/01');
        $response->assertSee('テスト');
        $response->assertSee('2025/04/04');
    }
    //各申請の「詳細」を押下すると申請詳細画面に遷移する
    public function testApplicationsDetail()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $work = Work::create([
            'user_id' => $user->id,
            'date' => '2025-04-01',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ]);

        $workApplication = WorkingApplication::create([
            'user_id' => $user->id,
            'work_id' => $work->id,
            'date' => '2025-04-04',
            'start_time' => '11:00:00',
            'end_time' => '19:00:00',
            'remarks' => 'テスト',
            'status' => '承認待ち'
        ]);

        $breakApplication = BreakingApplication::create([
            'user_id' => $user->id,
            'work_id' => $work->id,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);

        $response = $this->get('/stamp_correction_request/detail/' . $workApplication->id);
        $response->assertStatus(200);
        $response->assertViewIs('application_detail');
        $response->assertSee('test');
        $response->assertSee('2025年');
        $response->assertSee('4月4日');
        $response->assertSee('11:00');
        $response->assertSee('19:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
        $response->assertSee('テスト');
    }

    // [勤怠一覧情報取得機能（管理者）]
    // その日になされた全ユーザーの勤怠情報が正確に確認できる
    public function testAllAdminAttendanceInformation()
    {
        $user1 = User::create([
            'name' => 'test1',
            'email' => 'test1@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $user2 = User::create([
            'name' => 'test2',
            'email' => 'test2@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $work1 = Work::create([
            'user_id' => $user1->id,
            'date' => '2025-04-01',
            'start_time' => now(),
            'end_time' => now()->addHours(8),
        ]);
        $break1 = Breaking::create([
            'user_id' => $user1->id,
            'work_id' => $work1->id,
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(2),
        ]);
        $work2 = Work::create([
            'user_id' => $user2->id,
            'date' => '2025-04-01',
            'start_time' => now(),
            'end_time' => now()->addHours(8),
        ]);
        $break2 = Breaking::create([
            'user_id' => $user2->id,
            'work_id' => $work2->id,
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(5),
        ]);

        $adminUser = User::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'admin'
        ]);
        $this->actingAs($adminUser);

        $response = $this->get('/admin/attendance/list/2025-04-01');
        $response->assertStatus(200);
        $response->assertSee('2025/04/01');

        $expectedStartTime1 = date('H:i', strtotime($work1->start_time));
        $expectedEndTime1 = date('H:i', strtotime($work1->end_time));
        $expectedBreakTime1 = '1:00';
        $expectedWorkTime1 = '7:00';

        $response->assertSee('test1');
        $response->assertSee($expectedStartTime1);
        $response->assertSee($expectedEndTime1);
        $response->assertSee($expectedBreakTime1);
        $response->assertSee($expectedWorkTime1);

        $expectedStartTime2 = date('H:i', strtotime($work2->start_time));
        $expectedEndTime2 = date('H:i', strtotime($work2->end_time));
        $expectedBreakTime2 = '4:00';
        $expectedWorkTime2 = '4:00';

        $response->assertSee('test2');
        $response->assertSee($expectedStartTime2);
        $response->assertSee($expectedEndTime2);
        $response->assertSee($expectedBreakTime2);
        $response->assertSee($expectedWorkTime2);
    }
    //遷移した際に現在の日付が表示される
    public function testAdminTodayDisplayed()
    {
        $adminUser = User::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'admin'
        ]);
        $this->actingAs($adminUser);

        $response = $this->get('/admin/attendance/list');
        $response->assertSee(date('Y/m/d', strtotime(today())));
    }
    //「前日」を押下した時に前の日の勤怠情報が表示される
    public function testAdminPreviousDayAttendanceInformation()
    {
        $user1 = User::create([
            'name' => 'test1',
            'email' => 'test1@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $user2 = User::create([
            'name' => 'test2',
            'email' => 'test2@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $work1 = Work::create([
            'user_id' => $user1->id,
            'date' => '2025-04-01',
            'start_time' => now(),
            'end_time' => now()->addHours(8),
        ]);
        $break1 = Breaking::create([
            'user_id' => $user1->id,
            'work_id' => $work1->id,
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(2),
        ]);
        $work2 = Work::create([
            'user_id' => $user2->id,
            'date' => '2025-04-02',
            'start_time' => now(),
            'end_time' => now()->addHours(8),
        ]);
        $break2 = Breaking::create([
            'user_id' => $user2->id,
            'work_id' => $work2->id,
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(5),
        ]);

        $adminUser = User::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'admin'
        ]);
        $this->actingAs($adminUser);

        $response = $this->get('/admin/attendance/list/2025-04-01');
        $response->assertStatus(200);

        $response->assertSee('2025/04/01');
        $expectedStartTime1 = date('H:i', strtotime($work1->start_time));
        $expectedEndTime1 = date('H:i', strtotime($work1->end_time));
        $expectedBreakTime1 = '1:00';
        $expectedWorkTime1 = '7:00';

        $response->assertSee('test1');
        $response->assertSee($expectedStartTime1);
        $response->assertSee($expectedEndTime1);
        $response->assertSee($expectedBreakTime1);
        $response->assertSee($expectedWorkTime1);
    }
    //「翌日」を押下した時に次の日の勤怠情報が表示される
    public function testAdminNextDayAttendanceInformation()
    {
        $user1 = User::create([
            'name' => 'test1',
            'email' => 'test1@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $user2 = User::create([
            'name' => 'test2',
            'email' => 'test2@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $work1 = Work::create([
            'user_id' => $user1->id,
            'date' => '2025-04-01',
            'start_time' => now(),
            'end_time' => now()->addHours(8),
        ]);
        $break1 = Breaking::create([
            'user_id' => $user1->id,
            'work_id' => $work1->id,
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(2),
        ]);
        $work2 = Work::create([
            'user_id' => $user2->id,
            'date' => '2025-04-02',
            'start_time' => now(),
            'end_time' => now()->addHours(8),
        ]);
        $break2 = Breaking::create([
            'user_id' => $user2->id,
            'work_id' => $work2->id,
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(5),
        ]);

        $adminUser = User::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'admin'
        ]);
        $this->actingAs($adminUser);

        $response = $this->get('/admin/attendance/list/2025-04-02');
        $response->assertStatus(200);

        $response->assertSee('2025/04/02');
        $expectedStartTime2 = date('H:i', strtotime($work2->start_time));
        $expectedEndTime2 = date('H:i', strtotime($work2->end_time));
        $expectedBreakTime2 = '4:00';
        $expectedWorkTime2 = '4:00';

        $response->assertSee('test2');
        $response->assertSee($expectedStartTime2);
        $response->assertSee($expectedEndTime2);
        $response->assertSee($expectedBreakTime2);
        $response->assertSee($expectedWorkTime2);
    }

    // [勤怠詳細情報取得・修正機能（管理者）]
    // 勤怠詳細画面に表示されるデータが選択したものになっている
    public function testAdminAttendanceDetailsInformation()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $work = Work::create([
            'user_id' => $user->id,
            'date' => '2025-04-01',
            'start_time' => now(),
            'end_time' => now()->addHours(8),
        ]);
        $break = Breaking::create([
            'user_id' => $user->id,
            'work_id' => $work->id,
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(2),
        ]);

        $adminUser = User::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'admin'
        ]);
        $this->actingAs($adminUser);

        $expectedWorkStartTime = date('H:i', strtotime($work->start_time));
        $expectedWorkEndTime = date('H:i', strtotime($work->end_time));
        $expectedBreakStartTime = date('H:i', strtotime($break->start_time));
        $expectedBreakEndTime = date('H:i', strtotime($break->end_time));

        $response = $this->get('/admin/attendance/' . $work->id);
        $response->assertStatus(200);
        $response->assertSee('test');
        $response->assertSee('2025年');
        $response->assertSee('4月1日');
        $response->assertSee($expectedWorkStartTime);
        $response->assertSee($expectedWorkEndTime);
        $response->assertSee($expectedBreakStartTime);
        $response->assertSee($expectedBreakEndTime);
    }
    // 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    // 備考欄が未入力の場合のエラーメッセージが表示される
    #[\PHPUnit\Framework\Attributes\DataProvider('FixDataproviderValidation')]
    public function testFixValidationCheck(array $params, array $messages, bool $expect): void
    {
        $request = new ApplyRequest();
        $rules = $request->rules();
        $validator = Validator::make($params, $rules);
        $validator = $validator->setCustomMessages($request->messages());
        $result = $validator->passes();
        $this->assertEquals($expect, $result);
        $this->assertSame($messages, $validator->errors()->messages());
    }
    /**
     * バリデーションチェック用データ
     */
    public static function FixDataproviderValidation()
    {
        $now = now()->setDate(2025, 4, 1)->setTime(9, 0, 0);
        return [
            'work start after work end' => [
                [
                    'year' => '2025年',
                    'date' => '4月1日',
                    'work_start' => $now->copy()->addHours(8),
                    'work_end' => $now->copy()->addHours(7),
                    'remarks' => 'test'
                ],
                [
                    'work_end' => [
                        '出勤時刻もしくは退勤時刻が不適切な値です',
                    ],
                ],
                false
            ],
            'remarks null' => [
                [
                    'year' => '2025年',
                    'date' => '4月1日',
                    'work_start' => $now,
                    'work_end' => $now->copy()->addHours(8),
                    'remarks' => null
                ],
                [
                    'remarks' => [
                        '備考を記入してください',
                    ],
                ],
                false
            ],
        ];
    }
    //休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
    //休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function testFixBreakStartValidationCheck()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $work = Work::create([
            'user_id' => $user->id,
            'date' => '2025-04-01',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ]);

        $adminUser = User::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'admin'
        ]);
        $this->actingAs($adminUser);

        $data = [
            'year' => '2025年',
            'date' => '4月2日',
            'work_start' => '11:00',
            'work_end' => '19:00',
            'break_start' => '20:00',
            'break_end' => '21:00',
            'remarks' => 'テスト'
        ];
        $response = $this->post('/admin/attendance/' . $work->id, $data);
        $response->assertRedirect('/admin/attendance/' . $work->id);
        $response->assertSessionHasErrors(['break_start' => '休憩時間が勤務時間外です']);
    }

    //[ユーザー情報取得機能（管理者）]
    //管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる
    public function testAllUsersInformation()
    {
        $user1 = User::create([
            'name' => 'test1',
            'email' => 'test1@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $user2 = User::create([
            'name' => 'test2',
            'email' => 'test2@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $user3 = User::create([
            'name' => 'test3',
            'email' => 'test3@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $adminUser = User::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'admin'
        ]);
        $this->actingAs($adminUser);

        $response = $this->get('/admin/staff/list');
        $response->assertStatus(200);

        $response->assertSee('test1');
        $response->assertSee('test1@example.com');
        $response->assertSee('test2');
        $response->assertSee('test2@example.com');
        $response->assertSee('test3');
        $response->assertSee('test3@example.com');
    }
    //ユーザーの勤怠情報が正しく表示される
    public function testUsersAttendanceInformation()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $work1 = Work::create([
            'user_id' => $user->id,
            'date' => '2025-04-01',
            'start_time' => now(),
            'end_time' => now()->addHours(8),
        ]);
        $break1 = Breaking::create([
            'user_id' => $user->id,
            'work_id' => $work1->id,
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(2),
        ]);
        $work2 = Work::create([
            'user_id' => $user->id,
            'date' => '2025-04-02',
            'start_time' => now(),
            'end_time' => now()->addHours(8),
        ]);
        $break2 = Breaking::create([
            'user_id' => $user->id,
            'work_id' => $work2->id,
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(5),
        ]);

        $adminUser = User::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'admin'
        ]);
        $this->actingAs($adminUser);

        $response = $this->get('/admin/attendance/staff/' . $user->id . '/2025-04-01');
        $response->assertStatus(200);

        $response->assertSee('2025/04');
        $expectedStartTime1 = date('H:i', strtotime($work1->start_time));
        $expectedEndTime1 = date('H:i', strtotime($work1->end_time));
        $expectedBreakTime1 = '1:00';
        $expectedWorkTime1 = '7:00';

        $response->assertSee('04/01(火)');
        $response->assertSee($expectedStartTime1);
        $response->assertSee($expectedEndTime1);
        $response->assertSee($expectedBreakTime1);
        $response->assertSee($expectedWorkTime1);

        $expectedStartTime2 = date('H:i', strtotime($work2->start_time));
        $expectedEndTime2 = date('H:i', strtotime($work2->end_time));
        $expectedBreakTime2 = '4:00';
        $expectedWorkTime2 = '4:00';

        $response->assertSee('04/02(水)');
        $response->assertSee($expectedStartTime2);
        $response->assertSee($expectedEndTime2);
        $response->assertSee($expectedBreakTime2);
        $response->assertSee($expectedWorkTime2);
    }
    //「前月」を押下した時に表示月の前月の情報が表示される
    public function testUsersPreviousMonthAttendanceInformation()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $work1 = Work::create([
            'user_id' => $user->id,
            'date' => '2025-04-01',
            'start_time' => now(),
            'end_time' => now()->addHours(8),
        ]);
        $break1 = Breaking::create([
            'user_id' => $user->id,
            'work_id' => $work1->id,
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(2),
        ]);
        $work2 = Work::create([
            'user_id' => $user->id,
            'date' => '2025-04-02',
            'start_time' => now(),
            'end_time' => now()->addHours(8),
        ]);
        $break2 = Breaking::create([
            'user_id' => $user->id,
            'work_id' => $work2->id,
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(5),
        ]);
        $work3 = Work::create([
            'user_id' => $user->id,
            'date' => '2025-05-01',
            'start_time' => now(),
            'end_time' => now()->addHours(8),
        ]);
        $break3 = Breaking::create([
            'user_id' => $user->id,
            'work_id' => $work3->id,
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(2),
        ]);
        $work4 = Work::create([
            'user_id' => $user->id,
            'date' => '2025-05-02',
            'start_time' => now(),
            'end_time' => now()->addHours(8),
        ]);
        $break4 = Breaking::create([
            'user_id' => $user->id,
            'work_id' => $work4->id,
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(5),
        ]);

        $adminUser = User::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'admin'
        ]);
        $this->actingAs($adminUser);

        $response = $this->get('/admin/attendance/staff/' . $user->id . '/2025-04-01');
        $response->assertStatus(200);

        $response->assertSee('2025/04');
        $expectedStartTime1 = date('H:i', strtotime($work1->start_time));
        $expectedEndTime1 = date('H:i', strtotime($work1->end_time));
        $expectedBreakTime1 = '1:00';
        $expectedWorkTime1 = '7:00';

        $response->assertSee('04/01(火)');
        $response->assertSee($expectedStartTime1);
        $response->assertSee($expectedEndTime1);
        $response->assertSee($expectedBreakTime1);
        $response->assertSee($expectedWorkTime1);

        $expectedStartTime2 = date('H:i', strtotime($work2->start_time));
        $expectedEndTime2 = date('H:i', strtotime($work2->end_time));
        $expectedBreakTime2 = '4:00';
        $expectedWorkTime2 = '4:00';

        $response->assertSee('04/02(水)');
        $response->assertSee($expectedStartTime2);
        $response->assertSee($expectedEndTime2);
        $response->assertSee($expectedBreakTime2);
        $response->assertSee($expectedWorkTime2);
    }
    //「翌月」を押下した時に表示月の翌月の情報が表示される
    public function testUsersNextMonthAttendanceInformation()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $work1 = Work::create([
            'user_id' => $user->id,
            'date' => '2025-04-01',
            'start_time' => now(),
            'end_time' => now()->addHours(8),
        ]);
        $break1 = Breaking::create([
            'user_id' => $user->id,
            'work_id' => $work1->id,
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(2),
        ]);
        $work2 = Work::create([
            'user_id' => $user->id,
            'date' => '2025-04-02',
            'start_time' => now(),
            'end_time' => now()->addHours(8),
        ]);
        $break2 = Breaking::create([
            'user_id' => $user->id,
            'work_id' => $work2->id,
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(5),
        ]);
        $work3 = Work::create([
            'user_id' => $user->id,
            'date' => '2025-05-01',
            'start_time' => now(),
            'end_time' => now()->addHours(8),
        ]);
        $break3 = Breaking::create([
            'user_id' => $user->id,
            'work_id' => $work3->id,
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(2),
        ]);
        $work4 = Work::create([
            'user_id' => $user->id,
            'date' => '2025-05-02',
            'start_time' => now(),
            'end_time' => now()->addHours(8),
        ]);
        $break4 = Breaking::create([
            'user_id' => $user->id,
            'work_id' => $work4->id,
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(5),
        ]);

        $adminUser = User::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'admin'
        ]);
        $this->actingAs($adminUser);

        $response = $this->get('/admin/attendance/staff/' . $user->id . '/2025-05-01');
        $response->assertStatus(200);

        $response->assertSee('2025/05');
        $expectedStartTime3 = date('H:i', strtotime($work3->start_time));
        $expectedEndTime3 = date('H:i', strtotime($work3->end_time));
        $expectedBreakTime3 = '1:00';
        $expectedWorkTime3 = '7:00';

        $response->assertSee('05/01(木)');
        $response->assertSee($expectedStartTime3);
        $response->assertSee($expectedEndTime3);
        $response->assertSee($expectedBreakTime3);
        $response->assertSee($expectedWorkTime3);

        $expectedStartTime4 = date('H:i', strtotime($work4->start_time));
        $expectedEndTime4 = date('H:i', strtotime($work4->end_time));
        $expectedBreakTime4 = '4:00';
        $expectedWorkTime4 = '4:00';

        $response->assertSee('05/02(金)');
        $response->assertSee($expectedStartTime4);
        $response->assertSee($expectedEndTime4);
        $response->assertSee($expectedBreakTime4);
        $response->assertSee($expectedWorkTime4);
    }
    // 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
    public function testUsersAttendanceInformationDetail()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $work = Work::create([
            'user_id' => $user->id,
            'date' => '2025-04-01',
            'start_time' => now(),
            'end_time' => now()->addHours(8),
        ]);
        $break = Breaking::create([
            'user_id' => $user->id,
            'work_id' => $work->id,
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(2),
        ]);

        $adminUser = User::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'admin'
        ]);
        $this->actingAs($adminUser);

        $response = $this->get('/admin/attendance/' . $user->id);

        $expectedWorkStartTime = date('H:i', strtotime($work->start_time));
        $expectedWorkEndTime = date('H:i', strtotime($work->end_time));
        $expectedBreakStartTime = date('H:i', strtotime($break->start_time));
        $expectedBreakEndTime = date('H:i', strtotime($break->end_time));

        $response->assertSee('test');
        $response->assertSee('2025年');
        $response->assertSee('4月1日');
        $response->assertSee($expectedWorkStartTime);
        $response->assertSee($expectedWorkEndTime);
        $response->assertSee($expectedBreakStartTime);
        $response->assertSee($expectedBreakEndTime);
    }

    //[勤怠情報修正機能（管理者）]
    //承認待ちの修正申請が全て表示されている
    public function testAdminWaitingApplicationsDisplayed()
    {
        $user1 = User::create([
            'name' => 'test1',
            'email' => 'test1@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $user2 = User::create([
            'name' => 'test2',
            'email' => 'test2@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $work1 = Work::create([
            'user_id' => $user1->id,
            'date' => '2025-04-01',
            'start_time' => now(),
            'end_time' => now()->addHours(8),
        ]);
        $break1 = Breaking::create([
            'user_id' => $user1->id,
            'work_id' => $work1->id,
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(2),
        ]);
        $work2 = Work::create([
            'user_id' => $user2->id,
            'date' => '2025-04-02',
            'start_time' => now(),
            'end_time' => now()->addHours(8),
        ]);
        $break2 = Breaking::create([
            'user_id' => $user2->id,
            'work_id' => $work2->id,
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(5),
        ]);

        $workApplication1 = WorkingApplication::create([
            'user_id' => $user1->id,
            'work_id' => $work1->id,
            'date' => '2025-04-03',
            'start_time' => '11:00:00',
            'end_time' => '19:00:00',
            'remarks' => 'テスト1',
            'status' => '承認待ち'
        ]);
        $workApplication2 = WorkingApplication::create([
            'user_id' => $user2->id,
            'work_id' => $work2->id,
            'date' => '2025-04-04',
            'start_time' => '12:00:00',
            'end_time' => '20:00:00',
            'remarks' => 'テスト2',
            'status' => '承認待ち'
        ]);

        $adminUser = User::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'admin'
        ]);
        $this->actingAs($adminUser);

        $response = $this->get('/stamp_correction_request/list');
        $response->assertStatus(200);
        $response->assertViewIs('admin_applications');

        $response->assertSee('承認待ち');
        $response->assertSee('test1');
        $response->assertSee('2025/04/01');
        $response->assertSee('テスト1');
        $response->assertSee('2025/04/03');

        $response->assertSee('承認待ち');
        $response->assertSee('test2');
        $response->assertSee('2025/04/02');
        $response->assertSee('テスト2');
        $response->assertSee('2025/04/04');
    }
    //承認済みの修正申請が全て表示されている
    public function testAdminCompletedApplicationsDisplayed()
    {
        $user1 = User::create([
            'name' => 'test1',
            'email' => 'test1@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $user2 = User::create([
            'name' => 'test2',
            'email' => 'test2@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $work1 = Work::create([
            'user_id' => $user1->id,
            'date' => '2025-04-01',
            'start_time' => now(),
            'end_time' => now()->addHours(8),
        ]);
        $break1 = Breaking::create([
            'user_id' => $user1->id,
            'work_id' => $work1->id,
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(2),
        ]);
        $work2 = Work::create([
            'user_id' => $user2->id,
            'date' => '2025-04-02',
            'start_time' => now(),
            'end_time' => now()->addHours(8),
        ]);
        $break2 = Breaking::create([
            'user_id' => $user2->id,
            'work_id' => $work2->id,
            'start_time' => now()->addHours(1),
            'end_time' => now()->addHours(5),
        ]);

        $workApplication1 = WorkingApplication::create([
            'user_id' => $user1->id,
            'work_id' => $work1->id,
            'date' => '2025-04-03',
            'start_time' => '11:00:00',
            'end_time' => '19:00:00',
            'remarks' => 'テスト1',
            'status' => '承認済み'
        ]);
        $workApplication2 = WorkingApplication::create([
            'user_id' => $user2->id,
            'work_id' => $work2->id,
            'date' => '2025-04-04',
            'start_time' => '12:00:00',
            'end_time' => '20:00:00',
            'remarks' => 'テスト2',
            'status' => '承認済み'
        ]);

        $adminUser = User::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'admin'
        ]);
        $this->actingAs($adminUser);

        $response = $this->get('/stamp_correction_request/list');
        $response->assertStatus(200);
        $response->assertViewIs('admin_applications');

        $response->assertSee('承認済み');
        $response->assertSee('test1');
        $response->assertSee('2025/04/01');
        $response->assertSee('テスト1');
        $response->assertSee('2025/04/03');

        $response->assertSee('承認済み');
        $response->assertSee('test2');
        $response->assertSee('2025/04/02');
        $response->assertSee('テスト2');
        $response->assertSee('2025/04/04');
    }
    //修正申請の詳細内容が正しく表示されている
    public function testAdminApplicationsDetail()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $work = Work::create([
            'user_id' => $user->id,
            'date' => '2025-04-01',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ]);
        $workApplication = WorkingApplication::create([
            'user_id' => $user->id,
            'work_id' => $work->id,
            'date' => '2025-04-04',
            'start_time' => '11:00:00',
            'end_time' => '19:00:00',
            'remarks' => 'テスト',
            'status' => '承認待ち'
        ]);
        $breakApplication = BreakingApplication::create([
            'user_id' => $user->id,
            'work_id' => $work->id,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);

        $adminUser = User::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'admin'
        ]);
        $this->actingAs($adminUser);

        $response = $this->get('/stamp_correction_request/approve/' . $workApplication->id);
        $response->assertStatus(200);
        $response->assertViewIs('approve');
        $response->assertSee('test');
        $response->assertSee('2025年');
        $response->assertSee('4月4日');
        $response->assertSee('11:00');
        $response->assertSee('19:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
        $response->assertSee('テスト');
    }
    //修正申請の承認処理が正しく行われる
    public function testApprove()
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        $work = Work::create([
            'user_id' => $user->id,
            'date' => '2025-04-01',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ]);
        $workApplication = WorkingApplication::create([
            'user_id' => $user->id,
            'work_id' => $work->id,
            'date' => '2025-04-04',
            'start_time' => '11:00:00',
            'end_time' => '19:00:00',
            'remarks' => 'テスト',
            'status' => '承認待ち'
        ]);
        $breakApplication = BreakingApplication::create([
            'user_id' => $user->id,
            'work_id' => $work->id,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);
        $data = [
            'year' => '2025年',
            'date' => '4月4日',
            'work_start' => '11:00',
            'work_end' => '19:00',
            'break_start' => '12:00',
            'break_end' => '13:00',
            'remarks' => 'テスト'
        ];

        $adminUser = User::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'role' => 'admin'
        ]);
        $this->actingAs($adminUser);

        $response = $this->post('/stamp_correction_request/approve/' . $work->id, $data);
        $this->assertDatabaseHas('works', [
            'user_id' => $user->id,
            'id' => $work->id,
            'date' => '2025-04-04',
            'start_time' => '11:00:00',
            'end_time' => '19:00:00',
            'remarks' => 'テスト',
        ]);
        $this->assertDatabaseHas('working_applications', [
            'user_id' => $user->id,
            'work_id' => $work->id,
            'date' => '2025-04-04',
            'start_time' => '11:00:00',
            'end_time' => '19:00:00',
            'remarks' => 'テスト',
            'status' => '承認済み'
        ]);
    }
}