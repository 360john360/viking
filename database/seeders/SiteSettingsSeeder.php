<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // <-- Import DB facade
use Carbon\Carbon;                   // <-- Import Carbon for timestamps

class SiteSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Inserts or updates default site settings.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $settings = [
            [
                'key' => 'maintenance_mode',
                'value' => '0', // Use '0' for false, '1' for true
                'description' => 'Enable/Disable site-wide maintenance mode (0=off, 1=on).',
            ],
            [
                'key' => 'default_kingdom_cooldown_days',
                'value' => '7',
                'description' => 'Default number of days a user must wait after leaving a kingdom before joining another.',
            ],
             [
                'key' => 'allow_public_sagas',
                'value' => '1',
                'description' => 'Allow Sagas (wiki pages) to be made public after site admin approval (0=no, 1=yes).',
            ],
             [
                'key' => 'max_tribes_per_kingdom',
                'value' => '10', // Example limit
                'description' => 'Maximum number of tribes allowed within a single kingdom.',
            ],
            // Add other essential default settings here
        ];

        foreach ($settings as $setting) {
            DB::table('site_settings')->updateOrInsert(
                ['key' => $setting['key']], // Condition to find the row
                [                         // Data to insert or update
                    'value' => $setting['value'],
                    'description' => $setting['description'],
                    'created_at' => $now, // Set created_at only if inserting (updateOrInsert handles this)
                    'updated_at' => $now, // Always update updated_at
                ]
            );
        }

        $this->command->info('Site Settings table seeded/updated!');
    }
}