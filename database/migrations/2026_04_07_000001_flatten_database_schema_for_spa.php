<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('exams', 'min_score') && !Schema::hasColumn('exams', 'pass_score')) {
            Schema::table('exams', function (Blueprint $table) {
                $table->renameColumn('min_score', 'pass_score');
            });
        }

        if (Schema::hasColumn('exams', 'min_score') && Schema::hasColumn('exams', 'pass_score')) {
            DB::table('exams')
                ->whereNull('pass_score')
                ->update(['pass_score' => DB::raw('min_score')]);

            Schema::table('exams', function (Blueprint $table) {
                $table->dropColumn('min_score');
            });
        }

        if (Schema::hasColumn('users', 'class_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropConstrainedForeignId('class_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('exams', 'pass_score') && !Schema::hasColumn('exams', 'min_score')) {
            Schema::table('exams', function (Blueprint $table) {
                $table->renameColumn('pass_score', 'min_score');
            });
        }

        if (!Schema::hasColumn('users', 'class_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('class_id')->nullable()->after('role')->constrained('classes')->onDelete('set null');
            });
        }
    }
};

