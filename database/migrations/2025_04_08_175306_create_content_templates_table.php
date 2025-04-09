<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Defines the structure of the 'content_templates' table.
     */
    public function up(): void
    {
        Schema::create('content_templates', function (Blueprint $table) {
            // id (PK)
            $table->id();

            // name (VARCHAR, Unique) - Identifier for the template (e.g., "KvK Declaration Runestone").
            $table->string('name')->unique();

            // type (ENUM, Indexed) - What kind of content this template is for.
            $table->enum('type', ['runestone', 'saga', 'feast_description', 'longhouse_post'])->index();

            // content (LONGTEXT) - The template body (potentially using placeholders like {{variable}}).
            $table->longText('content');

            // scope (ENUM, Default: 'site') - Who can use this template (Site Admins or Kingdom Leadership).
            $table->enum('scope', ['site', 'kingdom'])->default('site');

            // created_by_user_id (FK -> users.id, Nullable) - User who created the template.
            // onDelete('set null') - Keep template if creator deleted.
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->onDelete('set null');

            // created_at, updated_at (TIMESTAMP)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the 'content_templates' table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_templates');
    }
};
