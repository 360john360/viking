<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Defines the structure of the 'sagas' table (wiki entries).
     */
    public function up(): void
    {
        Schema::create('sagas', function (Blueprint $table) {
            // id (PK)
            $table->id();

            // title (VARCHAR) - The title of the saga/wiki page.
            $table->string('title');

            // slug (VARCHAR, Unique, Indexed) - URL-friendly identifier.
            $table->string('slug')->unique();

            // creator_user_id (FK -> users.id, Nullable) - User who initially created the saga.
            // onDelete('set null') - Keep the saga entry even if the original creator is deleted.
            $table->foreignId('creator_user_id')->nullable()->constrained('users')->onDelete('set null');

            // current_revision_id (UNSIGNED BIGINT, Nullable) - Points to the active content version.
            // We define the column here. The Foreign Key constraint referencing 'saga_revisions.id'
            // will be added in a separate migration *after* the saga_revisions table is created.
            $table->unsignedBigInteger('current_revision_id')->nullable();
            $table->index('current_revision_id'); // Index for faster lookup of current revision

            // is_locked (BOOLEAN, Default: false) - Prevents edits except by mods/admins.
            $table->boolean('is_locked')->default(false);

            // created_at, updated_at (TIMESTAMP)
            $table->timestamps();

            // deleted_at (TIMESTAMP, Nullable) - For soft deletes
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the 'sagas' table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('sagas');
    }
};
