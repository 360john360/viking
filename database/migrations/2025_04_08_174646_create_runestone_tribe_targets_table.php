<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Defines the structure of the 'runestone_tribe_targets' pivot table.
     */
    public function up(): void
    {
        Schema::create('runestone_tribe_targets', function (Blueprint $table) {
            // runestone_id (FK -> runestones.id) - The targeted announcement.
            // onDelete('cascade') - If the announcement is deleted, remove target entries.
            $table->foreignId('runestone_id')->constrained('runestones')->onDelete('cascade');

            // tribe_id (FK -> tribes.id) - The tribe being targeted.
            // onDelete('cascade') - If the tribe is deleted, remove target entries.
            $table->foreignId('tribe_id')->constrained('tribes')->onDelete('cascade');

            // Define a composite primary key:
            // Ensures a specific tribe can only be targeted once per announcement.
            $table->primary(['runestone_id', 'tribe_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the 'runestone_tribe_targets' table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('runestone_tribe_targets');
    }
};
