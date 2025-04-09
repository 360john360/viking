<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Defines the structure of the 'longhouses' table (forum categories).
     */
    public function up(): void
    {
        Schema::create('longhouses', function (Blueprint $table) {
            // id (PK)
            $table->id();

            // name (VARCHAR) - The name of the forum category.
            $table->string('name');

            // slug (VARCHAR, Unique, Indexed) - URL-friendly identifier.
            $table->string('slug')->unique();

            // description (TEXT, Nullable) - A brief description of the forum's purpose.
            $table->text('description')->nullable();

            // creator_user_id (FK -> users.id, Nullable) - Optional: User who proposed/created the category.
            // onDelete('set null') - Keep the longhouse even if the creator is deleted.
            $table->foreignId('creator_user_id')->nullable()->constrained('users')->onDelete('set null');

            // created_at, updated_at (TIMESTAMP)
            $table->timestamps();

            // deleted_at (TIMESTAMP, Nullable) - For soft deletes
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the 'longhouses' table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('longhouses');
    }
};
