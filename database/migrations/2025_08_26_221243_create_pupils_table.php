<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // In database/migrations/..._create_pupils_table.php
public function up(): void
{
    Schema::create('pupils', function (Blueprint $table) {
        $table->id('pupil_db_id'); // Custom primary key name
        $table->unsignedBigInteger('pupil_id')->unique();
        $table->string('pupil_name');
        $table->string('pupil_class');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pupils');
    }
};
