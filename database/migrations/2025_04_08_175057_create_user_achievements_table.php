<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Defines the structure of the 'user_achievements' table (Earned Achievements Log).
     */
    public function up(): void
    {
        Schema::create('user_achievements', function (Blueprint $table) {
            // id (PK)
            $table->id();

            // user_id (FK -> users.id) - The user who earned the achievement.
            // onDelete('cascade') - If user deleted, remove their achievement records.
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // achievement_id (FK -> achievements.id) - The achievement that was earned.
            // onDelete('cascade') - If an achievement definition is deleted, remove records of it being earned.
            $table->foreignId('achievement_id')->constrained('achievements')->onDelete('cascade');

            // earned_at (TIMESTAMP) - When the achievement was earned/awarded. Defaults to now.
            $table->timestamp('earned_at')->useCurrent();

            // awarded_by_user_id (FK -> users.id, Nullable) - For manually awarded achievements. Null if earned automatically.
            // onDelete('set null') - Keep the record if the awarding admin/mod is deleted.
            $table->foreignId('awarded_by_user_id')->nullable()->constrained('users')->onDelete('set null');

            // related_context (JSON, Nullable) - Optional context, e.g., {"event_id": 123, "kvk_season": 5}.
            $table->json('related_context')->nullable();

            // created_at, updated_at (TIMESTAMP) - Standard record timestamps.
            $table->timestamps();

            // Index for common lookups (e.g., finding all achievements for a user).
            $table->index(['user_id', 'achievement_id']);

            // Note: A unique constraint on ['user_id', 'achievement_id'] could be added
            // if most achievements are not repeatable. For flexibility (allowing repeatable achievements),
            // we omit it here and rely on application logic or specific constraints added later if needed.
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the 'user_achievements' table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_achievements');
    }
};
