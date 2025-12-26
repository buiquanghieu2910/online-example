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
        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn('school_year');
            $table->string('subject')->after('description');
            $table->year('start_year')->after('subject');
            $table->year('end_year')->after('start_year');
            
            // Add unique constraint for name-subject-years
            $table->unique(['name', 'subject', 'start_year', 'end_year'], 'unique_class_subject_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropUnique('unique_class_subject_year');
            $table->dropColumn(['subject', 'start_year', 'end_year']);
            $table->string('school_year')->after('description');
        });
    }
};
