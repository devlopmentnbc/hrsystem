<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Support\AccessControl;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        AccessControl::ensureAdminSetup();
        abort_unless(auth()->check() && auth()->user()->canAccess('audit_logs.view'), 403);

        $query = AuditLog::with('user')->latest();

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('model')) {
            $query->where('auditable_type', $request->model);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('auditable_type', 'like', "%{$search}%")
                    ->orWhere('old_values', 'like', "%{$search}%")
                    ->orWhere('new_values', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(25)->withQueryString();
        $users = User::orderBy('name')->get();
        $events = AuditLog::query()->select('event')->distinct()->orderBy('event')->pluck('event');
        $models = AuditLog::query()->select('auditable_type')->whereNotNull('auditable_type')->distinct()->orderBy('auditable_type')->pluck('auditable_type');

        return view('audit_logs.index', compact('logs', 'users', 'events', 'models'));
    }
}
