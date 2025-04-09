<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Defines the structure of the 'longhouse_posts' table (forum replies).
     */
    public function up(): void
    {
        Schema::create('longhouse_posts', function (Blueprint $table) {
            // id (PK)
            $table->id();

            // longhouse_thread_id (FK -> longhouse_threads.id) - Which thread this post belongs to.
            // onDelete('cascade') - If the thread is deleted, delete its posts.
            $table->foreignId('longhouse_thread_id')->constrained('longhouse_threads')->onDelete('cascade');

            // user_id (FK -> users.id, Nullable) - User who wrote the post.
            // onDelete('set null') - Keep the post even if the author is deleted (show as 'deleted user').
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');

            // parent_post_id (UNSIGNED BIGINT, Nullable, Indexed) - For threaded replies.
            // Points to the 'id' of the post this one is replying to. Null if it's a top-level post in the thread.
            // We define the column and index here. Adding a strict foreign key constraint back to 'longhouse_posts.id'
            // can be complex and is often handled at the application level or added in a later migration if needed.
            $table->unsignedBigInteger('parent_post_id')->nullable();
            $table->index('parent_post_id');

            // content (TEXT) - The actual text content of the post.
            $table->text('content');

            // created_at, updated_at (TIMESTAMP)
            $table->timestamps();

            // deleted_at (TIMESTAMP, Nullable) - For soft deletes (moderator actions)
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the 'longhouse_posts' table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('longhouse_posts');
    }
};
