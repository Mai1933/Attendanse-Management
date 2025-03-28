<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Testing\WithFaker;
use App\Http\Requests\ApplyRequest;
use App\Http\Requests\EmailVerificationRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\Work;
use App\Models\Breaking;
use Tests\TestCase;

class AllControllerTest extends TestCase
{
    use RefreshDatabase;
    // [認証機能(一般ユーザー)]
    // 名前が未入力の場合、バリデーションメッセージが表示される
    // メールアドレスが未入力の場合、バリデーションメッセージが表示される
    // パスワードが8文字未満の場合、バリデーションメッセージが表示される
    // パスワードが一致しない場合、バリデーションメッセージが表示される
    // パスワードが未入力の場合、バリデーションメッセージが表示される
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
            'start_time' => now()->subHours(8),
            'end_time' => now()
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
            'start_time' => now()->subHours(8),
            'end_time' => now()
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
}