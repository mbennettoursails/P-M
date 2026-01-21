@extends('layouts.admin')

@section('title', 'Roles & Permissions')

@section('content')
<div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Roles & Permissions</h1>
                    <p class="mt-1 text-sm text-gray-600">Manage user roles and their permissions</p>
                </div>
                <a href="{{ route('admin.roles.create') }}" 
                   class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                    + Add Role
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Roles List -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Roles</h2>
            </div>
            <div class="divide-y divide-gray-200">
                @foreach($roles as $role)
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50">
                        <div class="flex-1">
                            <div class="flex items-center">
                                <h3 class="text-lg font-medium text-gray-900 capitalize">{{ $role->name }}</h3>
                                @if(in_array($role->name, ['admin', 'user']))
                                    <span class="ml-2 px-2 py-0.5 text-xs bg-gray-100 text-gray-500 rounded">System</span>
                                @endif
                            </div>
                            <div class="mt-1 flex items-center space-x-4 text-sm text-gray-500">
                                <span>{{ $role->users_count }} {{ Str::plural('user', $role->users_count) }}</span>
                                <span>•</span>
                                <span>{{ $role->permissions_count }} {{ Str::plural('permission', $role->permissions_count) }}</span>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('admin.roles.edit', $role) }}" 
                               class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition text-sm">
                                Edit
                            </a>
                            @unless(in_array($role->name, ['admin', 'user']))
                                <form method="POST" 
                                      action="{{ route('admin.roles.destroy', $role) }}" 
                                      onsubmit="return confirm('Delete this role?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 bg-red-100 text-red-700 rounded hover:bg-red-200 transition text-sm">
                                        Delete
                                    </button>
                                </form>
                            @endunless
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Permissions Overview -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">All Permissions</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($permissions as $group => $perms)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-sm font-medium text-gray-900 capitalize mb-2">{{ $group }}</h3>
                            <ul class="space-y-1">
                                @foreach($perms as $permission)
                                    <li class="text-xs text-gray-600">{{ $permission->name }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
