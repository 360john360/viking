<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tribe;
use App\Models\Kingdom;
use App\Models\User;
use App\Models\TribeMembership; // <-- Import TribeMembership model
use Illuminate\Support\Facades\Log;

class TribeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Attempt to find an existing active Kingdom
        $kingdom = Kingdom::where('name', 'Iron Tusk Clan')->where('is_active', true)->first();
        if (!$kingdom) {
            $kingdom = Kingdom::where('is_active', true)->first();
        }

        if (!$kingdom) {
            Log::info('TribeSeeder: No active kingdom found. Skipping tribe creation.');
            return;
        }

        // Attempt to find an existing User to act as a leader
        $leader = User::where('email', 'admin@example.com')->first();
        if (!$leader) {
            $leader = User::first();
        }

        if (!$leader) {
            Log::info('TribeSeeder: No user found to act as leader. Skipping tribe creation.');
            return;
        }

        Tribe::firstOrCreate(
            ['slug' => 'iron-guard'],
            [
                'name' => 'Iron Guard',
                'description' => 'The primary defenders of Iron Tusk Clan.',
                'leader_user_id' => $leader->id,
                'kingdom_id' => $kingdom->id,
                'is_active' => true,
            ]
        );

        // Re-fetch the tribe to ensure we have the ID if it was just created
        $tribe = Tribe::where('slug', 'iron-guard')->first();

        if ($tribe && $leader) {
            TribeMembership::firstOrCreate(
                [
                    'user_id' => $leader->id,
                    'tribe_id' => $tribe->id,
                ],
                [
                    'role' => 'leader',
                    'joined_at' => now(),
                ]
            );
            Log::info("TribeSeeder: Created TribeMembership for Leader ID {$leader->id} in Tribe ID {$tribe->id}.");
        }
    }
}
