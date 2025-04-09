<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Defines the structure of the 'kingdoms' table when the migration is applied.
     */
    public function up(): void
    {
        // Creates the 'kingdoms' table using Laravel's Schema builder
        Schema::create('kingdoms', function (Blueprint $table) {
            // Define the columns based on our schema design:

            // id (PK, Unsigned BigInt, AI) - Standard Laravel primary key
            $table->id(); 

            // name (VARCHAR, Unique, Indexed) - Kingdom name
            $table->string('name')->unique(); 

            // slug (VARCHAR, Unique, Indexed) - URL-friendly identifier
            $table->string('slug')->unique(); 

            // description (TEXT, Nullable) - Kingdom's saga or description
            $table->text('description')->nullable(); 

            // emblem_url (VARCHAR, Nullable) - Path to kingdom banner/icon
            $table->string('emblem_url')->nullable(); 

            // king_user_id (Unsigned BigInt, Nullable, FK -> users.id, Unique, Indexed) 
            // The user acting as King. We define the column structure here.
            // The foreign key constraint itself is often added later once the 'users' table definitely exists.
            $table->unsignedBigInteger('king_user_id')->nullable()->unique(); 

            // is_active (BOOLEAN, Default: true) - Allows admins to disable a kingdom
            $table->boolean('is_active')->default(true); 

            // created_at, updated_at (TIMESTAMP) - Standard Laravel timestamps
            $table->timestamps(); 

            // deleted_at (TIMESTAMP, Nullable) - For soft deletes
            $table->softDeletes(); 
        });
    }

    /**
     * Reverse the migrations.
     * * Defines what happens when this migration is rolled back (undo).
     */
    public function down(): void
    {
        // Drops the 'kingdoms' table if it exists
        Schema::dropIfExists('kingdoms');
    }
};

