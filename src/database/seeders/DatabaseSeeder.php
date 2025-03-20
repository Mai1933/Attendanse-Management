<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([AdminUsersTableSeeder::class]);
        $this->call([GeneralUsersTableSeeder::class]);
        $this->call([WorksTableSeeder::class]);
        $this->call([BreakingsTableSeeder::class]);
        $this->call([WorkingApplicationsTableSeeder::class]);
        $this->call([BreakingApplicationsTableSeeder::class]);
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
