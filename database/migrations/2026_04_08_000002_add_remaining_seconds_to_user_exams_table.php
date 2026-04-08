<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_exams', function (Blueprint $table) {
            $table->unsignedInteger('remaining_seconds')->nullable()->after('started_at');
        });
    }

    public function down(): void
    {
        Schema::table('user_exams', function (Blueprint $table) {
            $table->dropColumn('remaining_seconds');
        });
    }
};
