<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class BreakingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ids = [1, 2, 3, 4, 5, 6];

        foreach ($ids as $id) {
            $param = [
                'user_id' => $id,
                'work_id' => $id,
                'start_time' => '12:00:00',
                'end_time' => '13:00:00',
            ];
            DB::table('breakings')->insert($param);
        }
        $param = [
            'user_id' => 1,
            'work_id' => 1,
            'start_time' => '14:30:00',
            'end_time' => '15:13:00'
        ];
        DB::table('breakings')->insert($param);
        $param = [
            'user_id' => 1,
            'work_id' => 7,
            'start_time' => '14:30:00',
            'end_time' => '15:13:00'
        ];
        DB::table('breakings')->insert($param);
    }
}
