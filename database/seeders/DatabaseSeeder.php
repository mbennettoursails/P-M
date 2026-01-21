<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting database seeding...');
        $this->command->newLine();

        // 1. Create roles and permissions first
        $this->call(RolesAndPermissionsSeeder::class);
        $this->command->newLine();

        // 2. Create admin user
        $this->call(AdminUserSeeder::class);
        $this->command->newLine();

        // 3. Create test user (optional, for development)
        if (app()->environment('local', 'development')) {
            $testUser = User::firstOrCreate(
                ['email' => 'user@example.com'],
                [
                    'name' => 'Test User',
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ]
            );
            
            if (!$testUser->hasRole('user')) {
                $testUser->assignRole('user');
            }

            $this->command->info('✅ Test user created (user@example.com / password)');
        }

        $this->command->newLine();
        $this->command->info('🎉 Database seeding completed!');
    }
}
