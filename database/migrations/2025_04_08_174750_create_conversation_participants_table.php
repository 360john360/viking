<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Defines the structure of the 'conversation_participants' pivot table.
     */
    public function up(): void
    {
        Schema::create('conversation_participants', function (Blueprint $table) {
            // id (PK) - Useful even on a pivot if you need to reference a specific participation record.
            $table->id();

            // conversation_id (FK -> conversations.id) - The conversation being participated in.
            // onDelete('cascade') - If the conversation is deleted, remove participant entries.
            $table->foreignId('conversation_id')->constrained('conversations')->onDelete('cascade');

            // user_id (FK -> users.id) - The user participating.
            // onDelete('cascade') - If the user is deleted, remove their participation entries.
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // joined_at (TIMESTAMP, Nullable) - When the user was added to the conversation.
            $table->timestamp('joined_at')->nullable()->useCurrent(); // Default to when record is created

            // last_read_at (TIMESTAMP, Nullable, Indexed) - Track when the user last viewed messages here.
            $table->timestamp('last_read_at')->nullable()->index();

            // is_hidden (BOOLEAN, Default: false) - Allows user to 'archive' or hide from main list.
            $table->boolean('is_hidden')->default(false);

            // created_at, updated_at (TIMESTAMP) - Standard timestamps for the participation record.
            $table->timestamps();

            // Constraint: A user can only be listed once per conversation.
            $table->unique(['conversation_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the 'conversation_participants' table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_participants');
    }
};
