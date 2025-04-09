<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Defines the structure of the 'feast_attendances' table (Event RSVP/Attendance).
     */
    public function up(): void
    {
        Schema::create('feast_attendances', function (Blueprint $table) {
            // id (PK)
            $table->id();

            // feast_id (FK -> feasts.id) - The event being responded to.
            // onDelete('cascade') - If the event is deleted, remove attendance records.
            $table->foreignId('feast_id')->constrained('feasts')->onDelete('cascade');

            // user_id (FK -> users.id) - The user responding.
            // onDelete('cascade') - If the user is deleted, remove their attendance records.
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // status (ENUM, Indexed) - The user's RSVP status.
            $table->enum('status', ['attending', 'maybe', 'declined']);
            $table->index('status');

            // created_at, updated_at (TIMESTAMP)
            $table->timestamps();

            // Constraint: A user can only have one attendance record per event.
            $table->unique(['feast_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the 'feast_attendances' table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('feast_attendances');
    }
};
