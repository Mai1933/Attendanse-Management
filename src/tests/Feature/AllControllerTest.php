<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Testing\WithFaker;
use App\Http\Requests\ApplyRequest;
use App\Http\Requests\EmailVerificationRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;

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
    #[\PHPUnit\Framework\Attributes\DataProvider('dataproviderValidation')]
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
    public static function dataproviderValidation()
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
}
