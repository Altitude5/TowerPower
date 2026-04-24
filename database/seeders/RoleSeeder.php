<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'Super User',      'slug' => Role::ROLE_SUPER_USER],
            ['name' => 'Staff',           'slug' => Role::ROLE_STAFF],
            ['name' => 'Seller',          'slug' => Role::ROLE_SELLER],
            ['name' => 'Customer',        'slug' => Role::ROLE_CUSTOMER],
            ['name' => 'Delivery Person', 'slug' => Role::ROLE_DELIVERY_PERSON],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['slug' => $role['slug']], ['name' => $role['name']]);
        }
    }
}
