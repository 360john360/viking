<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Defines the structure of the 'achievements' table (Medals/Deeds Definitions).
     */
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table) {
            // id (PK)
            $table->id();

            // name (VARCHAR, Unique) - Name of the achievement (e.g., "Shieldwall Veteran").
            $table->string('name')->unique();

            // description (TEXT) - Explanation of what the achievement represents.
            $table->text('description');

            // icon_url (VARCHAR, Nullable) - Path to a visual badge/icon.
            $table->string('icon_url')->nullable();

            // type (ENUM, Indexed) - Category for filtering/grouping.
            $table->enum('type', ['kvk', 'event', 'contribution', 'community', 'manual'])->index();

            // is_repeatable (BOOLEAN, Default: false) - Can this be earned multiple times?
            $table->boolean('is_repeatable')->default(false);

            // criteria_description (TEXT, Nullable) - User-facing text about how to earn it.
            $table->text('criteria_description')->nullable();

            // internal_trigger_key (VARCHAR, Nullable, Unique, Indexed) - Key used by automated systems
            // to award this (e.g., 'participated_in_5_kvk'). Null if manually awarded.
            $table->string('internal_trigger_key')->nullable()->unique()->index();

            // created_at, updated_at (TIMESTAMP)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the 'achievements' table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};
