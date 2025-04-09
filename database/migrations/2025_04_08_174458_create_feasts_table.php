<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Defines the structure of the 'feasts' table (Events).
     */
    public function up(): void
    {
        Schema::create('feasts', function (Blueprint $table) {
            // id (PK)
            $table->id();

            // name (VARCHAR) - The name of the event.
            $table->string('name');

            // description (TEXT, Nullable) - Details about the event.
            $table->text('description')->nullable();

            // event_type (ENUM, Indexed) - Thematic type (Feast, Raid, Council, etc.).
            $table->enum('event_type', ['feast', 'raid', 'council', 'kvk_strategy', 'training', 'other'])->index();

            // start_time (TIMESTAMP, Indexed) - When the event starts.
            $table->timestamp('start_time')->index();

            // end_time (TIMESTAMP, Nullable) - When the event ends (optional).
            $table->timestamp('end_time')->nullable();

            // location (VARCHAR, Nullable) - In-game coordinates or description.
            $table->string('location')->nullable();

            // creator_user_id (FK -> users.id, Nullable) - User who created the event.
            // onDelete('set null') - Keep event if creator deleted.
            $table->foreignId('creator_user_id')->nullable()->constrained('users')->onDelete('set null');

            // is_recurring (BOOLEAN, Default: false) - Does the event repeat?
            $table->boolean('is_recurring')->default(false);

            // recurrence_rule (VARCHAR, Nullable) - Stores recurrence rules (e.g., RRULE string).
            $table->string('recurrence_rule')->nullable();

            // associated_longhouse_thread_id (FK -> longhouse_threads.id, Nullable)
            // Optional link to a specific discussion thread for this event.
            // onDelete('set null') - Keep event if discussion thread deleted.
            $table->foreignId('associated_longhouse_thread_id')->nullable()->constrained('longhouse_threads')->onDelete('set null');

            // created_at, updated_at (TIMESTAMP)
            $table->timestamps();

            // deleted_at (TIMESTAMP, Nullable) - For soft deletes
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the 'feasts' table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('feasts');
    }
};
