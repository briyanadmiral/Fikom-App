<?php

namespace App\Http\Controllers;

use App\Models\KeputusanHeader;
use App\Models\Notifikasi;
use App\Models\TugasHeader;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $tahun = $request->input('tahun', now()->year);
        $bulan = $request->input('period_month', now()->month); // Default current month
        $periodType = $request->input('period_type', 'month'); // month or year

        // FILTER DATE RANGE
        $startDate = $periodType == 'month'
            ? Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth()
            : Carbon::createFromDate($tahun, 1, 1)->startOfYear();
        $endDate = $periodType == 'month'
            ? Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()
            : Carbon::createFromDate($tahun, 12, 31)->endOfYear();

        // --- 1. KPI CARDS DATA (Filtered by Period) ---
        $kpi = [
            'total_surat' => $this->countSurat($startDate, $endDate),
            'total_st' => TugasHeader::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_sk' => KeputusanHeader::whereBetween('created_at', [$startDate, $endDate])->count(),
            'waiting_review' => $this->countStatus(['pending'], $startDate, $endDate),
            'waiting_sign' => $this->countStatus(['disetujui'], $startDate, $endDate, true),
            'final' => $this->countStatus(['disetujui'], $startDate, $endDate),
        ];

        $bulanIni = now()->month;
        $stats = [
            'st_bulan_ini' => TugasHeader::whereYear('created_at', $tahun)
                ->whereMonth('created_at', $bulanIni)
                ->count(),
            'sk_bulan_ini' => KeputusanHeader::whereYear('created_at', $tahun)
                ->whereMonth('created_at', $bulanIni)
                ->count(),
        ];

        // --- 2. WORK QUEUE (Operational - User Centric) ---
        // A. Perlu Action (Review/Approve) -> ONLY FOR APPROVERS (Dekan/Wakil)
        $perluAction = collect([]);
        if ($user->canApproveSurat()) {
            // FIX: Hanya tampilkan surat pending yang ditujukan ke user ini
            $stAction = TugasHeader::with('pembuat')
                ->where('status_surat', 'pending')
                ->where('next_approver', $user->id)
                ->latest()
                ->take(5)
                ->get();
            $skAction = KeputusanHeader::with('pembuat')
                ->where('status_surat', 'pending')
                ->where('penandatangan', $user->id)
                ->latest()
                ->take(5)
                ->get();
            $perluAction = $stAction->map(function ($i) {
                $i->jenis = 'ST';
                $i->display_title = $i->nama_umum ?? $i->tugas ?? '-';

                return $i;
            })
                ->merge($skAction->map(function ($i) {
                    $i->jenis = 'SK';
                    $i->display_title = $i->tentang ?? 'Tanpa Judul';

                    return $i;
                }))
                ->sortByDesc('updated_at')->take(10);
        }

        // B. Draft Saya (For Everyone)
        $myDrafts = TugasHeader::where('dibuat_oleh', $user->id)->whereIn('status_surat', ['draft'])->latest()->take(5)->get()
            ->map(function ($i) {
                $i->jenis = 'ST';
                $i->display_title = $i->nama_umum ?? $i->tugas ?? '-';

                return $i;
            })
            ->merge(KeputusanHeader::where('dibuat_oleh', $user->id)->whereIn('status_surat', ['draft'])->latest()->take(5)->get()->map(function ($i) {
                $i->jenis = 'SK';
                $i->display_title = $i->tentang ?? 'Tanpa Judul';

                return $i;
            }))
            ->sortByDesc('updated_at')->take(10);

        // C. Dikembalikan / Revisi (For Everyone)
        $myRevisions = TugasHeader::where('dibuat_oleh', $user->id)->whereIn('status_surat', ['ditolak', 'revisi'])->latest()->take(5)->get()
            ->map(function ($i) {
                $i->jenis = 'ST';
                $i->display_title = $i->nama_umum ?? $i->tugas ?? '-';

                return $i;
            })
            ->merge(KeputusanHeader::where('dibuat_oleh', $user->id)->whereIn('status_surat', ['ditolak', 'revisi'])->latest()->take(5)->get()->map(function ($i) {
                $i->jenis = 'SK';
                $i->display_title = $i->tentang ?? 'Tanpa Judul';

                return $i;
            }))
            ->sortByDesc('updated_at')->take(10);

        // Notifikasi
        $notifications = Notifikasi::where('pengguna_id', $user->id)->latest()->take(8)->get();

        // --- 3. ACTIVE USERS (Mandatory Feature) ---
        // Count users active in last 5 minutes
        $activeUsersCount = \App\Models\User::where('last_activity', '>=', now()->subMinutes(5))->count();
        $onlineUsers = \App\Models\User::where('last_activity', '>=', now()->subMinutes(5))
            ->orderBy('last_activity', 'desc')
            ->with('peran') // Eager load role
            ->take(20)->get();

        // --- 4. CHARTS & GRAPHS ---
        // Trend 12 Months (ST vs SK) — optimized: 2 queries instead of 24
        $months = [];
        $trendST = array_fill(0, 12, 0);
        $trendSK = array_fill(0, 12, 0);

        for ($i = 1; $i <= 12; $i++) {
            $months[] = Carbon::createFromDate($tahun, $i, 1)->format('M');
        }

        // Single query for ST monthly counts
        $stMonthly = TugasHeader::selectRaw('MONTH(created_at) as bulan, COUNT(*) as total')
            ->whereYear('created_at', $tahun)
            ->groupByRaw('MONTH(created_at)')
            ->pluck('total', 'bulan');

        // Single query for SK monthly counts
        $skMonthly = KeputusanHeader::selectRaw('MONTH(created_at) as bulan, COUNT(*) as total')
            ->whereYear('created_at', $tahun)
            ->groupByRaw('MONTH(created_at)')
            ->pluck('total', 'bulan');

        for ($i = 1; $i <= 12; $i++) {
            $trendST[$i - 1] = $stMonthly[$i] ?? 0;
            $trendSK[$i - 1] = $skMonthly[$i] ?? 0;
        }

        // Status Breakdown (Donut) — optimized: 2 queries instead of 8
        $statuses = ['draft', 'pending', 'disetujui', 'ditolak'];

        $stStatusCounts = TugasHeader::selectRaw('status_surat, COUNT(*) as total')
            ->whereYear('created_at', $tahun)
            ->whereIn('status_surat', $statuses)
            ->groupBy('status_surat')
            ->pluck('total', 'status_surat');

        $skStatusCounts = KeputusanHeader::selectRaw('status_surat, COUNT(*) as total')
            ->whereYear('created_at', $tahun)
            ->whereIn('status_surat', $statuses)
            ->groupBy('status_surat')
            ->pluck('total', 'status_surat');

        $statusBreakdown = [];
        foreach ($statuses as $s) {
            $statusBreakdown[] = ($stStatusCounts[$s] ?? 0) + ($skStatusCounts[$s] ?? 0);
        }

        // --- 5. MONITORING & ARCHIVES ---
        $lastST = TugasHeader::whereNotNull('nomor')->latest('created_at')->first();
        $lastSK = KeputusanHeader::whereNotNull('nomor')->latest('created_at')->first();

        $recentFinal = TugasHeader::whereNotNull('nomor')->latest('updated_at')->take(5)->get()
            ->map(function ($i) {
                $i->jenis = 'ST';
                $i->display_title = $i->nama_umum ?? $i->tugas ?? '-';

                return $i;
            })
            ->merge(KeputusanHeader::whereNotNull('nomor')->latest('updated_at')->take(5)->get()->map(function ($i) {
                $i->jenis = 'SK';
                $i->display_title = $i->tentang ?? 'Tanpa Judul';

                return $i;
            }))
            ->sortByDesc('updated_at')->take(6);

        // VIEW
        return view('home', compact(
            'user', 'tahun', 'bulan', 'periodType',
            'kpi', 'stats',
            'perluAction', 'myDrafts', 'myRevisions', 'notifications',
            'months', 'trendST', 'trendSK', 'statuses', 'statusBreakdown',
            'lastST', 'lastSK', 'recentFinal', 'activeUsersCount', 'onlineUsers'
        ));
    }

    private function countSurat($start, $end)
    {
        return TugasHeader::whereBetween('created_at', [$start, $end])->count() +
               KeputusanHeader::whereBetween('created_at', [$start, $end])->count();
    }

    private function countStatus($statuses, $start, $end, $nullNomor = false)
    {
        $q1 = TugasHeader::whereBetween('created_at', [$start, $end])->whereIn('status_surat', $statuses);
        $q2 = KeputusanHeader::whereBetween('created_at', [$start, $end])->whereIn('status_surat', $statuses);

        if ($nullNomor) {
            $q1->whereNull('nomor');
            $q2->whereNull('nomor');
        }

        return $q1->count() + $q2->count();
    }

    /**
     * Export ke Excel (CSV format) - Migrasi dari ReportController
     */
    public function exportExcel(Request $request)
    {
        $tahun = $request->input('tahun', now()->year);
        $type = $request->input('type', 'all'); // 'st', 'sk', or 'all'

        $filename = "laporan_surat_{$tahun}_".date('Ymd_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($tahun, $type) {
            $file = fopen('php://output', 'w');

            // BOM for Excel UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Export Surat Tugas
            if ($type === 'all' || $type === 'st') {
                fputcsv($file, ['=== SURAT TUGAS ===']);
                fputcsv($file, ['No', 'Nomor Surat', 'Perihal', 'Jenis', 'Status', 'Tanggal Dibuat', 'Pembuat']);

                $stList = TugasHeader::with('pembuat')
                    ->whereYear('created_at', $tahun)
                    ->orderBy('created_at', 'desc')
                    ->get();

                $no = 1;
                foreach ($stList as $st) {
                    fputcsv($file, [
                        $no++,
                        $st->nomor_surat ?? '-',
                        $st->perihal ?? $st->nama_umum ?? '-',
                        $st->jenis_tugas ?? 'Surat Tugas',
                        ucfirst($st->status_surat ?? '-'),
                        optional($st->created_at)->format('Y-m-d H:i'),
                        optional($st->pembuat)->nama_lengkap ?? '-',
                    ]);
                }
                fputcsv($file, []); // Empty row
            }

            // Export Surat Keputusan
            if ($type === 'all' || $type === 'sk') {
                fputcsv($file, ['=== SURAT KEPUTUSAN ===']);
                fputcsv($file, ['No', 'Nomor Surat', 'Perihal', 'Klasifikasi', 'Status', 'Tanggal Dibuat', 'Pembuat']);

                $skList = KeputusanHeader::with('pembuat')
                    ->whereYear('created_at', $tahun)
                    ->orderBy('created_at', 'desc')
                    ->get();

                $no = 1;
                foreach ($skList as $sk) {
                    fputcsv($file, [
                        $no++,
                        $sk->nomor_surat ?? '-',
                        $sk->perihal ?? $sk->tentang ?? '-',
                        $sk->klasifikasi ?? '-',
                        ucfirst($sk->status_surat ?? '-'),
                        optional($sk->created_at)->format('Y-m-d H:i'),
                        optional($sk->pembuat)->nama_lengkap ?? '-',
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export ke PDF - Migrasi dari ReportController
     */
    public function exportPdf(Request $request)
    {
        $tahun = $request->input('tahun', now()->year);

        // Gather data logic (same as index)
        $startDate = Carbon::createFromDate($tahun, 1, 1)->startOfYear();
        $endDate = Carbon::createFromDate($tahun, 12, 31)->endOfYear();

        $stats = [
            'total_st' => TugasHeader::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_sk' => KeputusanHeader::whereBetween('created_at', [$startDate, $endDate])->count(),
            'pending_st' => TugasHeader::where('status_surat', 'pending')->count(),
            'pending_sk' => KeputusanHeader::where('status_surat', 'pending')->count(),
            'disetujui_st' => TugasHeader::whereBetween('created_at', [$startDate, $endDate])->where('status_surat', 'disetujui')->count(),
            'disetujui_sk' => KeputusanHeader::whereBetween('created_at', [$startDate, $endDate])->where('status_surat', 'disetujui')->count(),
        ];

        // Recalculate monthly for PDF
        $monthlyData = [];
        $namaBulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyData[] = [
                'bulan' => $namaBulan[$i - 1],
                'st' => TugasHeader::whereYear('created_at', $tahun)->whereMonth('created_at', $i)->count(),
                'sk' => KeputusanHeader::whereYear('created_at', $tahun)->whereMonth('created_at', $i)->count(),
            ];
        }

        // Use the existing report PDF view
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.export_pdf', compact('stats', 'monthlyData', 'tahun'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("laporan_surat_{$tahun}.pdf");
    }
}
