<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds missing columns and constraints to the tribe_memberships table.
     */
    public function up(): void
    {
        Schema::table('tribe_memberships', function (Blueprint $table) {
            // Add columns only if they don't exist

            if (!Schema::hasColumn('tribe_memberships', 'user_id')) {
                // Add after 'id' for logical placement
                $table->foreignId('user_id')->after('id')->constrained('users')->onDelete('cascade');
                // Add unique constraint separately for clarity/potential modification needs
                $table->unique('user_id');
            }

            if (!Schema::hasColumn('tribe_memberships', 'tribe_id')) {
                $table->foreignId('tribe_id')->after('user_id')->constrained('tribes')->onDelete('cascade');
            }

            if (!Schema::hasColumn('tribe_memberships', 'role')) {
                $table->enum('role', ['member', 'elder', 'thane'])->default('member')->after('tribe_id');
                $table->index('role');
            }

            if (!Schema::hasColumn('tribe_memberships', 'joined_at')) {
                $table->timestamp('joined_at')->after('role');
            }
        });
    }

    /**
     * Reverse the migrations.
     * Drops the columns and constraints added in the up() method.
     */
    public function down(): void
    {
        Schema::table('tribe_memberships', function (Blueprint $table) {
            // Drop constraints first, then columns, checking existence

            if (Schema::hasColumn('tribe_memberships', 'user_id')) {
                 // Default constraint name: tablename_column_unique
                try { $table->dropUnique(['user_id']); } catch (\Exception $e) {}
                 // Default foreign key name: tablename_column_foreign
                try { $table->dropForeign(['user_id']); } catch (\Exception $e) {}
            }
             if (Schema::hasColumn('tribe_memberships', 'tribe_id')) {
                try { $table->dropForeign(['tribe_id']); } catch (\Exception $e) {}
             }
             if (Schema::hasColumn('tribe_memberships', 'role')) {
                 try { $table->dropIndex(['role']); } catch (\Exception $e) {} // Drop index if it exists
             }

            // Now drop columns if they exist
            $columnsToDrop = ['user_id', 'tribe_id', 'role', 'joined_at'];
            foreach($columnsToDrop as $column) {
                if (Schema::hasColumn('tribe_memberships', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
