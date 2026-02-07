<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Finance User',
                'email' => 'finance@example.com', 
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }

        $this->command->info('✅ UserSeeder completed successfully!');
        $this->command->info('👤 Created ' . count($users) . ' users');
        $this->command->info('🔑 Login credentials:');
        $this->command->info('   Email: admin@example.com | Password: password');
        $this->command->info('   Email: finance@example.com | Password: password');
        $this->command->info('   Email: test@example.com | Password: password');
    }
}
