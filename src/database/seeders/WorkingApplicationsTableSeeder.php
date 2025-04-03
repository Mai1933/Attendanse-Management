<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class WorkingApplicationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $param = [
            'user_id' => 2,
            'work_id' => 1,
            'date' => '20250320',
            'start_time' => '11:00:00',
            'end_time' => '19:00:00',
            'remarks' => '承認待ちテスト',
            'status' => '承認待ち'
        ];
        DB::table('working_applications')->insert($param);
        $param = [
            'user_id' => 2,
            'work_id' => 8,
            'date' => '20250321',
            'start_time' => '11:00:00',
            'end_time' => '19:00:00',
            'remarks' => '承認待ちテスト2',
            'status' => '承認待ち'
        ];
        DB::table('working_applications')->insert($param);
        $param = [
            'user_id' => 4,
            'work_id' => 3,
            'date' => '20250321',
            'start_time' => '11:00:00',
            'end_time' => '19:00:00',
            'remarks' => '承認待ちテスト3',
            'status' => '承認待ち'
        ];
        DB::table('working_applications')->insert($param);
        $param = [
            'user_id' => 2,
            'work_id' => 7,
            'date' => '20250420',
            'start_time' => '11:00:00',
            'end_time' => '19:00:00',
            'remarks' => '承認済みテスト',
            'status' => '承認済み'
        ];
        DB::table('working_applications')->insert($param);
        $param = [
            'user_id' => 2,
            'work_id' => 9,
            'date' => '20250420',
            'start_time' => '11:00:00',
            'end_time' => '19:00:00',
            'remarks' => '承認済みテスト2',
            'status' => '承認済み'
        ];
        DB::table('working_applications')->insert($param);
        $param = [
            'user_id' => 3,
            'work_id' => 2,
            'date' => '20250420',
            'start_time' => '11:00:00',
            'end_time' => '19:00:00',
            'remarks' => '承認済みテスト3',
            'status' => '承認済み'
        ];
        DB::table('working_applications')->insert($param);
    }
}
