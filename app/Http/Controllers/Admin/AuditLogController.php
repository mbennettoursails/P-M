<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    /**
     * Display the audit log.
     */
    public function index(Request $request)
    {
        $query = Activity::with('causer', 'subject');

        // Filter by log name
        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        // Filter by causer (user)
        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id)
                  ->where('causer_type', 'App\Models\User');
        }

        // Filter by subject type
        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }

        // Filter by event
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search in description
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $activities = $query->latest()->paginate(25);

        // Get filter options
        $logNames = Activity::distinct()->pluck('log_name')->filter();
        $subjectTypes = Activity::distinct()
            ->pluck('subject_type')
            ->filter()
            ->map(fn($type) => class_basename($type));
        $events = Activity::distinct()->pluck('event')->filter();

        return view('admin.audit-logs.index', compact(
            'activities',
            'logNames',
            'subjectTypes',
            'events'
        ));
    }

    /**
     * Display a single activity log entry.
     */
    public function show(Activity $activity)
    {
        $activity->load('causer', 'subject');

        return view('admin.audit-logs.show', compact('activity'));
    }

    /**
     * Clear old activity logs.
     */
    public function clear(Request $request)
    {
        $validated = $request->validate([
            'older_than_days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        $days = $validated['older_than_days'];
        $cutoff = now()->subDays($days);

        $deleted = Activity::where('created_at', '<', $cutoff)->delete();

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['deleted_count' => $deleted, 'older_than_days' => $days])
            ->log('Audit logs cleared');

        return back()->with('success', "Deleted {$deleted} activity log entries older than {$days} days.");
    }
}
