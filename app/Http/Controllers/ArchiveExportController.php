<?php

namespace App\Http\Controllers;

use App\Models\TugasHeader;
use App\Models\KeputusanHeader;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Controller untuk export arsip ST/SK
 */
class ArchiveExportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Export Surat Tugas ke CSV
     */
    public function exportStCsv(Request $request)
    {
        $query = TugasHeader::with(['pembuat', 'penerima']);

        // Apply filters
        if ($request->filled('tahun')) {
            $query->whereYear('created_at', $request->tahun);
        }
        if ($request->filled('status')) {
            $query->where('status_surat', $request->status);
        }
        if ($request->filled('jenis')) {
            $query->where('jenis_tugas', $request->jenis);
        }

        $data = $query->orderBy('created_at', 'desc')->get();

        $filename = 'arsip_surat_tugas_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'No', 'Nomor Surat', 'Perihal', 'Jenis Tugas', 
                'Tanggal Mulai', 'Tanggal Selesai', 'Status',
                'Pembuat', 'Tanggal Dibuat', 'Penerima'
            ]);

            $no = 1;
            foreach ($data as $st) {
                $penerima = $st->penerima->pluck('nama_pegawai')->implode(', ');
                
                fputcsv($file, [
                    $no++,
                    $st->nomor_surat ?? '-',
                    $st->perihal ?? '-',
                    $st->jenis_tugas ?? '-',
                    optional($st->tanggal_mulai)->format('d/m/Y') ?? '-',
                    optional($st->tanggal_selesai)->format('d/m/Y') ?? '-',
                    ucfirst($st->status_surat ?? '-'),
                    optional($st->pembuat)->nama_lengkap ?? '-',
                    optional($st->created_at)->format('d/m/Y H:i'),
                    $penerima ?: '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Surat Keputusan ke CSV
     */
    public function exportSkCsv(Request $request)
    {
        $query = KeputusanHeader::with(['pembuat']);

        // Apply filters
        if ($request->filled('tahun')) {
            $query->whereYear('created_at', $request->tahun);
        }
        if ($request->filled('status')) {
            $query->where('status_surat', $request->status);
        }
        if ($request->filled('klasifikasi')) {
            $query->where('klasifikasi', $request->klasifikasi);
        }

        $data = $query->orderBy('created_at', 'desc')->get();

        $filename = 'arsip_surat_keputusan_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'No', 'Nomor Surat', 'Perihal', 'Klasifikasi',
                'Tanggal SK', 'Status', 'Pembuat', 'Tanggal Dibuat'
            ]);

            $no = 1;
            foreach ($data as $sk) {
                fputcsv($file, [
                    $no++,
                    $sk->nomor_surat ?? '-',
                    $sk->perihal ?? '-',
                    $sk->klasifikasi ?? '-',
                    optional($sk->tanggal_sk)->format('d/m/Y') ?? '-',
                    ucfirst($sk->status_surat ?? '-'),
                    optional($sk->pembuat)->nama_lengkap ?? '-',
                    optional($sk->created_at)->format('d/m/Y H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Surat Tugas ke Excel (XLSX via CSV)
     */
    public function exportStExcel(Request $request)
    {
        return $this->exportStCsv($request);
    }

    /**
     * Export Surat Keputusan ke Excel (XLSX via CSV)
     */
    public function exportSkExcel(Request $request)
    {
        return $this->exportSkCsv($request);
    }
}
