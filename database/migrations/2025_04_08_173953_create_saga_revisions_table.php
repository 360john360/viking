<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Defines the structure of the 'saga_revisions' table (wiki content history).
     */
    public function up(): void
    {
        Schema::create('saga_revisions', function (Blueprint $table) {
            // id (PK)
            $table->id();

            // saga_id (FK -> sagas.id) - Which saga this revision belongs to.
            // onDelete('cascade') - If the parent saga is deleted, all its revisions are deleted.
            $table->foreignId('saga_id')->constrained('sagas')->onDelete('cascade');

            // editor_user_id (FK -> users.id, Nullable) - User who saved this revision.
            // onDelete('set null') - Keep the revision history even if the editor's account is deleted.
            $table->foreignId('editor_user_id')->nullable()->constrained('users')->onDelete('set null');

            // content (LONGTEXT) - The actual wiki content for this revision.
            $table->longText('content');

            // edit_summary (VARCHAR, Nullable) - Optional comment about the changes made.
            $table->string('edit_summary')->nullable();

            // revision_number (UNSIGNED INT) - Sequential number for this revision within its saga.
            $table->unsignedInteger('revision_number');
            $table->index(['saga_id', 'revision_number']); // Index for looking up revisions by number

            // created_at, updated_at (TIMESTAMP)
            // 'created_at' primarily indicates when this revision was saved.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the 'saga_revisions' table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('saga_revisions');
    }
};
