<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Defines the structure of the 'runestones' table (Announcements).
     */
    public function up(): void
    {
        Schema::create('runestones', function (Blueprint $table) {
            // id (PK)
            $table->id();

            // title (VARCHAR) - The title of the announcement.
            $table->string('title');

            // content (TEXT) - The main body of the announcement.
            $table->text('content');

            // creator_user_id (FK -> users.id, Nullable) - User who created the announcement.
            // onDelete('set null') - Keep announcement if creator deleted.
            $table->foreignId('creator_user_id')->nullable()->constrained('users')->onDelete('set null');

            // is_kvk_channel (BOOLEAN, Default: false, Indexed) - Special flag for KvK announcements.
            // Used with runestone_tribe_targets table for specific tribe visibility during KvK.
            $table->boolean('is_kvk_channel')->default(false)->index();

            // expires_at (TIMESTAMP, Nullable) - Optional expiry for time-sensitive news.
            $table->timestamp('expires_at')->nullable();

            // created_at, updated_at (TIMESTAMP)
            $table->timestamps();

            // deleted_at (TIMESTAMP, Nullable) - For soft deletes
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the 'runestones' table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('runestones');
    }
};
