<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'doctor@example.test'],
            ['name' => 'Dr. Demo', 'password' => Hash::make('password'), 'role' => 'doctor']
        );

        User::updateOrCreate(
            ['email' => 'manager@example.test'],
            ['name' => 'Manager Demo', 'password' => Hash::make('password'), 'role' => 'manager']
        );

        User::updateOrCreate(
            ['email' => 'patient@example.test'],
            ['name' => 'Patient Demo', 'password' => Hash::make('password'), 'role' => 'patient']
        );
    }
}
