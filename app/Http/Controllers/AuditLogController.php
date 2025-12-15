<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * ✅ AuditLogController - View audit logs (Admin only)
 */
class AuditLogController extends Controller
{
    /**
     * Display audit logs with filters
     */
    public function index(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'user_id' => 'nullable|integer|exists:pengguna,id',
            'entity_type' => 'nullable|string|max:50',
            'action' => 'nullable|string|max:50',
            'search' => 'nullable|string|max:100',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        // Build query
        $query = AuditLog::with('user')
            ->orderByDesc('created_at');

        // Apply filters
        if (!empty($validated['user_id'])) {
            $query->byUser($validated['user_id']);
        }

        if (!empty($validated['entity_type'])) {
            $query->byEntityType($validated['entity_type']);
        }

        if (!empty($validated['action'])) {
            $query->byAction($validated['action']);
        }

        if (!empty($validated['search'])) {
            $query->search($validated['search']);
        }

        if (!empty($validated['date_from']) || !empty($validated['date_to'])) {
            $query->dateRange(
                $validated['date_from'] ?? null,
                $validated['date_to'] ?? null
            );
        }

        // Paginate
        $logs = $query->paginate(25)->withQueryString();

        // Get filter options
        $users = User::select('id', 'nama_lengkap')
            ->whereIn('id', AuditLog::select('user_id')->distinct())
            ->orderBy('nama_lengkap')
            ->get();

        $entityTypes = AuditLog::getEntityTypeOptions();
        $actions = AuditLog::getActionOptions();

        // Stats
        $stats = [
            'total' => AuditLog::count(),
            'today' => AuditLog::whereDate('created_at', today())->count(),
            'this_week' => AuditLog::recent(7)->count(),
        ];

        return view('audit_logs.index', compact(
            'logs',
            'users',
            'entityTypes',
            'actions',
            'stats',
            'validated'
        ));
    }

    /**
     * Show detail of a single audit log
     */
    public function show(AuditLog $audit_log)
    {
        return view('audit_logs.show', compact('audit_log'));
    }

    /**
     * Get logs for specific entity (AJAX)
     */
    public function forEntity(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|max:50',
            'id' => 'required|integer',
        ]);

        $logs = AuditLog::byEntity($validated['type'], $validated['id'])
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'logs' => $logs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'action_label' => $log->action_label,
                    'user_name' => $log->user_name,
                    'created_at' => $log->formatted_date,
                    'old_values' => $log->old_values,
                    'new_values' => $log->new_values,
                ];
            }),
        ]);
    }
}
