<?php

namespace Database\Seeders;

// Import the necessary classes
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // <-- Ensures the DB facade is found
use Carbon\Carbon;                   // <-- Ensures the Carbon class is found

class HonourRanksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Inserts the initial set of honour ranks into the database.
     */
    public function run(): void
    {
        // Clear existing ranks using delete() instead of truncate()
        // This respects foreign key constraints (like users.honour_rank_id)
        DB::table('honour_ranks')->delete(); // <-- CHANGED THIS LINE

        // Get current timestamp for created_at/updated_at
        $now = Carbon::now(); // This requires the 'use Carbon\Carbon;' line above

        // Define the ranks to be inserted
        $ranks = [
            [
                'name' => 'Thrall',
                'level' => 0,
                'min_days_in_kingdom' => null, // Or 0 if applicable
                'min_saga_contributions' => null,
                'min_feasts_attended' => null,
                'description' => 'A newcomer, yet to prove their worth.',
                'permissions' => json_encode([]), // No special permissions initially
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Karl',
                'level' => 1,
                'min_days_in_kingdom' => 7,
                'min_saga_contributions' => 0,
                'min_feasts_attended' => 1,
                'description' => 'A recognized freeman, finding their place.',
                'permissions' => json_encode(['can_attend_council' => true]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Huskarl',
                'level' => 2,
                'min_days_in_kingdom' => 30,
                'min_saga_contributions' => 2,
                'min_feasts_attended' => 5,
                'description' => 'A trusted household warrior, loyal and capable.',
                'permissions' => json_encode(['can_attend_council' => true, 'can_view_tribe_plans' => true]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
             [
                'name' => 'Jarlsguard',
                'level' => 3,
                'min_days_in_kingdom' => 90,
                'min_saga_contributions' => 5,
                'min_feasts_attended' => 10,
                'description' => 'Elite guard, proven in battle and counsel.',
                'permissions' => json_encode(['can_attend_council' => true, 'can_view_tribe_plans' => true, 'can_propose_saga' => true]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
             [
                'name' => 'High Thane',
                'level' => 4,
                'min_days_in_kingdom' => 180,
                'min_saga_contributions' => 10,
                'min_feasts_attended' => 20,
                'description' => 'A respected leader, advisor to the King/Jarl.',
                'permissions' => json_encode(['can_attend_council' => true, 'can_view_tribe_plans' => true, 'can_propose_saga' => true, 'can_lead_raid' => true]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Add more ranks as needed
        ];

        // Insert the data into the 'honour_ranks' table
        DB::table('honour_ranks')->insert($ranks); // This requires the 'use Illuminate\Support\Facades\DB;' line above.

        // Optionally, add output to the console:
        $this->command->info('Honour Ranks table seeded!');
    }
}