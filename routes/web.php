<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TugasController;
use App\Http\Controllers\JenisTugasController;
use App\Http\Controllers\SubTugasController;
use App\Http\Controllers\KlasifikasiSuratController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\MasterKopSuratController;
use App\Http\Controllers\MySignatureController;
use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\SuratKeputusanController;
use App\Http\Controllers\SuratKeputusan\NomorSuratController;
use App\Http\Controllers\RedirectController;
use App\Http\Controllers\ExternalEntryController;
use App\Models\TugasHeader;
use App\Jobs\SendSuratTugasEmail;
use App\Models\KeputusanHeader;

// ❌ HAPUS: Redirect ke login (sistem eksternal yang handle)
// Route::redirect('/', '/login');

// ✅ RESTORE: Auth routes Laravel untuk login dengan email/password
// External dashboard entry (/entry?user_id=X) tetap berfungsi untuk integrasi
Auth::routes(['register' => false, 'reset' => false, 'verify' => false]);

// ❌ DISABLE: Login redirect ke external entry (sudah ada Auth::routes di atas)
// ✅ FIX: Define 'login' route name to prevent RouteNotFoundException
// Route::get('/login', function () {
//     return redirect()->route('external.entry');
// })->name('login');

// ✅ TAMBAH: Entry point dari Dashboard Menu eksternal
Route::get('/entry', [ExternalEntryController::class, 'entry'])->name('external.entry');

// ✅ TAMBAH: Exit point kembali ke Dashboard Menu
Route::post('/exit', [ExternalEntryController::class, 'exit'])->name('external.exit');

// ✅ TAMBAH: Landing page redirect ke /home
Route::get('/', function () {
    return redirect()->route('home');
})->name('landing');

// ✅ REVISI: Ganti middleware 'auth' jadi 'check.session.role'
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])
    ->name('home')
    ->middleware('check.session.role');

