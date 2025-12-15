<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\RecipientImportService;

class RecipientImportController extends Controller
{
    protected RecipientImportService $importService;

    public function __construct(RecipientImportService $importService)
    {
        $this->importService = $importService;
    }

    /**
     * Show import form
     */
    public function index()
    {
        return view('surat_tugas.import_recipients');
    }

    /**
     * Process upload and return preview
     */
    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:5120',
        ]);

        try {
            $result = $this->importService->parseFile($request->file('file'));

            // Store parsed data in session for confirmation
            session(['recipient_import_data' => $result['rows']]);

            return response()->json([
                'success' => true,
                'rows' => $result['rows'],
                'errors' => $result['errors'],
                'total' => $result['total'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Confirm and return data for form insertion
     */
    public function confirm(Request $request)
    {
        $data = session('recipient_import_data', []);

        if (empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data import. Silakan upload ulang.',
            ], 422);
        }

        // Save import record
        if ($request->hasFile('file')) {
            $this->importService->saveImport(
                $request->file('file'),
                Auth::id(),
                ['rows' => $data, 'errors' => [], 'total' => count($data)]
            );
        }

        // Clear session
        session()->forget('recipient_import_data');

        return response()->json([
            'success' => true,
            'recipients' => $data,
        ]);
    }

    /**
     * Download template
     */
    public function downloadTemplate()
    {
        $path = $this->importService->generateTemplate();

        return response()->download($path)->deleteFileAfterSend(true);
    }
}
