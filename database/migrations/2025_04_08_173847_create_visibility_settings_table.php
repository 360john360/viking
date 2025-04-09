<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Defines the structure of the 'visibility_settings' table for polymorphic relations.
     */
    public function up(): void
    {
        Schema::create('visibility_settings', function (Blueprint $table) {
            // id (PK)
            $table->id();

            // Polymorphic relationship columns & index:
            // Creates `visible_id` (UNSIGNED BIGINT) and `visible_type` (VARCHAR)
            // Represents the item whose visibility is being defined (e.g., Saga ID 5, Post ID 12)
            $table->morphs('visible'); // Automatically adds index: visibility_settings_visible_type_visible_id_index

            // level (ENUM, Indexed) - Defines who can see the item.
            $table->enum('level', ['private', 'tribe', 'kingdom', 'public']);
            $table->index('level');

            // owning_tribe_id (FK -> tribes.id, Nullable)
            // Required if level is 'tribe'. Null otherwise.
            // onDelete('cascade') - If the owning tribe is deleted, cascade the deletion to this setting.
            $table->foreignId('owning_tribe_id')->nullable()->constrained('tribes')->onDelete('cascade');

            // owning_kingdom_id (FK -> kingdoms.id, Nullable)
            // Required if level is 'tribe' or 'kingdom'. Null otherwise.
            // onDelete('cascade') - If the owning kingdom is deleted, cascade the deletion.
            $table->foreignId('owning_kingdom_id')->nullable()->constrained('kingdoms')->onDelete('cascade');

            // created_at, updated_at (TIMESTAMP)
            $table->timestamps();

            // Constraint: An item should only have one visibility setting record.
            $table->unique(['visible_id', 'visible_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the 'visibility_settings' table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('visibility_settings');
    }
};
