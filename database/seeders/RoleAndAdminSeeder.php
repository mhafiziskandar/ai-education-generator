<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleAndAdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $educatorRole = Role::firstOrCreate(['name' => 'educator']);
        $studentRole = Role::firstOrCreate(['name' => 'student']);

        $adminPermission = Permission::firstOrCreate(['name' => 'view admin panel']);
        $educatorPermission = Permission::firstOrCreate(['name' => 'view educator panel']);
        $studentPermission = Permission::firstOrCreate(['name' => 'view student panel']);

        $adminRole->syncPermissions([$adminPermission, $educatorPermission, $studentPermission]);
        $educatorRole->syncPermissions([$educatorPermission]);
        $studentRole->syncPermissions([$studentPermission]);

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );
        
        $admin->assignRole('admin');

        $educator = User::firstOrCreate(
            ['email' => 'educator@example.com'],
            [
                'name' => 'Educator User',
                'password' => Hash::make('password'),
            ]
        );
        
        $educator->assignRole('educator');

        $educator = User::firstOrCreate(
            ['email' => 'student@example.com'],
            [
                'name' => 'Student User',
                'password' => Hash::make('password'),
            ]
        );
        
        $educator->assignRole('student');

        $this->command->info('Roles created or already exist: admin, educator');
        $this->command->info('Permissions created or already exist: view admin panel, view educator panel');
        $this->command->info('Users created or already exist: admin@example.com, educator@example.com');
    }
}