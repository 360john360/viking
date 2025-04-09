<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Defines the structure of the 'approvals' table for polymorphic relations.
     */
    public function up(): void
    {
        Schema::create('approvals', function (Blueprint $table) {
            // id (PK)
            $table->id();

            // Polymorphic relationship columns & index:
            // Creates `approvable_id` (UNSIGNED BIGINT) and `approvable_type` (VARCHAR)
            // Represents the item being approved (e.g., Saga ID 5, Post ID 12)
            $table->morphs('approvable'); // Automatically adds index: approvals_approvable_type_approvable_id_index

            // status (ENUM, Default: 'pending', Indexed) - Approval status
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->index('status');

            // scope (ENUM, Indexed) - Level required for approval (Site, Kingdom, Tribe)
            $table->enum('scope', ['site', 'kingdom', 'tribe']);
            $table->index('scope');

            // scope_id (UNSIGNED BIGINT, Nullable, Indexed) - ID of kingdom/tribe if scope requires it.
            // Null if scope is 'site'. No direct FK as type depends on 'scope'.
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->index(['scope', 'scope_id']); // Index for combined scope lookups

            // submitter_user_id (FK -> users.id) - User who created the content/request.
            // onDelete('cascade') - If submitter deleted, their pending approvals are removed.
            $table->foreignId('submitter_user_id')->constrained('users')->onDelete('cascade');

            // approver_user_id (FK -> users.id, Nullable) - User who approved/rejected it.
            // onDelete('set null') - If approver deleted, keep the record but lose the direct link.
            $table->foreignId('approver_user_id')->nullable()->constrained('users')->onDelete('set null');

            // approved_at (TIMESTAMP, Nullable) - Timestamp of approval/rejection.
            $table->timestamp('approved_at')->nullable();

            // rejection_reason (TEXT, Nullable) - Reason if rejected.
            $table->text('rejection_reason')->nullable();

            // created_at, updated_at (TIMESTAMP)
            $table->timestamps();

            // Constraint: An item should only have one approval record.
            // We already indexed the morphs columns, this adds the unique constraint.
            // $table->unique(['approvable_id', 'approvable_type']); // Optional: enforce strict 1-to-1 at DB level if needed. Often handled in application logic.
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the 'approvals' table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
