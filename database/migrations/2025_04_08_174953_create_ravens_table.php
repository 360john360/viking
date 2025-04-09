<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Defines the structure of the 'ravens' table (Notifications).
     */
    public function up(): void
    {
        Schema::create('ravens', function (Blueprint $table) {
            // id (UUID PK) - Using UUID for potentially high volume.
            $table->uuid('id')->primary();

            // recipient_user_id (FK -> users.id) - Who receives the raven.
            // onDelete('cascade') - If user deleted, remove their notifications.
            $table->foreignId('recipient_user_id')->constrained('users')->onDelete('cascade');

            // message (TEXT) - The notification text.
            $table->text('message');

            // link_url (VARCHAR, Nullable) - URL to the relevant item (e.g., post, profile).
            $table->string('link_url')->nullable();

            // Polymorphic relationship: What triggered the notification? (Post, User, Approval, etc.)
            // Creates nullable `notifiable_id` (UUID) and `notifiable_type` (VARCHAR) columns.
            // Nullable because some notifications might not link to a specific model.
            // Using UuidMorphs because the related item might also use UUIDs, or just to be consistent.
            // Note: If the related items ALWAYS use standard BigInt IDs, you could use `nullableMorphs` instead.
            // Let's assume flexibility and use UuidMorphs. Check related models later.
             $table->nullableUuidMorphs('notifiable'); // Index automatically added

            // type (VARCHAR, Nullable, Indexed) - Specific notification category key
            // (e.g., 'new_post_mention', 'kingdom_join_approved', 'approval_required').
            $table->string('type')->nullable()->index();

            // read_at (TIMESTAMP, Nullable, Indexed) - When the user marked it as read.
            $table->timestamp('read_at')->nullable()->index();

            // created_at, updated_at (TIMESTAMP)
            $table->timestamps();

            // Index recipient_id and read_at together for efficient fetching of unread notifications.
            $table->index(['recipient_user_id', 'read_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the 'ravens' table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('ravens');
    }
};
