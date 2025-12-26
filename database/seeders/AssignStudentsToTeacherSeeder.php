<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AssignStudentsToTeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teacher = \App\Models\User::where('role', 'teacher')->first();
        
        if ($teacher) {
            \App\Models\User::where('role', 'student')
                ->whereNull('teacher_id')
                ->update(['teacher_id' => $teacher->id]);
            
            $this->command->info('Assigned all students to teacher: ' . $teacher->name);
        } else {
            $this->command->warn('No teacher found in database');
        }
    }
}
