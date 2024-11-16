<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up()
    {
        // First make sure the role column accepts the correct values
        Schema::table('users', function (Blueprint $table) {
            // Drop any existing default constraint
            if (Schema::hasColumn('users', 'role')) {
                $table->string('role')->nullable()->change();
            } else {
                $table->string('role')->nullable()->after('email');
            }
        });

        // Create an admin if none exists
        if (DB::table('users')->where('role', 'admin')->count() === 0) {
            DB::table('users')
                ->orderBy('id')
                ->limit(1)
                ->update(['role' => 'admin']);
        }

        // Create an educator if none exists and email doesn't exist
        if (DB::table('users')->where('role', 'educator')->count() === 0 
            && DB::table('users')->where('email', 'educator@example.com')->count() === 0) {
            // Create a new educator user
            DB::table('users')->insert([
                'name' => 'Default Educator',
                'email' => 'educator@example.com',
                'password' => Hash::make('password'),
                'role' => 'educator',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            // If email exists but role is not educator, update to educator
            DB::table('users')
                ->where('email', 'educator@example.com')
                ->update(['role' => 'educator']);
        }

        // Fix any remaining null or invalid roles to be 'student'
        DB::table('users')
            ->whereNull('role')
            ->orWhere('role', '')
            ->orWhereNotIn('role', ['admin', 'educator', 'student'])
            ->update(['role' => 'student']);
    }

    public function down()
    {
        // Optional: Reset roles if needed
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('student')->change();
        });
    }
};