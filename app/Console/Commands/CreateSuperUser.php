<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('user:create-super-user')]
#[Description('Create a new Super User')]
class CreateSuperUser extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->ask('Enter the email address for the Super User');

        $validator = \Illuminate\Support\Facades\Validator::make(['email' => $email], [
            'email' => ['required', 'email', 'unique:users,email'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return 1;
        }

        $password = $this->secret('Enter the password (minimum 8 characters)');
        $passwordConfirmation = $this->secret('Confirm the password');

        if ($password !== $passwordConfirmation) {
            $this->error('Passwords do not match.');
            return 1;
        }

        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters.');
            return 1;
        }

        $user = \App\Models\User::create([
            'email' => $email,
            'name' => ucfirst(explode('@', $email)[0]),
            'password' => \Illuminate\Support\Facades\Hash::make($password),
        ]);

        $role = \App\Models\Role::where('slug', \App\Models\Role::ROLE_SUPER_USER)->first();

        if (!$role) {
            $this->error('Super User role not found. Please run RoleSeeder.');
            return 1;
        }

        $user->assignRole($role);

        $this->info("Super User {$email} created successfully.");

        return 0;
    }
}
