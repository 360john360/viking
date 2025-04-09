<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Defines the structure of the 'conversations' table (DM Threads).
     */
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            // id (PK)
            $table->id();

            // subject (VARCHAR, Nullable) - Optional subject line for the conversation.
            $table->string('subject')->nullable();

            // created_at, updated_at (TIMESTAMP)
            // updated_at will typically be updated when a new message is added.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the 'conversations' table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
