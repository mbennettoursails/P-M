@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
<div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <h1 class="text-3xl font-bold text-gray-900">Application Settings</h1>
            <p class="mt-1 text-sm text-gray-600">Configure global application settings</p>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- General Settings -->
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">General Settings</h2>
                </div>
                <div class="p-6 space-y-6">
                    <!-- App Name -->
                    <div>
                        <label for="app_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Application Name
                        </label>
                        <input type="text" 
                               name="app_name" 
                               id="app_name" 
                               value="{{ old('app_name', $settings->app_name) }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               required>
                        @error('app_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- App Logo -->
                    <div>
                        <label for="app_logo" class="block text-sm font-medium text-gray-700 mb-2">
                            Application Logo
                        </label>
                        @if($settings->app_logo)
                            <div class="mb-3 flex items-center space-x-4">
                                <img src="{{ Storage::url($settings->app_logo) }}" alt="Current Logo" class="h-12 w-auto bg-gray-100 rounded p-1">
                                <label class="flex items-center text-sm text-gray-600">
                                    <input type="checkbox" name="remove_logo" value="1" class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    Remove current logo
                                </label>
                            </div>
                        @endif
                        <input type="file" 
                               name="app_logo" 
                               id="app_logo" 
                               accept="image/jpeg,image/png,image/svg+xml"
                               class="w-full text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="mt-1 text-xs text-gray-500">JPG, PNG, or SVG. Max 2MB.</p>
                        @error('app_logo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Timezone -->
                    <div>
                        <label for="timezone" class="block text-sm font-medium text-gray-700 mb-2">
                            Timezone
                        </label>
                        <select name="timezone" 
                                id="timezone" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach($timezones as $tz)
                                <option value="{{ $tz }}" {{ old('timezone', $settings->timezone) === $tz ? 'selected' : '' }}>
                                    {{ $tz }}
                                </option>
                            @endforeach
                        </select>
                        @error('timezone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Access Settings -->
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Access Settings</h2>
                </div>
                <div class="p-6 space-y-6">
                    <!-- Maintenance Mode -->
                    <div class="flex items-center justify-between">
                        <div>
                            <label for="maintenance_mode" class="text-sm font-medium text-gray-700">
                                Maintenance Mode
                            </label>
                            <p class="text-xs text-gray-500">When enabled, only admins can access the application</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="maintenance_mode" value="0">
                            <input type="checkbox" 
                                   name="maintenance_mode" 
                                   id="maintenance_mode" 
                                   value="1"
                                   {{ old('maintenance_mode', $settings->maintenance_mode) ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>

                    <!-- Registration Enabled -->
                    <div class="flex items-center justify-between">
                        <div>
                            <label for="registration_enabled" class="text-sm font-medium text-gray-700">
                                User Registration
                            </label>
                            <p class="text-xs text-gray-500">Allow new users to create accounts</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="registration_enabled" value="0">
                            <input type="checkbox" 
                                   name="registration_enabled" 
                                   id="registration_enabled" 
                                   value="1"
                                   {{ old('registration_enabled', $settings->registration_enabled) ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>

                    <!-- Default User Role -->
                    <div>
                        <label for="default_user_role" class="block text-sm font-medium text-gray-700 mb-2">
                            Default User Role
                        </label>
                        <select name="default_user_role" 
                                id="default_user_role" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach($roles as $role)
                                <option value="{{ $role }}" {{ old('default_user_role', $settings->default_user_role) === $role ? 'selected' : '' }}>
                                    {{ ucfirst($role) }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Role assigned to newly registered users</p>
                        @error('default_user_role')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end">
                <button type="submit" 
                        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium transition">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
