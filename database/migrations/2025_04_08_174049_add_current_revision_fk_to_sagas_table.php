<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds the foreign key constraint for current_revision_id on the sagas table.
     */
    public function up(): void
    {
        Schema::table('sagas', function (Blueprint $table) {
            // Ensure the column exists before adding the constraint
            if (Schema::hasColumn('sagas', 'current_revision_id')) {
                 // Add the foreign key constraint.
                 // onDelete('set null') means if the referenced revision is deleted,
                 // the sagas.current_revision_id becomes NULL.
                 $table->foreign('current_revision_id')
                       ->references('id')
                       ->on('saga_revisions')
                       ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * Removes the foreign key constraint.
     */
    public function down(): void
    {
        Schema::table('sagas', function (Blueprint $table) {
            // Drop the foreign key using the conventional name Laravel creates: tablename_columnname_foreign
            $table->dropForeign(['current_revision_id']); 
        });
    }
};
