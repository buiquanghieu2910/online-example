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
        // Drop constraint first
        \DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        
        // Update existing 'user' roles to 'student'
        \DB::statement("UPDATE users SET role = 'student' WHERE role = 'user'");
        
        // Add new constraint with 'student' instead of 'user'
        \DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role::text = ANY (ARRAY['admin'::character varying, 'teacher'::character varying, 'student'::character varying]::text[]))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert 'student' roles back to 'user'
        \DB::statement("UPDATE users SET role = 'user' WHERE role = 'student'");
        
        // Revert constraint
        \DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        \DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role::text = ANY (ARRAY['admin'::character varying, 'teacher'::character varying, 'user'::character varying]::text[]))");
    }
};
