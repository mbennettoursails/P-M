<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUsersController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::with('roles');

        // Filter by role
        if ($request->filled('role')) {
            $query->role($request->role);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $users = $query->latest()->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load('roles', 'permissions');
        
        return view('admin.users.show', compact('user'));
    }

    /**
     * Toggle admin status for a user.
     */
    public function toggleAdmin(Request $request, User $user)
    {
        // Prevent users from demoting themselves
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own admin status.');
        }

        // Toggle the admin role
        if ($user->hasRole('admin')) {
            $user->removeRole('admin');
            $user->assignRole('user');
            $status = 'demoted to user';
        } else {
            $user->removeRole('user');
            $user->assignRole('admin');
            $status = 'promoted to admin';
        }

        // Log the activity
        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties(['new_role' => $user->roles->first()?->name])
            ->log("User {$status}");

        return back()->with('success', "User {$user->name} has been {$status}.");
    }
}
