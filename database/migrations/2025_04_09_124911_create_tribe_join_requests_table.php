<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     /**
       * Run the migrations.
       */
      public function up(): void
      {
          Schema::create('tribe_join_requests', function (Blueprint $table) {
              $table->id();
              // User making the request
              $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
              // Tribe being requested to join
              $table->foreignId('tribe_id')->constrained('tribes')->onDelete('cascade');
              // Status of the request
              $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
              $table->index('status');
              // Optional message from applicant
              $table->text('message')->nullable();
              // User (Leader/Officer) who reviewed the request
              $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->onDelete('set null');
              // Timestamp of review
              $table->timestamp('reviewed_at')->nullable();
              // Standard timestamps (created_at = request time)
              $table->timestamps();
          });
      }

      /**
       * Reverse the migrations.
       */
      public function down(): void
      {
          Schema::dropIfExists('tribe_join_requests');
      }
};
