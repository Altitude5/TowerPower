<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'Super User',      'slug' => \App\Models\Role::ROLE_SUPER_USER],
            ['name' => 'Staff',           'slug' => \App\Models\Role::ROLE_STAFF],
            ['name' => 'Seller',          'slug' => \App\Models\Role::ROLE_SELLER],
            ['name' => 'Customer',        'slug' => \App\Models\Role::ROLE_CUSTOMER],
            ['name' => 'Delivery Person', 'slug' => \App\Models\Role::ROLE_DELIVERY_PERSON],
        ];

        foreach ($roles as $role) {
            \App\Models\Role::firstOrCreate(['slug' => $role['slug']], ['name' => $role['name']]);
        }
    }
}
