<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Kingdom;
use App\Models\User; // <-- Import User model
use App\Models\KingdomMembership; // <-- Import KingdomMembership model
use Illuminate\Support\Facades\DB; // <-- Import DB Facade for transaction
use Illuminate\Support\Facades\Log; // <-- Import Log facade for debugging

class KingdomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Fetch the kingdom names/slugs based on your current data dump
            $kingdomNamesToClear = ['Iron Tusk Clan', 'Serpents of the Sea', 'Whispering Woods Tribe (Inactive)'];
            $kingdomSlugsToClear = ['iron-tusk-clan', 'serpents-of-the-sea', 'whispering-woods-tribe-inactive'];

            // Clear potential previous seeded kingdoms using existing names/slugs
            // Using forceDelete() in case SoftDeletes is used and we want a clean slate
            Kingdom::whereIn('slug', $kingdomSlugsToClear)->forceDelete();
            Log::info("KingdomSeeder: Cleared existing kingdoms matching slugs: " . implode(', ', $kingdomSlugsToClear));

            // Find the first user to assign as King (ensure at least one user exists)
            $firstUser = User::orderBy('id', 'asc')->first();
            $kingUserId = null;

            if ($firstUser) {
                $kingUserId = $firstUser->id;
                Log::info("KingdomSeeder: Found user ID {$kingUserId} ({$firstUser->name}) to assign as King.");
            } else {
                Log::warning("KingdomSeeder: No users found in the database. Cannot assign a King. Please ensure a user exists (e.g., run UserSeeder or register manually).");
                // Proceeding without assigning a king if no user exists.
            }

            // Create Active Kingdom 1 (and assign King if found)
            $kingdom1 = Kingdom::create([
                'name' => 'Iron Tusk Clan', // Match name from your dump
                'slug' => 'iron-tusk-clan', // Match slug from your dump
                'description' => 'Fierce warriors from the northern fjords, known for their resilience.', // Match desc
                'king_user_id' => $kingUserId, // Assign the first user's ID here
                'is_active' => true,
                // emblem_url is nullable, keep as null unless you have one
            ]);
            Log::info("KingdomSeeder: Created Kingdom '{$kingdom1->name}' (ID {$kingdom1->id}) with King ID: " . ($kingUserId ?? 'None'));

            if ($kingUserId) {
                KingdomMembership::create([
                    'user_id' => $kingUserId,
                    'kingdom_id' => $kingdom1->id,
                    'role' => 'king',
                    'joined_at' => now(),
                ]);
                Log::info("KingdomSeeder: Created KingdomMembership for King ID {$kingUserId} in Kingdom ID {$kingdom1->id}.");
            }

            // Create Active Kingdom 2 (without a king initially)
            $kingdom2 = Kingdom::create([
                'name' => 'Serpents of the Sea', // Match name from your dump
                'slug' => 'serpents-of-the-sea', // Match slug from your dump
                'description' => 'Master sailors and raiders, controlling the western isles.', // Match desc
                'king_user_id' => null,
                'is_active' => true,
            ]);
            Log::info("KingdomSeeder: Created Kingdom '{$kingdom2->name}' (ID {$kingdom2->id}) with King ID: None");

            // Create Inactive Kingdom
            $kingdom3 = Kingdom::create([
                'name' => 'Whispering Woods Tribe (Inactive)', // Match name from your dump
                'slug' => 'whispering-woods-tribe-inactive', // Match slug from your dump
                'description' => 'An old tribe, currently inactive on the platform.', // Match desc
                'king_user_id' => null,
                'is_active' => false,
            ]);
            Log::info("KingdomSeeder: Created Kingdom '{$kingdom3->name}' (ID {$kingdom3->id}) (Inactive) with King ID: None");

        }); // End transaction
    }
}