<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::insert([
            ['name' => 'view-users', 'slug' => 'view-users', 'module' => 'users'],
            ['name' => 'create-users', 'slug' => 'create-users', 'module' => 'users'],
            ['name' => 'edit-users', 'slug' => 'edit-users', 'module' => 'users'],
            ['name' => 'deactivate-users', 'slug' => 'deactivate-users', 'module' => 'users'],

            ['name' => 'view-roles', 'slug' => 'view-roles', 'module' => 'roles'],
            ['name' => 'create-roles', 'slug' => 'create-roles', 'module' => 'roles'],
            ['name' => 'edit-roles', 'slug' => 'edit-roles', 'module' => 'roles'],
            ['name' => 'delete-roles', 'slug' => 'delete-roles', 'module' => 'roles'],

            ['name' => 'assign-permissions', 'slug' => 'assign-permissions', 'module' => 'roles'],

            ['name' => 'game-day-start', 'slug' => 'game-day-start', 'module' => 'game-day'],
            ['name' => 'game-day-close', 'slug' => 'game-day-close', 'module' => 'game-day'],
        ]);
    }
}
