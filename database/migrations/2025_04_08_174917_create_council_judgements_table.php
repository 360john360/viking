<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   /**
     * Run the migrations.
     *
     * Defines the structure of the 'council_judgements' table (Moderation Actions).
     */
    public function up(): void
    {
        Schema::create('council_judgements', function (Blueprint $table) {
            // id (PK)
            $table->id();

            // Polymorphic relationship: What is being moderated? (User, Post, Saga, etc.)
            // Creates actionable_id (UNSIGNED BIGINT) and actionable_type (VARCHAR)
            $table->morphs('actionable'); // Index automatically added

            // moderator_user_id (FK -> users.id, Nullable) - Who issued the judgement.
            // onDelete('set null') - Keep the judgement record even if the mod account is deleted.
            $table->foreignId('moderator_user_id')->nullable()->constrained('users')->onDelete('set null');

            // action_type (ENUM, Indexed) - The type of moderation action taken.
            $table->enum('action_type', ['warning', 'content_hide', 'content_delete', 'timeout', 'banishment', 'edit'])->index();

            // reason (TEXT) - Why the action was taken.
            $table->text('reason');

            // scope (ENUM, Indexed) - Level at which moderation occurred (Site, Kingdom, Tribe).
            $table->enum('scope', ['site', 'kingdom', 'tribe'])->index();

            // scope_id (UNSIGNED BIGINT, Nullable, Indexed) - Kingdom or Tribe ID if applicable. Null for site scope.
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->index(['scope', 'scope_id']); // Index for combined scope lookups

            // expires_at (TIMESTAMP, Nullable, Indexed) - For temporary actions like 'timeout'.
            // Null for permanent actions like 'banishment' (unless timed ban).
            $table->timestamp('expires_at')->nullable()->index();

            // created_at, updated_at (TIMESTAMP)
            // created_at = when the judgement was issued.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the 'council_judgements' table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('council_judgements');
    }
};
