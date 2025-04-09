<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tribes', function (Blueprint $table) {
            // Add boolean column, default to active, place after leader_user_id
            $table->boolean('is_active')->default(true)->after('leader_user_id');
            $table->index('is_active'); // Add index
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tribes', function (Blueprint $table) {
            // Drop index first if exists
            try { $table->dropIndex(['is_active']); } catch (\Exception $e) {}
            $table->dropColumn('is_active');
        });
    }
};
