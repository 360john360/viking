<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds the self-referencing foreign key constraint for parent_post_id.
     */
    public function up(): void
    {
        Schema::table('longhouse_posts', function (Blueprint $table) {
            // Add the foreign key constraint if the column exists
            if (Schema::hasColumn('longhouse_posts', 'parent_post_id')) {
                // onDelete('set null') - If the parent post is deleted,
                // set this post's parent_post_id to NULL (it becomes a top-level reply orphan).
                $table->foreign('parent_post_id')
                      ->references('id')
                      ->on('longhouse_posts') // References the same table
                      ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * Removes the foreign key constraint from the parent_post_id column.
     */
    public function down(): void
    {
        Schema::table('longhouse_posts', function (Blueprint $table) {
             // Check if column exists before trying to drop constraint related to it
             if (Schema::hasColumn('longhouse_posts', 'parent_post_id')) {
                // Drop the foreign key using the conventional name
                $table->dropForeign(['parent_post_id']);
             }
        });
    }
};
