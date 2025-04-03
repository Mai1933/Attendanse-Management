<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BreakingApplicationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $param = [
            'user_id' => 2,
            'work_id' => 1,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ];
        DB::table('breaking_applications')->insert($param);
        $param = [
            'user_id' => 2,
            'work_id' => 1,
            'start_time' => '14:00:00',
            'end_time' => '14:35:00',
        ];
        DB::table('breaking_applications')->insert($param);
        $param = [
            'user_id' => 2,
            'work_id' => 7,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ];
        DB::table('breaking_applications')->insert($param);
    }
}
