<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('student')->after('email');
            });
        }

        if (!Schema::hasColumn('users', 'grade_level')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('grade_level')->nullable()->after('role');
            });
        }

        if (!Schema::hasColumn('users', 'student_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('student_id')->nullable()->after('grade_level');
            });
        }

        if (DB::table('users')->where('role', 'admin')->count() === 0) {
            DB::table('users')
                ->orderBy('id')
                ->limit(1)
                ->update(['role' => 'admin']);
        }

        DB::table('users')
            ->whereNull('role')
            ->orWhere('role', '')
            ->update(['role' => 'student']);
    }

    public function down()
    {
        DB::table('users')->update([
            'role' => null,
            'grade_level' => null,
            'student_id' => null,
        ]);
    }
};