// ✅ REVISI: Ganti middleware 'auth' jadi 'check.session.role'
Route::middleware('check.session.role')->group(function () {
    // 1) Users & Roles
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);

    // 2) Jenis Surat Tugas
    Route::resource('jenis_surat_tugas', JenisTugasController::class)->except(['show']);

    // 3) Sub Tugas (nested)
    Route::prefix('jenis_surat_tugas/{jenistugas}')
        ->name('sub_tugas.')
        ->group(function () {
            Route::get('sub_tugas', [SubTugasController::class, 'index'])->name('index');
            Route::post('sub_tugas', [SubTugasController::class, 'store'])->name('store');
            Route::put('sub_tugas/{subtugas}', [SubTugasController::class, 'update'])->name('update');
            Route::delete('sub_tugas/{subtugas}', [SubTugasController::class, 'destroy'])->name('destroy');
        });

    // 4) CRUD Klasifikasi Surat
    Route::prefix('klasifikasi_surat')
        ->name('klasifikasi_surat.')
        ->group(function () {
            Route::get('/', [KlasifikasiSuratController::class, 'index'])->name('index');
            Route::post('/', [KlasifikasiSuratController::class, 'store'])->name('store');
            Route::put('/{klasifikasi_surat}', [KlasifikasiSuratController::class, 'update'])->name('update');
            Route::delete('/{klasifikasi_surat}', [KlasifikasiSuratController::class, 'destroy'])->name('destroy');

            // AJAX Routes
            Route::post('/ajax/next-code', [KlasifikasiSuratController::class, 'getNextCode'])->name('nextCode');
            Route::post('/ajax/golongan', [KlasifikasiSuratController::class, 'getAvailableGolongan'])->name('getGolongan');
        });

    // 5) Nomor Surat AJAX
    Route::post('/ajax/nomor-surat/reserve', [NomorSuratController::class, 'reserve'])
        ->name('ajax.nomor.reserve')
        ->middleware(['throttle:20,1']);

    // 6) Surat Tugas Routes
    Route::prefix('surat_tugas')
        ->name('surat_tugas.')
        ->group(function () {
            Route::get('/', [TugasController::class, 'mine'])->name('index');
            Route::get('create', [TugasController::class, 'create'])->name('create');
            Route::post('/', [TugasController::class, 'store'])->name('store');
            Route::get('tugas_saya', [TugasController::class, 'mine'])->name('mine');
            Route::get('semua', [TugasController::class, 'all'])->name('all');
            
            // Arsip: khusus Admin TU (peran_id 1)
            Route::get('arsip', [TugasController::class, 'arsipList'])
                ->name('arsipList')
                ->middleware('can:viewAny,App\Models\TugasHeader');

            Route::get('approve-list', [TugasController::class, 'approveList'])
                ->name('approveList')
                ->middleware('can:viewApproveList,App\Models\TugasHeader');

            Route::get('approve', [RedirectController::class, 'toApproveListSt'])
                ->name('approveRedirect')
                ->middleware('can:viewApproveList,App\Models\TugasHeader');

            // ✅ Show approve page
            Route::get('{tugas}/approve', [TugasController::class, 'showApproveForm'])
                ->name('approve.form')
                ->whereNumber('tugas')
                ->middleware('can:approve,tugas');

            // ✅ Action approve
            Route::match(['post', 'patch'], '{tugas}/approve', [TugasController::class, 'approve'])
                ->name('approve')
                ->whereNumber('tugas')
                ->middleware('can:approve,tugas');

            // ✅ Legacy
            Route::get('{tugas}/review-approve', [TugasController::class, 'approveForm'])
                ->name('approveForm')
                ->whereNumber('tugas')
                ->middleware('can:approve,tugas');

            Route::get('{tugas}/approve/preview', [TugasController::class, 'approvePreview'])
                ->name('approvePreview')
                ->whereNumber('tugas')
                ->middleware('can:approve,tugas');

            Route::get('{tugas}/highlight', [TugasController::class, 'highlight'])
                ->name('highlight')
                ->whereNumber('tugas')
                ->middleware('can:view,tugas');

            // ✅ UNARCHIVE ACTION
            Route::post('{tugas}/buka-arsip', [TugasController::class, 'bukaArsip'])
                ->name('buka-arsip')
                ->whereNumber('tugas');

            Route::get('{tugas}/download-pdf', [TugasController::class, 'downloadPdf'])
                ->name('downloadPdf')
                ->whereNumber('tugas')
                ->middleware('can:view,tugas');

            Route::post('{tugas}/arsipkan', [TugasController::class, 'arsipkan'])
                ->name('arsipkan')
                ->whereNumber('tugas');

            Route::get('{tugas}/preview', [TugasController::class, 'preview'])
                ->name('preview')
                ->whereNumber('tugas')
                ->middleware('can:view,tugas');

            // 🔹 Halaman Tersusun Detail (baru)
            Route::get('{tugas}/detail', [TugasController::class, 'editDetail'])
                ->name('detail.edit')
                ->whereNumber('tugas')
                ->middleware('can:update,tugas');

            Route::put('{tugas}/detail', [TugasController::class, 'updateDetail'])
                ->name('detail.update')
                ->whereNumber('tugas')
                ->middleware('can:update,tugas');
            // 🔹 akhir detail
    
            Route::post('{tugas}/penerima', [TugasController::class, 'addRecipient'])
                ->name('recipient.add')
                ->middleware('can:addRecipient,tugas')
                ->whereNumber('tugas');

            // ✅ TAMBAH: Route khusus submit dari detail (bypass edit form)
            Route::post('{tugas}/submit', [TugasController::class, 'submit'])
                ->name('submit')
                ->middleware('can:update,tugas')
                ->whereNumber('tugas');

            Route::get('{tugas}', [TugasController::class, 'show'])
                ->whereNumber('tugas')
                ->name('show');

            Route::get('{tugas}/edit', [TugasController::class, 'edit'])
                ->name('edit')
                ->middleware('can:update,tugas')
                ->whereNumber('tugas');

            Route::put('{tugas}', [TugasController::class, 'update'])
                ->name('update')
                ->middleware('can:update,tugas')
                ->whereNumber('tugas');

            Route::delete('{tugas}', [TugasController::class, 'destroy'])
                ->name('destroy')
                ->middleware('can:delete,tugas')
                ->whereNumber('tugas');
        });

    // 6b) AJAX: Nomor Turunan (Suffix) - untuk ST
    Route::get('/ajax/surat-tugas/{tugas}/next-suffix', function (\App\Models\TugasHeader $tugas) {
        $service = app(\App\Services\NomorSuratService::class);

        // Load relasi yang dibutuhkan untuk auto-fill
        $tugas->load(['penerima.pengguna', 'klasifikasiSurat']);

        try {
            return response()->json([
                'suffix' => $service->getNextSuffix($tugas->id),
                'nomor_preview' => $service->previewSuffixNomor($tugas->id),
                'parent_nomor' => $tugas->nomor,
                // Data untuk auto-fill form
                'parent_data' => [
                    'jenis_tugas' => $tugas->jenis_tugas,
                    'tugas' => $tugas->tugas,
                    'detail_tugas' => $tugas->detail_tugas,
                    'status_penerima' => $tugas->status_penerima,
                    'redaksi_pembuka' => $tugas->redaksi_pembuka,
                    'penutup' => $tugas->penutup,
                    'waktu_mulai' => $tugas->waktu_mulai?->format('Y-m-d\TH:i'),
                    'waktu_selesai' => $tugas->waktu_selesai?->format('Y-m-d\TH:i'),
                    'tempat' => $tugas->tempat,
                    'tembusan' => $tugas->tembusan,
                    'klasifikasi_surat_id' => $tugas->klasifikasi_surat_id,
                    'klasifikasi_kode' => optional($tugas->klasifikasiSurat)->kode ?? '',
                    'klasifikasi_label' => optional($tugas->klasifikasiSurat)->kode ? optional($tugas->klasifikasiSurat)->kode . ' - ' . (optional($tugas->klasifikasiSurat)->deskripsi ?? (optional($tugas->klasifikasiSurat)->nama ?? '')) : '',
                    'nama_umum' => $tugas->nama_umum,
                    'asal_surat_id' => $tugas->asal_surat_id ?? $tugas->asal_surat,
                    'penandatangan_id' => $tugas->penandatangan_id ?? $tugas->penandatangan,
                    'penerima_internal' => $tugas->penerima->where('pengguna_id', '!=', null)->pluck('pengguna_id')->toArray(),
                    'penerima_eksternal' => $tugas->penerima
                        ->where('pengguna_id', null)
                        ->map(function ($p) {
                            return [
                                'nama' => $p->nama_penerima,
                                'jabatan' => $p->jabatan_penerima,
                                'instansi' => $p->instansi,
                            ];
                        })
                        ->values()
                        ->toArray(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    })
        ->name('ajax.surat_tugas.nextSuffix')
        ->whereNumber('tugas');

    Route::get('/surat-tugas/{any?}', [RedirectController::class, 'legacySt'])->where('any', '.*');

    // 7) Account Settings & Notifications
    Route::prefix('pengaturan')
        ->name('account.')
        ->group(function () {
            Route::get('akun', [AccountSettingsController::class, 'edit'])->name('settings');
            Route::put('akun/profile', [AccountSettingsController::class, 'updateProfile'])->name('updateProfile');
            Route::put('akun/password', [AccountSettingsController::class, 'updatePassword'])->name('updatePassword');
        });

    Route::get('notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::patch('notifikasi/{id}/read', [NotifikasiController::class, 'markAsRead'])
        ->name('notifikasi.read')
        ->whereNumber('id');
    Route::post('notifikasi/prune', [NotifikasiController::class, 'prune'])->name('notifikasi.prune');
    Route::patch('notifikasi/mark-all-read', [NotifikasiController::class, 'markAllAsRead'])->name('notifikasi.markAllRead');

    // 8) Surat Keputusan (SK)
    Route::prefix('surat_keputusan')
        ->name('surat_keputusan.')
        ->group(function () {
            Route::get('/', [SuratKeputusanController::class, 'index'])->name('index');

            Route::get('approve-list', [SuratKeputusanController::class, 'approveList'])
                ->name('approveList')
                ->middleware('can:viewAny,App\Models\KeputusanHeader');

            Route::get('approve', [RedirectController::class, 'toApproveListSk'])
                ->name('approveRedirect')
                ->middleware('can:viewAny,App\Models\KeputusanHeader');

            Route::get('saya', [SuratKeputusanController::class, 'mine'])->name('mine');

            // Semua user boleh lihat SK Terbit (dikontrol di policy jika mau dibatasi)
            Route::get('terbit', [SuratKeputusanController::class, 'terbitList'])
                ->name('terbitList')
                ->middleware('can:viewAny,App\Models\KeputusanHeader');

            // Arsip: khusus Admin TU (peran_id 1) via ability viewArchive
            Route::get('arsip', [SuratKeputusanController::class, 'arsipList'])
                ->name('arsipList')
                ->middleware('can:viewArchive,App\Models\KeputusanHeader');

            Route::get('/create', [SuratKeputusanController::class, 'create'])
                ->name('create')
                ->middleware('can:create,' . KeputusanHeader::class);

            Route::post('/', [SuratKeputusanController::class, 'store'])
                ->name('store')
                ->middleware('can:create,' . KeputusanHeader::class);

            // ✅ PERBAIKAN: Hapus middleware authorization dari route edit & update
            // Biarkan controller yang handle authorization
            Route::get('/{surat_keputusan}/edit', [SuratKeputusanController::class, 'edit'])
                ->name('edit')
                ->whereNumber('surat_keputusan');

            Route::put('/{surat_keputusan}', [SuratKeputusanController::class, 'update'])
                ->name('update')
                ->whereNumber('surat_keputusan');

            Route::delete('/{surat_keputusan}', [SuratKeputusanController::class, 'destroy'])
                ->name('destroy')
                ->whereNumber('surat_keputusan')
                ->middleware('can:delete,surat_keputusan');

            Route::post('/{surat_keputusan}/submit', [SuratKeputusanController::class, 'submit'])
                ->name('submit')
                ->whereNumber('surat_keputusan')
                ->middleware('can:submit,surat_keputusan');

            Route::post('/{surat_keputusan}/approve', [SuratKeputusanController::class, 'approve'])
                ->name('approve')
                ->whereNumber('surat_keputusan')
                ->middleware('can:approve,surat_keputusan');

            Route::post('/{surat_keputusan}/reject', [SuratKeputusanController::class, 'reject'])
                ->name('reject')
                ->whereNumber('surat_keputusan')
                ->middleware('can:reject,surat_keputusan');

            Route::post('/{surat_keputusan}/reopen', [SuratKeputusanController::class, 'reopen'])
                ->name('reopen')
                ->whereNumber('surat_keputusan')
                ->middleware('can:reopen,surat_keputusan');

            Route::post('{surat_keputusan}/terbitkan', [SuratKeputusanController::class, 'terbitkan'])
                ->name('terbitkan')
                ->whereNumber('surat_keputusan');

            Route::post('{surat_keputusan}/arsipkan', [SuratKeputusanController::class, 'arsipkan'])
                ->name('arsipkan')
                ->whereNumber('surat_keputusan')
                ->middleware('can:archive,surat_keputusan');

            Route::post('{surat_keputusan}/batal-terbitkan', [SuratKeputusanController::class, 'batalTerbitkan'])
                ->name('batal_terbitkan')
                ->whereNumber('surat_keputusan')
                ->middleware('can:unpublish,surat_keputusan');

            Route::get('/{surat_keputusan}/approve-form', [SuratKeputusanController::class, 'approveForm'])
                ->name('approveForm')
                ->whereNumber('surat_keputusan')
                ->middleware('can:approve,surat_keputusan');

            Route::get('/{surat_keputusan}/approve-preview', [SuratKeputusanController::class, 'approvePreview'])
                ->name('approvePreview')
                ->whereNumber('surat_keputusan')
                ->middleware('can:approve,surat_keputusan');

            Route::get('/{surat_keputusan}/preview', [SuratKeputusanController::class, 'preview'])
                ->name('preview')
                ->whereNumber('surat_keputusan')
                ->middleware('can:view,surat_keputusan');

            Route::get('/{surat_keputusan}/download', [SuratKeputusanController::class, 'downloadPdf'])
                ->name('downloadPdf')
                ->whereNumber('surat_keputusan')
                ->middleware('can:view,surat_keputusan');

            Route::get('/{surat_keputusan}', [SuratKeputusanController::class, 'show'])
                ->name('show')
                ->whereNumber('surat_keputusan')
                ->middleware('can:view,surat_keputusan');
            
            // ✅ UNARCHIVE ACTION
            Route::post('{surat_keputusan}/buka-arsip', [SuratKeputusanController::class, 'bukaArsip'])
                ->name('buka-arsip')
                ->whereNumber('surat_keputusan');

            // ✅ FASE 1.2: Lampiran file routes (NESTED RESOURCE)
            Route::prefix('{surat_keputusan}')
                ->whereNumber('surat_keputusan')
                ->group(function () {
                // ✅ PERBAIKAN: Hapus middleware dari upload & delete attachment
                Route::post('/attachments', [SuratKeputusanController::class, 'uploadAttachment'])->name('attachments.upload');

                Route::get('/attachments/{attachment}', [SuratKeputusanController::class, 'downloadAttachment'])
                    ->name('attachments.download')
                    ->whereNumber('attachment')
                    ->middleware('can:view,surat_keputusan');

                Route::delete('/attachments/{attachment}', [SuratKeputusanController::class, 'deleteAttachment'])
                    ->name('attachments.delete')
                    ->whereNumber('attachment');
            });
        });

    Route::get('/surat-keputusan/{any?}', [RedirectController::class, 'legacySk'])->where('any', '.*');

    // NOTE: Kop Surat routes dipindahkan ke section 24 (Kop Surat Settings)

    Route::get('/kop-surat/ttd-saya', [MySignatureController::class, 'edit'])->name('kop.ttd.edit');
    Route::post('/kop-surat/ttd-saya', [MySignatureController::class, 'update'])->name('kop.ttd.update');
    Route::delete('/kop-surat/ttd-saya', [MySignatureController::class, 'destroy'])->name('kop.ttd.destroy');
    Route::post('/kop-surat/ttd-saya/preview', [MySignatureController::class, 'preview'])->name('kop.ttd.preview');

    // 10) Dev Helper
    Route::get('/dev/send-surat/{id}', function ($id) {
        SendSuratTugasEmail::dispatch((int) $id, 'to_recipients');
        return "Job dikirim untuk surat ID {$id}. Cek inbox (atau MailHog).";
    })->whereNumber('id');

    // ==================== PHASE 1 ROUTES ====================

    // 11) Template Surat Tugas
    Route::resource('surat_templates', \App\Http\Controllers\SuratTemplateController::class);
    Route::post('/surat_templates/{surat_template}/duplicate', [\App\Http\Controllers\SuratTemplateController::class, 'duplicate'])->name('surat_templates.duplicate');
    Route::get('/ajax/surat_templates/{id}', [\App\Http\Controllers\SuratTemplateController::class, 'getTemplate'])
        ->name('ajax.template.get')
        ->whereNumber('id');

    // 12) Audit Logs (Admin only)
    Route::get('/pengaturan/audit-logs', [\App\Http\Controllers\AuditLogController::class, 'index'])->name('audit_logs.index');
    Route::get('/pengaturan/audit-logs/export', [\App\Http\Controllers\AuditLogController::class, 'export'])->name('audit_logs.export');
    Route::post('/pengaturan/audit-logs/prune', [\App\Http\Controllers\AuditLogController::class, 'prune'])->name('audit_logs.prune');
    Route::get('/pengaturan/audit-logs/{audit_log}', [\App\Http\Controllers\AuditLogController::class, 'show'])->name('audit_logs.show');
    Route::get('/ajax/audit-logs/entity', [\App\Http\Controllers\AuditLogController::class, 'forEntity'])->name('ajax.audit.entity');

    // ==================== PHASE 2 & 3 ROUTES ====================

    // 13) Menimbang Library (SK)
    Route::prefix('pengaturan/menimbang-library')
        ->name('menimbang_library.')
        ->group(function () {
            Route::get('/', [\App\Http\Controllers\MenimbangLibraryController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\MenimbangLibraryController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\MenimbangLibraryController::class, 'store'])->name('store');
            Route::get('/{menimbangLibrary}/edit', [\App\Http\Controllers\MenimbangLibraryController::class, 'edit'])->name('edit');
            Route::put('/{menimbangLibrary}', [\App\Http\Controllers\MenimbangLibraryController::class, 'update'])->name('update');
            Route::delete('/{menimbangLibrary}', [\App\Http\Controllers\MenimbangLibraryController::class, 'destroy'])->name('destroy');
        });
    Route::get('/ajax/menimbang-library/search', [\App\Http\Controllers\MenimbangLibraryController::class, 'search'])->name('ajax.menimbang.search');
    Route::post('/ajax/menimbang-library/{menimbangLibrary}/usage', [\App\Http\Controllers\MenimbangLibraryController::class, 'incrementUsage'])->name('ajax.menimbang.usage');

    // 14) Mengingat Library (SK)
    Route::prefix('pengaturan/mengingat-library')
        ->name('mengingat_library.')
        ->group(function () {
            Route::get('/', [\App\Http\Controllers\MengingatLibraryController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\MengingatLibraryController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\MengingatLibraryController::class, 'store'])->name('store');
            Route::get('/{mengingatLibrary}/edit', [\App\Http\Controllers\MengingatLibraryController::class, 'edit'])->name('edit');
            Route::put('/{mengingatLibrary}', [\App\Http\Controllers\MengingatLibraryController::class, 'update'])->name('update');
            Route::delete('/{mengingatLibrary}', [\App\Http\Controllers\MengingatLibraryController::class, 'destroy'])->name('destroy');
        });
    Route::get('/ajax/mengingat-library/search', [\App\Http\Controllers\MengingatLibraryController::class, 'search'])->name('ajax.mengingat.search');
    Route::get('/ajax/mengingat-library/categories', [\App\Http\Controllers\MengingatLibraryController::class, 'categories'])->name('ajax.mengingat.categories');
    Route::post('/ajax/mengingat-library/{mengingatLibrary}/usage', [\App\Http\Controllers\MengingatLibraryController::class, 'incrementUsage'])->name('ajax.mengingat.usage');

    // 15) Duplicate SK Feature
    Route::post('/surat_keputusan/{surat_keputusan}/duplicate', [\App\Http\Controllers\SuratKeputusanController::class, 'duplicate'])->name('surat_keputusan.duplicate');

    // 16) Reports Dashboard
    Route::prefix('laporan')
        ->name('laporan.')
        ->group(function () {
            Route::get('/', [\App\Http\Controllers\ReportController::class, 'dashboard'])->name('dashboard');
            Route::get('/export/excel', [\App\Http\Controllers\ReportController::class, 'exportExcel'])->name('export.excel');
            Route::get('/export/pdf', [\App\Http\Controllers\ReportController::class, 'exportPdf'])->name('export.pdf');
        });

    // NOTE: Audit Logs sudah didefinisikan di section 12 dengan nama 'audit_logs.index'

    // 18) Recipient Import (ST) - dipindahkan ke section 23 sebagai 'import.penerima.*'

    // 19) Signature Capture
    Route::prefix('signature')
        ->name('signature.')
        ->group(function () {
            Route::get('/', [\App\Http\Controllers\SignatureController::class, 'edit'])->name('edit');
            Route::put('/', [\App\Http\Controllers\SignatureController::class, 'update'])->name('update');
            Route::delete('/', [\App\Http\Controllers\SignatureController::class, 'destroy'])->name('destroy');
            Route::get('/preview', [\App\Http\Controllers\SignatureController::class, 'preview'])->name('preview');
        });

    // 20) Notification Preferences
    Route::prefix('pengaturan/notifikasi')
        ->name('notification_preferences.')
        ->group(function () {
            Route::get('/', [\App\Http\Controllers\NotificationPreferenceController::class, 'edit'])->name('edit');
            Route::put('/', [\App\Http\Controllers\NotificationPreferenceController::class, 'update'])->name('update');
        });

    // 22) Archive Export (ST & SK)
    Route::prefix('arsip/export')
        ->name('arsip_export.')
        ->group(function () {
            Route::get('/surat-tugas/csv', [\App\Http\Controllers\ArchiveExportController::class, 'exportStCsv'])->name('st.csv');
            Route::get('/surat-tugas/excel', [\App\Http\Controllers\ArchiveExportController::class, 'exportStExcel'])->name('st.excel');
            Route::get('/surat-keputusan/csv', [\App\Http\Controllers\ArchiveExportController::class, 'exportSkCsv'])->name('sk.csv');
            Route::get('/surat-keputusan/excel', [\App\Http\Controllers\ArchiveExportController::class, 'exportSkExcel'])->name('sk.excel');
        });

    // 23) Recipient Import (Bulk Import Penerima ST)
    Route::prefix('import-penerima')
        ->name('import.penerima.')
        ->group(function () {
            Route::get('/', [\App\Http\Controllers\RecipientImportController::class, 'index'])->name('index');
            Route::post('/preview', [\App\Http\Controllers\RecipientImportController::class, 'preview'])->name('preview');
            Route::post('/confirm', [\App\Http\Controllers\RecipientImportController::class, 'confirm'])->name('confirm');
            Route::get('/template', [\App\Http\Controllers\RecipientImportController::class, 'downloadTemplate'])->name('template');
        });

    // 24) Kop Surat Settings (Legacy Singleton)
    Route::middleware(['check.session.role', 'can:manage-kop-surat'])->group(function () {
        // Specific routes FIRST (before generic routes)
        Route::post('/pengaturan/kop-surat/preview', [MasterKopSuratController::class, 'preview'])->name('kop.preview');
        Route::delete('/pengaturan/kop-surat/delete-image/{type}', [MasterKopSuratController::class, 'deleteImage'])->name('kop.delete-image');
        Route::get('/pengaturan/kop-surat/presets', [MasterKopSuratController::class, 'getPresets'])->name('kop.presets');
        Route::post('/pengaturan/kop-surat/apply-preset', [MasterKopSuratController::class, 'applyPreset'])->name('kop.apply-preset');
        Route::get('/pengaturan/kop-surat/export', [MasterKopSuratController::class, 'export'])->name('kop.export');
        Route::post('/pengaturan/kop-surat/import', [MasterKopSuratController::class, 'import'])->name('kop.import');

        // Generic routes LAST
        Route::get('/pengaturan/kop-surat', [MasterKopSuratController::class, 'index'])->name('kop.index');
        Route::put('/pengaturan/kop-surat', [MasterKopSuratController::class, 'update'])->name('kop.update');
    });
});
