<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class WorksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user_ids = [1, 2, 3, 4, 5, 6];

        foreach ($user_ids as $user_id) {
            $param = [
                'user_id' => $user_id,
                'date' => '20250307',
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
            ];
            DB::table('works')->insert($param);
        }

        $dates = [20250311, 20250312, 20250313, 20250314, 20250315, 20250316, 20250416, 20250417,];
        foreach ($dates as $date) {
            $param = [
                'user_id' => 1,
                'date' => $date,
                'start_time' => '07:00:00',
                'end_time' => '18:24:00',
            ];
            DB::table('works')->insert($param);
        }
    }
}
