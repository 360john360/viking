<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create(); // Ensure this remains commented if UserSeeder is primary

        // Call the seeder(s) you want to run
        $this->call([
            UserSeeder::class,          // Ensures users exist first
            HonourRanksSeeder::class,   // Independent, can run early
            SiteSettingsSeeder::class,  // Independent, can run early
            KingdomSeeder::class,       // Depends on Users
            TribeSeeder::class,         // Depends on Users and Kingdoms
        ]);
    }
}