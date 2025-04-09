<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Defines the structure of the 'kingdom_relations' table (Diplomacy).
     */
    public function up(): void
    {
        Schema::create('kingdom_relations', function (Blueprint $table) {
            // id (PK)
            $table->id();

            // kingdom_a_id (FK -> kingdoms.id) - First kingdom in the relation.
            // Important: Application logic should ensure kingdom_a_id < kingdom_b_id to enforce unique pair.
            $table->foreignId('kingdom_a_id')->constrained('kingdoms')->onDelete('cascade');

            // kingdom_b_id (FK -> kingdoms.id) - Second kingdom in the relation.
            $table->foreignId('kingdom_b_id')->constrained('kingdoms')->onDelete('cascade');

            // status (ENUM, Default: 'neutral', Indexed) - Current diplomatic status.
            $table->enum('status', [
                'proposed_alliance',
                'alliance',
                'proposed_rivalry',
                'rivalry',
                'neutral',
                'war'
            ])->default('neutral')->index();

            // initiated_by_kingdom_id (FK -> kingdoms.id) - Which kingdom proposed the relation change.
            $table->foreignId('initiated_by_kingdom_id')->constrained('kingdoms')->onDelete('cascade');

            // approved_by_a_king_id (FK -> users.id, Nullable) - King of Kingdom A who approved.
            $table->foreignId('approved_by_a_king_id')->nullable()->constrained('users')->onDelete('set null');

            // approved_by_b_king_id (FK -> users.id, Nullable) - King of Kingdom B who approved.
            $table->foreignId('approved_by_b_king_id')->nullable()->constrained('users')->onDelete('set null');

            // established_at (TIMESTAMP, Nullable) - When the status became active (e.g., alliance formed).
            $table->timestamp('established_at')->nullable();

            // created_at, updated_at (TIMESTAMP)
            $table->timestamps();

            // Constraint: Ensure only one relation entry exists per unique pair of kingdoms.
            // Assumes application logic stores the lower kingdom ID in kingdom_a_id.
            $table->unique(['kingdom_a_id', 'kingdom_b_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the 'kingdom_relations' table if it exists.
     */
    public function down(): void
    {
        Schema::dropIfExists('kingdom_relations');
    }
};
