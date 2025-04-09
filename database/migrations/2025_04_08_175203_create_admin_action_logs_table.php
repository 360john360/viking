<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Defines the structure of the 'admin_action_logs' table (Audit Trail).
     */
    public function up(): void
    {
        Schema::create('admin_action_logs', function (Blueprint $table) {
            // id (PK)
            $table->id();

            // admin_user_id (FK -> users.id, Nullable) - The admin/mod performing the action.
            // onDelete('set null') - Keep the log even if the admin account is deleted.
            $table->foreignId('admin_user_id')->nullable()->constrained('users')->onDelete('set null');

            // action (VARCHAR, Indexed) - Description of the action (e.g., 'user.ban', 'kingdom.verify_king').
            $table->string('action')->index();

            // target_resource_type (VARCHAR, Nullable, Indexed) - Eloquent model acted upon (e.g., App\Models\User).
            $table->string('target_resource_type')->nullable();

            // target_resource_id (UNSIGNED BIGINT, Nullable) - ID of the model acted upon.
            // Not using foreignId because the type depends on target_resource_type.
            $table->unsignedBigInteger('target_resource_id')->nullable();

            // Index for target lookup
            $table->index(['target_resource_type', 'target_resource_id']);

            // details (JSON, Nullable) - Additional context (e.g., old/new values, reason provided).
            $table->json('details')->nullable();

            // ip_address (VARCHAR, Nullable) - IP address of the admin performing the action.
            $table->ipAddress('ip_address')->nullable();

            // created_at (TIMESTAMP) - Only need created_at for logs.
            $table->timestamp('created_at')->nullable();
            // No $table->timestamps() or $table->updated_at needed typically for logs.
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the 'admin_action_logs' table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_action_logs');
    }
};
