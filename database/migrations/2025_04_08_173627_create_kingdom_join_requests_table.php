<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Defines the structure of the 'kingdom_join_requests' table.
     */
    public function up(): void
    {
        Schema::create('kingdom_join_requests', function (Blueprint $table) {
            // id (PK)
            $table->id();

            // user_id (FK -> users.id) - The user applying to join.
            // onDelete('cascade') - If user deleted, their pending requests are removed.
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // kingdom_id (FK -> kingdoms.id) - The kingdom they want to join.
            // onDelete('cascade') - If kingdom deleted, pending requests are removed.
            $table->foreignId('kingdom_id')->constrained('kingdoms')->onDelete('cascade');

            // status (ENUM, Default: 'pending', Indexed) - Status of the request.
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->index('status'); // Index for faster status lookups

            // message (TEXT, Nullable) - Optional message from the applicant.
            $table->text('message')->nullable();

            // reviewed_by_user_id (FK -> users.id, Nullable) - The King who reviewed it.
            // onDelete('set null') - If reviewer account deleted, we keep the record but lose reviewer link.
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->onDelete('set null');

            // reviewed_at (TIMESTAMP, Nullable) - When the request was approved/rejected.
            $table->timestamp('reviewed_at')->nullable();

            // created_at, updated_at (TIMESTAMP)
            // created_at = when request submitted
            // updated_at = when status changes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the 'kingdom_join_requests' table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('kingdom_join_requests');
    }
};
