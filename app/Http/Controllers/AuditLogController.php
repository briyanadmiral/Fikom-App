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
    /**
     * Show detail of a single audit log
     */
    public function show(AuditLog $audit_log)
    {
        if (request()->ajax()) {
            return view('audit_logs.show_modal', compact('audit_log'));
        }
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
    /**
     * Export audit logs to Excel
     */
    public function export(Request $request)
    {
        // Reuse detailed filtering logic from index
        $query = AuditLog::with('user')->orderByDesc('created_at');

        if (!empty($request->user_id)) $query->byUser($request->user_id);
        if (!empty($request->entity_type)) $query->byEntityType($request->entity_type);
        if (!empty($request->action)) $query->byAction($request->action);
        if (!empty($request->search)) $query->search($request->search);
        if (!empty($request->date_from) || !empty($request->date_to)) {
            $query->dateRange($request->date_from ?? null, $request->date_to ?? null);
        }

        $logs = $query->limit(5000)->get(); // Limit to 5000 rows for performance

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $headers = ['Waktu', 'User', 'Aksi', 'Tipe Entitas', 'Nama Objek', 'Detail Lama', 'Detail Baru', 'IP Address', 'Browser/OS'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);

        // Data
        $row = 2;
        foreach ($logs as $log) {
            $sheet->setCellValue('A' . $row, $log->created_at->format('Y-m-d H:i:s'));
            $sheet->setCellValue('B' . $row, $log->user_name ?? 'System');
            $sheet->setCellValue('C' . $row, $log->action_label);
            $sheet->setCellValue('D' . $row, $log->entity_type_label);
            $sheet->setCellValue('E' . $row, $log->entity_name);
            $sheet->setCellValue('F' . $row, json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $sheet->setCellValue('G' . $row, json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $sheet->setCellValue('H' . $row, $log->ip_address);
            $sheet->setCellValue('I' . $row, $log->browser_info);
            $row++;
        }

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'Audit_Log_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $writer->save('php://output');
        exit;
    }

    /**
     * Prune logs older than 1 year (Admin only)
     */
    public function prune(Request $request)
    {
        // Authorization: Only Admin (Role ID 1) can prune logs
        if (auth()->user()->peran_id !== 1) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'retention_period' => 'required|in:1,2,3', // Years
        ]);

        $years = (int) $validated['retention_period'];
        $date = now()->subYears($years);

        $count = AuditLog::where('created_at', '<', $date)->count();
        
        if ($count > 0) {
            AuditLog::where('created_at', '<', $date)->delete();
            
            // Log this action!
            AuditLog::create([
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->nama_lengkap,
                'action' => 'delete',
                'entity_type' => 'AuditLog',
                'entity_id' => 0,
                'entity_name' => "Pruned {$count} logs older than {$years} year(s)",
                'old_values' => null,
                'new_values' => ['count' => $count, 'cutoff_date' => $date->toDateTimeString()],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            
            return back()->with('success', "Berhasil menghapus {$count} log aktivitas yang lebih tua dari {$years} tahun.");
        }

        return back()->with('info', "Tidak ada log yang lebih tua dari {$years} tahun untuk dihapus.");
    }
}
