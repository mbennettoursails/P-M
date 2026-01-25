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

        // Create permissions grouped by category
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
            
            // ======================
            // Proposal/Decision permissions
            // ======================
            'view proposals',
            'create proposals',
            'edit proposals',
            'delete proposals',
            'publish proposals',        // Move from draft to feedback
            'close proposals',          // Close voting and set outcome
            
            // Voting permissions
            'vote on proposals',
            'view vote results',
            
            // Comment permissions
            'comment on proposals',
            'moderate comments',        // Edit/delete others' comments
            
            // ======================
            // Content permissions
            // ======================
            'view news',
            'create news',
            'edit news',
            'delete news',
            'publish news',
            
            'view events',
            'create events',
            'edit events',
            'delete events',
            'publish events',
            
            // ======================
            // Community permissions
            // ======================
            'view mutual aid',
            'create mutual aid requests',
            'manage mutual aid',        // Admin functions for mutual aid
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ======================
        // Create Roles
        // ======================

        // Admin - has all permissions (superadmin)
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Reijikai (理事会) - Committee Members / Board Members
        // Full governance capabilities
        $reijikaiRole = Role::firstOrCreate(['name' => 'reijikai']);
        $reijikaiRole->givePermissionTo([
            // Proposals - full control
            'view proposals',
            'create proposals',
            'edit proposals',
            'delete proposals',
            'publish proposals',
            'close proposals',
            'vote on proposals',
            'view vote results',
            'comment on proposals',
            'moderate comments',
            
            // Content management
            'view news',
            'create news',
            'edit news',
            'publish news',
            'view events',
            'create events',
            'edit events',
            'publish events',
            
            // Community
            'view mutual aid',
            'create mutual aid requests',
            'manage mutual aid',
            
            // Limited admin
            'view users',
        ]);

        // Shokuin (職員) - Staff / Administrative Personnel
        // Content management focus, limited governance
        $shokuinRole = Role::firstOrCreate(['name' => 'shokuin']);
        $shokuinRole->givePermissionTo([
            // Proposals - can participate but not create
            'view proposals',
            'vote on proposals',
            'view vote results',
            'comment on proposals',
            
            // Content management - full control
            'view news',
            'create news',
            'edit news',
            'delete news',
            'publish news',
            'view events',
            'create events',
            'edit events',
            'delete events',
            'publish events',
            
            // Community
            'view mutual aid',
            'create mutual aid requests',
            'manage mutual aid',
            
            // User viewing
            'view users',
        ]);

        // Volunteer (ボランティア) - General Members
        // Community participation focus
        $volunteerRole = Role::firstOrCreate(['name' => 'volunteer']);
        $volunteerRole->givePermissionTo([
            // Proposals - view and comment only (no voting by default)
            'view proposals',
            'comment on proposals',
            
            // Content - view only
            'view news',
            'view events',
            
            // Community - full participation
            'view mutual aid',
            'create mutual aid requests',
        ]);

        // User - basic role (legacy, minimal permissions)
        $userRole = Role::firstOrCreate(['name' => 'user']);
        $userRole->givePermissionTo([
            'view news',
            'view events',
            'view mutual aid',
        ]);

        $this->command->info('✅ Roles and permissions created successfully!');
        $this->command->newLine();
        $this->command->table(
            ['Role', 'Japanese', 'Description', 'Permissions'],
            [
                ['admin', '管理者', 'System Administrator', $adminRole->permissions->count()],
                ['reijikai', '理事会', 'Committee/Board Members', $reijikaiRole->permissions->count()],
                ['shokuin', '職員', 'Staff/Administrative', $shokuinRole->permissions->count()],
                ['volunteer', 'ボランティア', 'General Members', $volunteerRole->permissions->count()],
                ['user', 'ユーザー', 'Basic User (legacy)', $userRole->permissions->count()],
            ]
        );
    }
}