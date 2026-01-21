<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Log the login activity
        activity()
            ->causedBy(Auth::user())
            ->log('User logged in');

        // Role-based redirect after login
        return redirect()->intended($this->redirectTo());
    }

    /**
     * Determine the redirect path based on user role.
     * Uses Spatie Permission hasRole() method.
     */
    protected function redirectTo(): string
    {
        $user = Auth::user();
        
        // Admin goes to admin dashboard
        if ($user && $user->hasRole('admin')) {
            return route('admin.dashboard');
        }
        
        // Regular users go to user dashboard
        return route('dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Log the logout activity before logging out
        if (Auth::check()) {
            activity()
                ->causedBy(Auth::user())
                ->log('User logged out');
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
