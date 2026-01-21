@extends('layouts.admin')

@section('title', 'User Details - ' . $user->name)

@section('content')
<div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $user->name }}</h1>
                    <p class="mt-1 text-sm text-gray-600">User ID: #{{ $user->id }}</p>
                </div>
                <a href="{{ route('admin.users') }}" class="text-blue-600 hover:text-blue-800">← Back to Users</a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Profile Card -->
            <div class="md:col-span-2 bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Profile Information</h3>
                <div class="flex items-center mb-6">
                    <div class="flex-shrink-0 h-16 w-16 rounded-full bg-blue-100 flex items-center justify-center">
                        <span class="text-blue-600 font-bold text-xl">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </span>
                    </div>
                    <div class="ml-4">
                        <p class="text-lg font-medium text-gray-900">{{ $user->name }}</p>
                        <p class="text-sm text-gray-600">{{ $user->email }}</p>
                    </div>
                </div>
                
                <dl class="space-y-3 border-t border-gray-200 pt-4">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">Role:</dt>
                        <dd>
                            @if($user->hasRole('admin'))
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    Admin
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    User
                                </span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">Email Verified:</dt>
                        <dd class="text-sm font-medium text-gray-900">
                            @if($user->email_verified_at)
                                <span class="text-green-600">Yes - {{ $user->email_verified_at->format('M d, Y') }}</span>
                            @else
                                <span class="text-yellow-600">Not verified</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">Joined:</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $user->created_at->format('F d, Y \a\t g:i A') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">Last Updated:</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $user->updated_at->diffForHumans() }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Account Stats -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Stats</h3>
                <div class="text-center py-4">
                    <p class="text-4xl font-bold text-gray-900">{{ $user->created_at->diffInDays(now()) }}</p>
                    <p class="text-sm text-gray-500 mt-1">days since signup</p>
                </div>
            </div>
        </div>

        <!-- Permissions -->
        @if($user->permissions->isNotEmpty())
            <div class="mt-6 bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Direct Permissions</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($user->permissions as $permission)
                        <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm">
                            {{ $permission->name }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Admin Actions -->
        @if($user->id !== auth()->id())
            <div class="mt-6 bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Admin Actions</h3>
                <form method="POST" action="{{ route('admin.users.toggle-admin', $user) }}" class="inline">
                    @csrf
                    <button type="submit" 
                            class="px-4 py-2 {{ $user->hasRole('admin') ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-purple-600 hover:bg-purple-700' }} text-white rounded-md text-sm font-medium transition"
                            onclick="return confirm('Are you sure you want to {{ $user->hasRole('admin') ? 'demote' : 'promote' }} this user?')">
                        @if($user->hasRole('admin'))
                            Demote to User
                        @else
                            Promote to Admin
                        @endif
                    </button>
                </form>
            </div>
        @else
            <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <p class="text-sm text-yellow-700">
                        <strong>Note:</strong> This is your own account. You cannot modify your own admin status.
                    </p>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
