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
        // \App\Models\User::factory(10)->create();

        // Call the seeder(s) you want to run
        $this->call([
            HonourRanksSeeder::class,
            SiteSettingsSeeder::class,
            KingdomSeeder::class, // <-- Added this line
        ]);
    }
}