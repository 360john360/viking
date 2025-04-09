<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Defines the structure of the 'longhouse_threads' table (forum threads).
     */
    public function up(): void
    {
        Schema::create('longhouse_threads', function (Blueprint $table) {
            // id (PK)
            $table->id();

            // longhouse_id (FK -> longhouses.id) - Which category this thread belongs to.
            // onDelete('cascade') - If the category is deleted, delete its threads.
            $table->foreignId('longhouse_id')->constrained('longhouses')->onDelete('cascade');

            // title (VARCHAR) - The title of the discussion thread.
            $table->string('title');

            // creator_user_id (FK -> users.id, Nullable) - User who started the thread.
            // onDelete('set null') - Keep the thread even if the creator is deleted.
            $table->foreignId('creator_user_id')->nullable()->constrained('users')->onDelete('set null');

            // is_pinned (BOOLEAN, Default: false) - For sticky threads.
            $table->boolean('is_pinned')->default(false);

            // is_locked (BOOLEAN, Default: false) - Prevents further replies.
            $table->boolean('is_locked')->default(false);

            // last_reply_at (TIMESTAMP, Nullable, Indexed) - Timestamp of the last reply for sorting.
            $table->timestamp('last_reply_at')->nullable()->index();

            // created_at, updated_at (TIMESTAMP)
            $table->timestamps();

            // deleted_at (TIMESTAMP, Nullable) - For soft deletes
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the 'longhouse_threads' table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('longhouse_threads');
    }
};
