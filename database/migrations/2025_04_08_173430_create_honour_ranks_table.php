<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Defines the structure of the 'honour_ranks' table.
     */
    public function up(): void
    {
        Schema::create('honour_ranks', function (Blueprint $table) {
            // id (PK)
            $table->id();

            // name (VARCHAR, Unique) - Thematic rank name (e.g., 'Karl', 'Huskarl')
            $table->string('name')->unique();

            // level (UNSIGNED TINYINT, Unique) - Numeric level for ordering ranks (0, 1, 2...)
            $table->unsignedTinyInteger('level')->unique();

            // Criteria columns (examples, adjust as needed for automation logic)
            // min_days_in_kingdom (UNSIGNED INT, Nullable) - Days required in current kingdom
            $table->unsignedInteger('min_days_in_kingdom')->nullable();
            // min_saga_contributions (UNSIGNED INT, Nullable) - Approved Saga edits/creations
            $table->unsignedInteger('min_saga_contributions')->nullable();
            // min_feasts_attended (UNSIGNED INT, Nullable) - Events attended
            $table->unsignedInteger('min_feasts_attended')->nullable();

            // description (TEXT, Nullable) - What this rank signifies
            $table->text('description')->nullable();

            // permissions (JSON, Nullable) - Defines specific capabilities unlocked
            // Example: {"can_create_events": true, "can_view_kvk_plans": true}
            $table->json('permissions')->nullable();

            // created_at, updated_at (TIMESTAMP)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the 'honour_ranks' table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('honour_ranks');
    }
};
