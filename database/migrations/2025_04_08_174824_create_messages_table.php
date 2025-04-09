<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Defines the structure of the 'messages' table (DM content).
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            // id (PK)
            $table->id();

            // conversation_id (FK -> conversations.id) - Which conversation this message belongs to.
            // onDelete('cascade') - If the conversation is deleted, delete its messages.
            $table->foreignId('conversation_id')->constrained('conversations')->onDelete('cascade');

            // sender_user_id (FK -> users.id, Nullable) - User who sent the message.
            // onDelete('set null') - Keep the message content even if sender is deleted (show as 'deleted user').
            $table->foreignId('sender_user_id')->nullable()->constrained('users')->onDelete('set null');

            // content (TEXT) - The actual text of the message.
            $table->text('content');

            // created_at, updated_at (TIMESTAMP)
            $table->timestamps();

            // deleted_at (TIMESTAMP, Nullable) - For soft deletes (allowing sender to 'unsend' or hide).
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the 'messages' table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
