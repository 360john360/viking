<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Changes the role enum to member, officer, leader.
     * Requires doctrine/dbal: composer require doctrine/dbal
     */
    public function up(): void
    {
        Schema::table('tribe_memberships', function (Blueprint $table) {
            // Important: Ensure default value is valid within the new set
            // Change the enum definition. Existing 'member' stays, 'thane'/'elder' removed.
            $table->enum('role', ['member', 'officer', 'leader'])
                  ->default('member')
                  ->comment('Role within the tribe (member=R1-3, officer=R4, leader=R5/Thane/Chief)')
                  ->change(); // Use change() method
        });
    }

    /**
     * Reverse the migrations.
     * Reverts the enum back to the previous definition.
     */
    public function down(): void
    {
         Schema::table('tribe_memberships', function (Blueprint $table) {
             // Revert back to old enum values if needed
             $table->enum('role', ['member', 'elder', 'thane'])
                   ->default('member')
                   ->comment('') // Reset comment if desired
                   ->change();
         });
    }
};
