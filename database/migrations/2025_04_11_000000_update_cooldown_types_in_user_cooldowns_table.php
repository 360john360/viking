<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Ensure doctrine/dbal is installed before running this:
        // composer require doctrine/dbal
        Schema::table('user_cooldowns', function (Blueprint $table) {
            $table->enum('cooldown_type', ['kingdom_join', 'tribe_join', 'posting'])->default('kingdom_join')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_cooldowns', function (Blueprint $table) {
            // Reverting to the previous state.
            // If 'posting' was essential and other values existed, this might need adjustment
            // For this case, assuming 'kingdom_join' was the only original definite value.
            $table->enum('cooldown_type', ['kingdom_join'])->default('kingdom_join')->change();
        });
    }
};
