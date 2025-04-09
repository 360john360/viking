<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Defines the structure of the 'tribes' table.
     */
    public function up(): void
    {
        Schema::create('tribes', function (Blueprint $table) {
            // id (PK)
            $table->id();

            // kingdom_id (FK -> kingdoms.id) - Which kingdom this tribe belongs to
            // onDelete('cascade') means if a kingdom is deleted, its tribes are also deleted.
            $table->foreignId('kingdom_id')->constrained('kingdoms')->onDelete('cascade');

            // name (VARCHAR, Indexed) - Tribe name (unique within a kingdom)
            $table->string('name');

            // slug (VARCHAR, Indexed) - URL-friendly identifier (unique within a kingdom)
            $table->string('slug');

            // description (TEXT, Nullable) - Tribe's purpose or lore
            $table->text('description')->nullable();

            // emblem_url (VARCHAR, Nullable) - Path to tribe banner/icon
            $table->string('emblem_url')->nullable();

            // leader_user_id (FK -> users.id, Nullable) - The primary leader (Thane)
            // onDelete('set null') means if the leader user is deleted, this field becomes NULL.
            $table->foreignId('leader_user_id')->nullable()->constrained('users')->onDelete('set null');
            
            // created_at, updated_at (TIMESTAMP)
            $table->timestamps();

            // deleted_at (TIMESTAMP, Nullable) - For soft deletes
            $table->softDeletes();

            // Constraints:
            // A tribe's name must be unique within its specific kingdom.
            $table->unique(['kingdom_id', 'name']);
            // A tribe's slug must be unique within its specific kingdom.
            $table->unique(['kingdom_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the 'tribes' table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('tribes');
    }
};
