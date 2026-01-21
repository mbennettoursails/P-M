@extends('layouts.admin')

@section('title', 'Edit Role')

@section('content')
<div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <a href="{{ route('admin.roles.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">← Back to Roles</a>
            <h1 class="text-3xl font-bold text-gray-900 mt-2">Edit Role: {{ $role->name }}</h1>
        </div>
    </div>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form method="POST" action="{{ route('admin.roles.update', $role) }}">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-lg shadow">
                <div class="p-6 space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Role Name (slug)
                        </label>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               value="{{ old('name', $role->name) }}"
                               pattern="[a-z0-9-]+"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 {{ in_array($role->name, ['admin', 'user']) ? 'bg-gray-100' : '' }}"
                               {{ in_array($role->name, ['admin', 'user']) ? 'readonly' : '' }}
                               required>
                        @if(in_array($role->name, ['admin', 'user']))
                            <p class="mt-1 text-xs text-gray-500">System role names cannot be changed</p>
                        @endif
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Permissions -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
                        <div class="space-y-4">
                            @foreach($permissions as $group => $perms)
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h4 class="text-sm font-medium text-gray-900 capitalize mb-2">{{ $group }}</h4>
                                    <div class="space-y-2">
                                        @foreach($perms as $permission)
                                            <label class="flex items-center">
                                                <input type="checkbox" 
                                                       name="permissions[]" 
                                                       value="{{ $permission->id }}"
                                                       {{ in_array($permission->id, old('permissions', $rolePermissions)) ? 'checked' : '' }}
                                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                <span class="ml-2 text-sm text-gray-700">{{ $permission->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 flex justify-between">
                    @unless(in_array($role->name, ['admin', 'user']))
                        <form method="POST" 
                              action="{{ route('admin.roles.destroy', $role) }}" 
                              onsubmit="return confirm('Are you sure you want to delete this role?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition">
                                Delete Role
                            </button>
                        </form>
                    @else
                        <div></div>
                    @endunless

                    <div class="flex space-x-3">
                        <a href="{{ route('admin.roles.index') }}" 
                           class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                            Update Role
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
