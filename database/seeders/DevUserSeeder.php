<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DevUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! in_array(config('app.env'), ['local', 'testing'])) {
            return;
        }

        $users = [
            ['email' => 'super@example.com',    'role' => Role::ROLE_SUPER_USER],
            ['email' => 'staff@example.com',    'role' => Role::ROLE_STAFF],
            ['email' => 'seller@example.com',   'role' => Role::ROLE_SELLER],
            ['email' => 'customer@example.com', 'role' => Role::ROLE_CUSTOMER],
            ['email' => 'delivery@example.com', 'role' => Role::ROLE_DELIVERY_PERSON],
            ['email' => 'user@example.com',     'role' => null],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => Str::ucfirst(explode('@', $data['email'])[0]),
                    'password' => Hash::make('password'),
                ]
            );

            if ($data['role']) {
                $user->assignRole($data['role']); // assigned_by = null (system)
            }
        }
    }
}
