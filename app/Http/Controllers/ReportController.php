<?php

namespace App\Http\Controllers;

use App\Models\TugasHeader;
use App\Models\KeputusanHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Controller untuk dashboard laporan dan analitik
 */
class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Dashboard laporan utama
     */
    public function dashboard(Request $request)
    {
        $tahun = $request->input('tahun', now()->year);
        $bulanIni = now()->month;
        
        // Summary Stats
        $stats = [
            'total_st' => TugasHeader::whereYear('created_at', $tahun)->count(),
            'total_sk' => KeputusanHeader::whereYear('created_at', $tahun)->count(),
            'st_bulan_ini' => TugasHeader::whereYear('created_at', $tahun)
                ->whereMonth('created_at', $bulanIni)->count(),
            'sk_bulan_ini' => KeputusanHeader::whereYear('created_at', $tahun)
                ->whereMonth('created_at', $bulanIni)->count(),
            'pending_st' => TugasHeader::where('status_surat', 'pending')->count(),
            'pending_sk' => KeputusanHeader::where('status_surat', 'pending')->count(),
            'pending_review' => TugasHeader::where('status_surat', 'pending')->count() +
                                KeputusanHeader::where('status_surat', 'pending')->count(),
            'disetujui_st' => TugasHeader::whereYear('created_at', $tahun)
                ->where('status_surat', 'disetujui')->count(),
            'disetujui_sk' => KeputusanHeader::whereYear('created_at', $tahun)
                ->where('status_surat', 'disetujui')->count(),
        ];
        
        // Status Distribution - ST
        $stStatusDist = TugasHeader::whereYear('created_at', $tahun)
            ->selectRaw('status_surat, COUNT(*) as jumlah')
            ->groupBy('status_surat')
            ->pluck('jumlah', 'status_surat')
            ->toArray();
            
        // Status Distribution - SK
        $skStatusDist = KeputusanHeader::whereYear('created_at', $tahun)
            ->selectRaw('status_surat, COUNT(*) as jumlah')
            ->groupBy('status_surat')
            ->pluck('jumlah', 'status_surat')
            ->toArray();
            
        // Monthly Trend ST
        $stMonthly = TugasHeader::whereYear('created_at', $tahun)
            ->selectRaw('MONTH(created_at) as bulan, COUNT(*) as jumlah')
            ->groupByRaw('MONTH(created_at)')
            ->orderByRaw('MONTH(created_at)')
            ->pluck('jumlah', 'bulan')
            ->toArray();
            
        // Monthly Trend SK
        $skMonthly = KeputusanHeader::whereYear('created_at', $tahun)
            ->selectRaw('MONTH(created_at) as bulan, COUNT(*) as jumlah')
            ->groupByRaw('MONTH(created_at)')
            ->orderByRaw('MONTH(created_at)')
            ->pluck('jumlah', 'bulan')
            ->toArray();
            
        // Fill missing months
        $stTrend = [];
        $skTrend = [];
        for ($i = 1; $i <= 12; $i++) {
            $stTrend[$i] = $stMonthly[$i] ?? 0;
            $skTrend[$i] = $skMonthly[$i] ?? 0;
        }
        
        // Available years for filter
        $years = range(now()->year - 5, now()->year);
        
        return view('reports.dashboard', compact(
            'stats', 'stStatusDist', 'skStatusDist', 
            'stTrend', 'skTrend', 'tahun', 'years'
        ));
    }

    /**
     * Export ke Excel (CSV format)
     */
    public function exportExcel(Request $request)
    {
        $tahun = $request->input('tahun', now()->year);
        $type = $request->input('type', 'all'); // 'st', 'sk', or 'all'

        $filename = "laporan_surat_{$tahun}_" . date('Ymd_His') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($tahun, $type) {
            $file = fopen('php://output', 'w');
            
            // BOM for Excel UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

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
                        $st->perihal ?? '-',
                        $st->jenis_tugas ?? '-',
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
                        $sk->perihal ?? '-',
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
     * Export ke PDF
     */
    public function exportPdf(Request $request)
    {
        $tahun = $request->input('tahun', now()->year);

        // Gather data
        $stats = [
            'total_st' => TugasHeader::whereYear('created_at', $tahun)->count(),
            'total_sk' => KeputusanHeader::whereYear('created_at', $tahun)->count(),
            'pending_st' => TugasHeader::where('status_surat', 'pending')->count(),
            'pending_sk' => KeputusanHeader::where('status_surat', 'pending')->count(),
            'disetujui_st' => TugasHeader::whereYear('created_at', $tahun)
                ->where('status_surat', 'disetujui')->count(),
            'disetujui_sk' => KeputusanHeader::whereYear('created_at', $tahun)
                ->where('status_surat', 'disetujui')->count(),
        ];

        // Monthly data
        $stMonthly = TugasHeader::whereYear('created_at', $tahun)
            ->selectRaw('MONTH(created_at) as bulan, COUNT(*) as jumlah')
            ->groupBy('bulan')
            ->pluck('jumlah', 'bulan')
            ->toArray();

        $skMonthly = KeputusanHeader::whereYear('created_at', $tahun)
            ->selectRaw('MONTH(created_at) as bulan, COUNT(*) as jumlah')
            ->groupBy('bulan')
            ->pluck('jumlah', 'bulan')
            ->toArray();

        $monthlyData = [];
        $namaBulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyData[] = [
                'bulan' => $namaBulan[$i - 1],
                'st' => $stMonthly[$i] ?? 0,
                'sk' => $skMonthly[$i] ?? 0,
            ];
        }

        $pdf = Pdf::loadView('reports.export_pdf', compact('stats', 'monthlyData', 'tahun'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("laporan_surat_{$tahun}.pdf");
    }
}

