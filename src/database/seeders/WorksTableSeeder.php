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
    }
}
