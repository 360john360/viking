<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// The class name will match your filename (e.g., AddSoftDeletesToUsersTable)
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds the deleted_at column required for soft deletes.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add the 'deleted_at' column, placing it after 'updated_at' for convention
            $table->softDeletes()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Removes the 'deleted_at' column.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Helper method to drop the 'deleted_at' column
            $table->dropSoftDeletes();
        });
    }
};