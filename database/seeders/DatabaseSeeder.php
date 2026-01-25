<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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

        // 3. Create test users for each role (development only)
        if (app()->environment('local', 'development')) {
            $this->createTestUsers();
        }

        $this->command->newLine();
        $this->command->info('🎉 Database seeding completed!');
    }

    /**
     * Create test users for each cooperative role.
     */
    protected function createTestUsers(): void
    {
        $this->command->info('👥 Creating test users for development...');
        $this->command->newLine();

        // Reijikai (Committee Members) - 5 users for quorum testing
        $reijikaiUsers = [
            ['name' => '田中 太郎', 'email' => 'tanaka@example.com'],
            ['name' => '佐藤 花子', 'email' => 'sato@example.com'],
            ['name' => '鈴木 一郎', 'email' => 'suzuki@example.com'],
            ['name' => '高橋 美咲', 'email' => 'takahashi@example.com'],
            ['name' => '伊藤 健太', 'email' => 'ito@example.com'],
        ];

        foreach ($reijikaiUsers as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            
            if (!$user->hasRole('reijikai')) {
                $user->assignRole('reijikai');
            }
        }
        $this->command->info("  ✅ Created 5 Reijikai (理事会) users");

        // Shokuin (Staff) - 3 users
        $shokuinUsers = [
            ['name' => '山田 裕子', 'email' => 'yamada@example.com'],
            ['name' => '中村 大輔', 'email' => 'nakamura@example.com'],
            ['name' => '小林 さくら', 'email' => 'kobayashi@example.com'],
        ];

        foreach ($shokuinUsers as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            
            if (!$user->hasRole('shokuin')) {
                $user->assignRole('shokuin');
            }
        }
        $this->command->info("  ✅ Created 3 Shokuin (職員) users");

        // Volunteer (General Members) - 3 users
        $volunteerUsers = [
            ['name' => '渡辺 明', 'email' => 'watanabe@example.com'],
            ['name' => '加藤 優', 'email' => 'kato@example.com'],
            ['name' => '吉田 真理', 'email' => 'yoshida@example.com'],
        ];

        foreach ($volunteerUsers as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            
            if (!$user->hasRole('volunteer')) {
                $user->assignRole('volunteer');
            }
        }
        $this->command->info("  ✅ Created 3 Volunteer (ボランティア) users");

        // Legacy user role - 1 user
        $testUser = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        
        if (!$testUser->hasRole('user')) {
            $testUser->assignRole('user');
        }
        $this->command->info("  ✅ Created 1 basic User");

        $this->command->newLine();
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['admin', 'admin@example.com', 'Admin123!Change'],
                ['reijikai', 'tanaka@example.com', 'password'],
                ['reijikai', 'sato@example.com', 'password'],
                ['reijikai', 'suzuki@example.com', 'password'],
                ['reijikai', 'takahashi@example.com', 'password'],
                ['reijikai', 'ito@example.com', 'password'],
                ['shokuin', 'yamada@example.com', 'password'],
                ['shokuin', 'nakamura@example.com', 'password'],
                ['shokuin', 'kobayashi@example.com', 'password'],
                ['volunteer', 'watanabe@example.com', 'password'],
                ['volunteer', 'kato@example.com', 'password'],
                ['volunteer', 'yoshida@example.com', 'password'],
                ['user', 'user@example.com', 'password'],
            ]
        );

        $this->command->warn('⚠️  All test users use password: "password" - DO NOT USE IN PRODUCTION');
    }
}