<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('generated_contents', function (Blueprint $table) {
            $table->decimal('api_cost', 8, 4)->default(0)->after('content');
        });
    }

    public function down()
    {
        Schema::table('generated_contents', function (Blueprint $table) {
            $table->dropColumn('api_cost');
        });
    }
};