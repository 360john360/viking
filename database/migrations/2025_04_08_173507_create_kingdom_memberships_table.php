<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Defines the structure of the 'kingdom_memberships' table, linking users to kingdoms.
     */
    public function up(): void
    {
        Schema::create('kingdom_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('kingdom_id')->constrained('kingdoms')->onDelete('cascade');
            $table->enum('role', ['member', 'moderator', 'king'])->default('member');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the 'kingdom_memberships' table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('kingdom_memberships');
    }
};
