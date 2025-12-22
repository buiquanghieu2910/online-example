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
        // Add grading status to user_exams
        Schema::table('user_exams', function (Blueprint $table) {
            $table->enum('grading_status', ['auto_graded', 'pending_review', 'manually_graded'])
                ->default('auto_graded')
                ->after('status');
        });

        // Add admin feedback to user_answers
        Schema::table('user_answers', function (Blueprint $table) {
            $table->text('admin_feedback')->nullable()->after('essay_answer');
            $table->decimal('essay_score', 5, 2)->nullable()->after('admin_feedback');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_exams', function (Blueprint $table) {
            $table->dropColumn('grading_status');
        });

        Schema::table('user_answers', function (Blueprint $table) {
            $table->dropColumn(['admin_feedback', 'essay_score']);
        });
    }
};
