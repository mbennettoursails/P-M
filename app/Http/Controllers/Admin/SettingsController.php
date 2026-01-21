<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Settings\GeneralSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index(GeneralSettings $settings)
    {
        $timezones = timezone_identifiers_list();
        $roles = Role::pluck('name', 'name');

        return view('admin.settings.index', compact('settings', 'timezones', 'roles'));
    }

    /**
     * Update the settings.
     */
    public function update(Request $request, GeneralSettings $settings)
    {
        $validated = $request->validate([
            'app_name' => ['required', 'string', 'max:255'],
            'app_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,svg', 'max:2048'],
            'timezone' => ['required', 'string', 'timezone'],
            'maintenance_mode' => ['boolean'],
            'registration_enabled' => ['boolean'],
            'default_user_role' => ['required', 'string', 'exists:roles,name'],
        ]);

        // Handle logo upload
        if ($request->hasFile('app_logo')) {
            // Delete old logo if exists
            if ($settings->app_logo && Storage::disk('public')->exists($settings->app_logo)) {
                Storage::disk('public')->delete($settings->app_logo);
            }

            $path = $request->file('app_logo')->store('logos', 'public');
            $settings->app_logo = $path;
        }

        // Handle logo removal
        if ($request->boolean('remove_logo') && $settings->app_logo) {
            if (Storage::disk('public')->exists($settings->app_logo)) {
                Storage::disk('public')->delete($settings->app_logo);
            }
            $settings->app_logo = null;
        }

        // Update settings
        $settings->app_name = $validated['app_name'];
        $settings->timezone = $validated['timezone'];
        $settings->maintenance_mode = $request->boolean('maintenance_mode');
        $settings->registration_enabled = $request->boolean('registration_enabled');
        $settings->default_user_role = $validated['default_user_role'];

        $settings->save();

        // Log the activity
        activity()
            ->causedBy(auth()->user())
            ->withProperties(['settings' => $settings->toArray()])
            ->log('Settings updated');

        return back()->with('success', 'Settings updated successfully.');
    }
}
