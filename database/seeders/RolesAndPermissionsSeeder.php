<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User management
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // Role management
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            'assign roles',
            
            // Settings management
            'view settings',
            'edit settings',
            
            // Audit logs
            'view audit logs',
            'clear audit logs',
            
            // System
            'view system health',
            'clear cache',
            'access telescope',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Admin - has all permissions (acts as superadmin)
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // User - basic permissions (none by default)
        $userRole = Role::firstOrCreate(['name' => 'user']);
        // Users have no special permissions by default

        $this->command->info('✅ Roles and permissions created successfully!');
        $this->command->table(
            ['Role', 'Permissions Count'],
            [
                ['admin', $adminRole->permissions->count()],
                ['user', $userRole->permissions->count()],
            ]
        );
    }
}
