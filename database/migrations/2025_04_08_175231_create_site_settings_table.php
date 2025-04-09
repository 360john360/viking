<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Defines the structure of the 'site_settings' table (Global Configuration).
     */
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            // id (PK)
            $table->id();

            // key (VARCHAR, Unique, Indexed) - The unique identifier for the setting (e.g., 'maintenance_mode').
            $table->string('key')->unique();

            // value (TEXT) - The value of the setting. Stored as text to accommodate various types (string, number, boolean as '0'/'1', JSON).
            $table->text('value');

            // description (VARCHAR, Nullable) - Explanation of the setting's purpose for admins.
            $table->string('description')->nullable();

            // last_updated_by (FK -> users.id, Nullable) - Tracks which admin last changed this setting.
            // onDelete('set null') - Keep setting if admin deleted.
            $table->foreignId('last_updated_by')->nullable()->constrained('users')->onDelete('set null');

            // created_at, updated_at (TIMESTAMP)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the 'site_settings' table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
