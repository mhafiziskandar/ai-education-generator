<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        $this->call([
            RoleAndAdminSeeder::class,
            // DocumentSeeder::class,
            // GeneratedContentSeeder::class,
            // DemoDataSeeder::class,
            // QuizSeeder::class,
            // GeneratedContentSeeder::class,
        ]);
    }
}
