<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds the foreign key constraint for king_user_id on the kingdoms table.
     */
    public function up(): void
    {
        Schema::table('kingdoms', function (Blueprint $table) {
             // Add the foreign key constraint if the column exists
             if (Schema::hasColumn('kingdoms', 'king_user_id')) {
                // onDelete('set null') - If the user account acting as king is deleted,
                // set the kingdom's king_user_id to NULL (kingdom needs a new king).
                $table->foreign('king_user_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * Removes the foreign key constraint from the king_user_id column.
     */
    public function down(): void
    {
        Schema::table('kingdoms', function (Blueprint $table) {
            // Drop the foreign key using the conventional name: tablename_columnname_foreign
            // Check if column exists before trying to drop constraint related to it
             if (Schema::hasColumn('kingdoms', 'king_user_id')) {
                $table->dropForeign(['king_user_id']);
            }
        });
    }
};
