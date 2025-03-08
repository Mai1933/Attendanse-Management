<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class GeneralUsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $param = [
            'name' => '西 伶奈',
            'password' => bcrypt('password'),
            'email' => 'reina.n@coachtech.com',
            'email_verified_at' => now(),
        ];
        DB::table('general_users')->insert($param);
        $param = [
            'name' => '山田 太郎',
            'password' => bcrypt('password'),
            'email' => 'taro.y@coachtech.com',
            'email_verified_at' => now(),
        ];
        DB::table('general_users')->insert($param);
        $param = [
            'name' => '増田 一世',
            'password' => bcrypt('password'),
            'email' => 'issei.m@coachtech.com',
            'email_verified_at' => now(),
        ];
        DB::table('general_users')->insert($param);
        $param = [
            'name' => '山本 敬吉',
            'password' => bcrypt('password'),
            'email' => 'keikichi.y@coachtech.com',
            'email_verified_at' => now(),
        ];
        DB::table('general_users')->insert($param);
        $param = [
            'name' => '秋田 朋美',
            'password' => bcrypt('password'),
            'email' => 'tomomi.a@coachtech.com',
            'email_verified_at' => now(),
        ];
        DB::table('general_users')->insert($param);
        $param = [
            'name' => '中西 教夫',
            'password' => bcrypt('password'),
            'email' => 'norio.n@coachtech.com',
            'email_verified_at' => now(),
        ];
        DB::table('general_users')->insert($param);
    }
}
