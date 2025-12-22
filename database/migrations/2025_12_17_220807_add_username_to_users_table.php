<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->after('name');
        });
        
        // Update existing users with default usernames
        DB::table('users')->get()->each(function ($user) {
            $username = strtolower(str_replace(' ', '', $user->name));
            $counter = 1;
            $originalUsername = $username;
            
            while (DB::table('users')->where('username', $username)->exists()) {
                $username = $originalUsername . $counter;
                $counter++;
            }
            
            DB::table('users')->where('id', $user->id)->update(['username' => $username]);
        });
        
        // Make username required after populating
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');
        });
    }
};
