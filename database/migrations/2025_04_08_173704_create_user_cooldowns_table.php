<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Defines the structure of the 'user_cooldowns' table.
     */
    public function up(): void
    {
        Schema::create('user_cooldowns', function (Blueprint $table) {
            // id (PK)
            $table->id();

            // user_id (FK -> users.id) - The user subject to the cooldown.
            // onDelete('cascade') - If user deleted, their cooldown records are removed.
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // cooldown_type (ENUM, Indexed) - Type of action being cooled down.
            // Start with 'kingdom_join', can add others like 'tribe_join', 'posting' later.
            $table->enum('cooldown_type', ['kingdom_join']);
            $table->index('cooldown_type');

            // expires_at (TIMESTAMP, Indexed) - When the cooldown period ends. Crucial for checks.
            $table->timestamp('expires_at');
            $table->index('expires_at');

            // reason_kingdom_id (FK -> kingdoms.id, Nullable) - Optional link to the kingdom
            // that triggered the cooldown (e.g., the one the user left).
            // onDelete('set null') - If the related kingdom is deleted, just remove the link.
            $table->foreignId('reason_kingdom_id')->nullable()->constrained('kingdoms')->onDelete('set null');

            // created_at, updated_at (TIMESTAMP)
            // created_at = when cooldown started
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the 'user_cooldowns' table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_cooldowns');
    }
};
