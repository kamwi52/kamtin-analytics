<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // In database/migrations/..._create_results_table.php
public function up(): void
{
    Schema::create('results', function (Blueprint $table) {
        $table->id();
        $table->foreignId('pupil_db_id')->constrained('pupils', 'pupil_db_id')->onDelete('cascade');
        $table->string('subject');
        $table->integer('score')->nullable(); // Nullable to handle absences
        $table->string('assessment');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
