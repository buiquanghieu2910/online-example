<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MigrateTeacherIdToPivotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Migrate existing teacher_id to pivot table
        $students = \App\Models\User::where('role', 'student')
            ->whereNotNull('teacher_id')
            ->get();
        
        foreach ($students as $student) {
            // Insert into pivot table if not exists
            \DB::table('teacher_student')->insertOrIgnore([
                'teacher_id' => $student->teacher_id,
                'student_id' => $student->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        $this->command->info('Migrated ' . $students->count() . ' student relationships to pivot table');
    }
}
