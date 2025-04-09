<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Defines the structure of the 'tribe_memberships' table, linking users to tribes.
     */
    public function up(): void
    {
        Schema::create('tribe_memberships', function (Blueprint $table) {
            // id (PK)
            $table->id();

            // user_id (FK -> users.id, UNIQUE) - The user who is the member.
            // ->unique() is CRITICAL - enforces single tribe membership rule.
            // onDelete('cascade') means if user is deleted, their membership is removed.
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');

            // tribe_id (FK -> tribes.id) - The tribe the user belongs to.
            // onDelete('cascade') means if the tribe is deleted, its memberships are removed.
            $table->foreignId('tribe_id')->constrained('tribes')->onDelete('cascade');

            // role (ENUM, Default: 'member', Indexed) - Role within the tribe ('Warrior' maps to 'member').
            $table->enum('role', ['member', 'elder', 'thane'])->default('member');
            $table->index('role'); // Add index for faster role lookups

            // joined_at (TIMESTAMP) - When the user officially joined this tribe.
            $table->timestamp('joined_at');

            // created_at, updated_at (TIMESTAMP) - For the membership record itself.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the 'tribe_memberships' table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('tribe_memberships');
    }
};
