<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DevUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!in_array(config('app.env'), ['local', 'testing'])) {
            return;
        }

        $users = [
            ['email' => 'super@example.com',    'role' => \App\Models\Role::ROLE_SUPER_USER],
            ['email' => 'staff@example.com',    'role' => \App\Models\Role::ROLE_STAFF],
            ['email' => 'seller@example.com',   'role' => \App\Models\Role::ROLE_SELLER],
            ['email' => 'customer@example.com', 'role' => \App\Models\Role::ROLE_CUSTOMER],
            ['email' => 'delivery@example.com', 'role' => \App\Models\Role::ROLE_DELIVERY_PERSON],
            ['email' => 'user@example.com',     'role' => null],
        ];

        foreach ($users as $data) {
            $user = \App\Models\User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => \Illuminate\Support\Str::ucfirst(explode('@', $data['email'])[0]),
                    'password' => \Illuminate\Support\Facades\Hash::make('password'),
                ]
            );

            if ($data['role']) {
                $user->assignRole($data['role']); // assigned_by = null (system)
            }
        }
    }
}
