<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index()
    {
        $roles = Role::withCount(['users', 'permissions'])->get();
        $permissions = Permission::all()->groupBy(function ($permission) {
            // Group by first word (e.g., "view users" -> "view")
            return explode(' ', $permission->name)[0];
        });

        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $permissions = Permission::all()->groupBy(function ($permission) {
            $parts = explode(' ', $permission->name);
            return count($parts) > 1 ? $parts[1] : $parts[0];
        });

        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name', 'regex:/^[a-z0-9-]+$/'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role = Role::create(['name' => $validated['name']]);

        if (!empty($validated['permissions'])) {
            $permissions = Permission::whereIn('id', $validated['permissions'])->get();
            $role->syncPermissions($permissions);
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($role)
            ->withProperties(['permissions' => $role->permissions->pluck('name')])
            ->log('Role created');

        return redirect()
            ->route('admin.roles.index')
            ->with('success', "Role '{$role->name}' created successfully.");
    }

    /**
     * Show the form for editing a role.
     */
    public function edit(Role $role)
    {
        $permissions = Permission::all()->groupBy(function ($permission) {
            $parts = explode(' ', $permission->name);
            return count($parts) > 1 ? $parts[1] : $parts[0];
        });

        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Role $role)
    {
        // Prevent editing admin role name
        if ($role->name === 'admin' && $request->name !== 'admin') {
            return back()->with('error', 'Cannot rename the admin role.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,' . $role->id, 'regex:/^[a-z0-9-]+$/'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role->update(['name' => $validated['name']]);

        $permissions = Permission::whereIn('id', $validated['permissions'] ?? [])->get();
        $role->syncPermissions($permissions);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($role)
            ->withProperties(['permissions' => $role->permissions->pluck('name')])
            ->log('Role updated');

        return redirect()
            ->route('admin.roles.index')
            ->with('success', "Role '{$role->name}' updated successfully.");
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role)
    {
        // Prevent deleting protected roles
        if (in_array($role->name, ['admin', 'user'])) {
            return back()->with('error', 'Cannot delete protected system roles.');
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return back()->with('error', 'Cannot delete role that has users assigned.');
        }

        $name = $role->name;

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['role_name' => $name])
            ->log('Role deleted');

        $role->delete();

        return redirect()
            ->route('admin.roles.index')
            ->with('success', "Role '{$name}' deleted successfully.");
    }
}
