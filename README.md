# coachtech 勤怠管理アプリ

## 環境構築

### Dicker ビルド

1. `git clone git@github.com:Mai1933/Attendanse-Management.git`
2. `docker-compose up -d --build`
3. ＊MySQL は、OS によって起動しない場合があるので,それぞれの PC に合わせて docker-compose.yml ファイルを編集してください。

### Laravel 環境構築

1. `docker-compose exec php bash`
2. `composer install`
3. .env.example ファイルから.env を作成し、環境変数を変更  
   (1)DB_PORT から DB_PASSWORD までのコメントアウトを解除  
   (2)以下を該当箇所へコピペ

```
APP_TIMEZONE=Asia/Tokyo
APP_URL=http://localhost

APP_LOCALE=ja
APP_FALLBACK_LOCALE=ja
APP_FAKER_LOCALE=ja_JP

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=test
MAIL_PASSWORD=pass
MAIL_ENCRYPTION=smtp
MAIL_FROM_ADDRESS="test@test"
MAIL_FROM_NAME="${APP_NAME}"
```

4. `php artisan key:generate`
5. `php artisan migrate`
6. `php artisan db:seed`
7. `php artisan storage:link`

### テスト環境構築

1.  `composer require phpunit/phpunit --dev`
2.  .env.example をコピーして.env.testing を作成
3.  以下をコピペ

```
APP_NAME=Laravel
APP_ENV=test
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=Asia/Tokyo
APP_URL=http://localhost


APP_LOCALE=ja
APP_FALLBACK_LOCALE=ja
APP_FAKER_LOCALE=ja_JP

APP_MAINTENANCE_DRIVER=file

PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=demo_test
DB_USERNAME=root
DB_PASSWORD=root

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=test
MAIL_PASSWORD=pass
MAIL_ENCRYPTION=smtp
MAIL_FROM_ADDRESS="test@test"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"
```

4.  phpunit.xml の該当箇所に以下をコピペ

```
 <php>
        <env name="APP_ENV" value="testing"/>
        <env name="APP_MAINTENANCE_DRIVER" value="file"/>
        <env name="BCRYPT_ROUNDS" value="4"/>
        <env name="CACHE_STORE" value="array"/>
        <env name="DB_CONNECTION" value="mysql_test"/>
        <env name="DB_DATABASE" value="demo_test"/>
        <env name="MAIL_MAILER" value="array"/>
        <env name="PULSE_ENABLED" value="false"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
        <env name="SESSION_DRIVER" value="array"/>
        <env name="TELESCOPE_ENABLED" value="false"/>
    </php>
```

5.  src/config/database.php に以下を追加

```
    'mysql_test' => [
      'driver' => 'mysql',
      'url' => env('DB_URL'),
      'host' => env('DB_HOST', '127.0.0.1'),
      'port' => env('DB_PORT', '3306'),
      'database' => 'demo_test',
      'username' => 'root',
      'password' => 'root',
      'unix_socket' => env('DB_SOCKET', ''),
      'charset' => env('DB_CHARSET', 'utf8mb4'),
      'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
      'prefix' => '',
      'prefix_indexes' => true,
      'strict' => true,
      'engine' => null,
      'options' => extension_loaded('pdo_mysql') ? array_filter([
    PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
    ]) : [],
    ],
```

6.  `php artisan key:generate --env=testing`

## 使用技術

- PHP 8.4.3
- Laravel 11.42.1
- MySQL 8.4.4
- PHPUnit 11.5.15

## ER 図(表示されない場合は再読み込みしてください）

![Image](https://github.com/user-attachments/assets/8c66ebb9-6157-41ce-b14c-dfb652cfa400)

## URL

- 開発環境：http://localhost/login
- phpMyAdmin:http://localhost:8080/
- mailhog:http://localhost:8025/

## 注意

- 登録しているユーザーに関して、  
  一般ユーザー：名前とメールアドレスは画面設計の通り、パスワードは「password」  
  管理者ユーザー：名前は admin,メールアドレスはadmin@email.com,パスワードは「password」としております。

- 代表者として「西伶奈,reina.n@coachtech.com,password」により多くの勤怠及び申請情報を登録しております。各種テストの際にお役立てください。

- 拡張機能で ChatGPT のサイドバーをオンにしている場合、画面下部のボタンが押せない事象が発生することがあります。一時的に拡張機能をアンインストールすると解消します。

- 変更申請を行ったものの、未承認である勤務の詳細確認について(一般ユーザー、管理者共通)
  申請前の勤務内容を確認したい場合：「勤怠一覧画面」より該当勤務の詳細ボタンを押下
  申請自体の内容を確認したい場合:「申請一覧画面」より該当申請の詳細ボタンを押下
  としております。

- テストに関して、AllControllerTest に全てのテストコードを記述しています。  
  //【項目】  
  //テスト内容１  
  //テスト内容２  
  テスト内容１及び２に関するメソッド  
  の順で表記しています。
