<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FixRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Drop constraint
        \DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        
        // Update roles
        \DB::statement("UPDATE users SET role = 'student' WHERE role = 'user'");
        
        // Add new constraint
        \DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role::text = ANY (ARRAY['admin'::character varying, 'teacher'::character varying, 'student'::character varying]::text[]))");
        
        $this->command->info('Successfully updated roles from user to student');
    }
}
