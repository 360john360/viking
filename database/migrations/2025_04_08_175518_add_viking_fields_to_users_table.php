<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds custom fields related to kingdoms, tribes, and honour to the users table.
     */
    public function up(): void
    {
        // Use Schema::table() to modify the existing 'users' table
        Schema::table('users', function (Blueprint $table) {
            // Add current_kingdom_id (FK -> kingdoms.id, Nullable)
            // Place it after a common existing column like 'remember_token' or 'id' for organization.
            // onDelete('set null') - If kingdom deleted, user is no longer associated with it.
            $table->foreignId('current_kingdom_id')
                  ->nullable()
                  ->after('remember_token') // Adjust 'after' column if needed based on your default users table
                  ->constrained('kingdoms')
                  ->onDelete('set null');

            // Add current_tribe_id (FK -> tribes.id, Nullable)
            // onDelete('set null') - If tribe deleted, user is no longer associated.
            $table->foreignId('current_tribe_id')
                  ->nullable()
                  ->after('current_kingdom_id')
                  ->constrained('tribes')
                  ->onDelete('set null');

            // Add honour_rank_id (FK -> honour_ranks.id, Nullable)
            // onDelete('set null') - If rank definition deleted, set user's rank to NULL (or a default).
            $table->foreignId('honour_rank_id')
                  ->nullable()
                  ->after('current_tribe_id')
                  ->constrained('honour_ranks')
                  ->onDelete('set null');

            // Add is_king_candidate_verified (BOOLEAN, Default: false) - From original schema
             $table->boolean('is_king_candidate_verified')
                   ->default(false)
                   ->after('honour_rank_id'); // Place it after the other new fields

             // Add site admin/mod flags if not already present (check default users migration first)
             // Assuming they are NOT in the default migration:
             if (!Schema::hasColumn('users', 'is_site_admin')) {
                $table->boolean('is_site_admin')->default(false)->after('is_king_candidate_verified');
             }
             if (!Schema::hasColumn('users', 'is_site_moderator')) {
                $table->boolean('is_site_moderator')->default(false)->after('is_site_admin');
             }

        });
    }

    /**
     * Reverse the migrations.
     *
     * Removes the custom fields and their foreign keys from the users table.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign keys first (using array syntax for default constraint names)
            $table->dropForeign(['current_kingdom_id']);
            $table->dropForeign(['current_tribe_id']);
            $table->dropForeign(['honour_rank_id']);

            // Then drop the columns
            $table->dropColumn([
                'current_kingdom_id',
                'current_tribe_id',
                'honour_rank_id',
                'is_king_candidate_verified',
                // Only drop admin/mod if we added them here
                // Add checks if needed: Schema::hasColumn... then drop
                 'is_site_admin',
                 'is_site_moderator'
            ]);
        });
    }
};
