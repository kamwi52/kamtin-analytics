<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pupils', function (Blueprint $table) {
            // Add the new gender column after 'pupil_name'
            $table->string('gender', 1)->nullable()->after('pupil_name');
        });
    }

    public function down(): void
    {
        Schema::table('pupils', function (Blueprint $table) {
            // This allows the migration to be reversed
            $table->dropColumn('gender');
        });
    }
};