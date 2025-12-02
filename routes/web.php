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
use App\Http\Controllers\ExternalEntryController; // ✅ TAMBAH: Import controller baru
use App\Models\TugasHeader;
use App\Jobs\SendSuratTugasEmail;
use App\Models\KeputusanHeader;

// ❌ HAPUS: Redirect ke login (sistem eksternal yang handle)
// Route::redirect('/', '/login');

// ❌ HAPUS: Auth routes Laravel (login/register dihandle dashboard eksternal)
// Auth::routes();

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
    ->middleware('check.session.role'); // ✅ GANTI dari implicit auth ke explicit

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

            Route::get('{tugas}/download-pdf', [TugasController::class, 'downloadPdf'])
                ->name('downloadPdf')
                ->whereNumber('tugas')
                ->middleware('can:view,tugas');

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

            // (catatan: kamu punya 2x route create sebelumnya; cukup satu ini saja kalau mau dirapikan)

            Route::post('/', [SuratKeputusanController::class, 'store'])
                ->name('store')
                ->middleware('can:create,' . KeputusanHeader::class);

            Route::get('/{surat_keputusan}/edit', [SuratKeputusanController::class, 'edit'])
                ->name('edit')
                ->whereNumber('surat_keputusan')
                ->middleware('can:update,surat_keputusan');

            Route::put('/{surat_keputusan}', [SuratKeputusanController::class, 'update'])
                ->name('update')
                ->whereNumber('surat_keputusan')
                ->middleware('can:update,surat_keputusan');

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
                ->name('batalTerbitkan')
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

            // ✅ FASE 1.2: Lampiran file routes (NESTED RESOURCE)
            Route::prefix('{surat_keputusan}')
                ->whereNumber('surat_keputusan')
                ->group(function () {
                    // Upload attachment
                    Route::post('/attachments', [SuratKeputusanController::class, 'uploadAttachment'])
                        ->name('attachments.upload')
                        ->middleware('can:update,surat_keputusan');

                    // Download attachment
                    Route::get('/attachments/{attachment}', [SuratKeputusanController::class, 'downloadAttachment'])
                        ->name('attachments.download')
                        ->whereNumber('attachment')
                        ->middleware('can:view,surat_keputusan');

                    // Delete attachment
                    Route::delete('/attachments/{attachment}', [SuratKeputusanController::class, 'deleteAttachment'])
                        ->name('attachments.delete')
                        ->whereNumber('attachment')
                        ->middleware('can:update,surat_keputusan');
                });
        });

    Route::get('/surat-keputusan/{any?}', [RedirectController::class, 'legacySk'])->where('any', '.*');

    // 9) Kop Surat & Signature
    Route::get('/pengaturan/kop-surat', [MasterKopSuratController::class, 'index'])
        ->name('kop.index')
        ->middleware('can:manage-kop-surat');
    Route::put('/pengaturan/kop-surat', [MasterKopSuratController::class, 'update'])
        ->name('kop.update')
        ->middleware('can:manage-kop-surat');
    Route::post('/pengaturan/kop-surat/preview', [MasterKopSuratController::class, 'preview'])
        ->name('kop.preview')
        ->middleware('can:manage-kop-surat');
    Route::delete('/pengaturan/kop-surat/delete-image/{type}', [MasterKopSuratController::class, 'deleteImage'])
        ->name('kop.delete-image')
        ->middleware('can:manage-kop-surat');

    Route::get('/kop-surat/ttd-saya', [MySignatureController::class, 'edit'])->name('kop.ttd.edit');
    Route::post('/kop-surat/ttd-saya', [MySignatureController::class, 'update'])->name('kop.ttd.update');
    Route::delete('/kop-surat/ttd-saya', [MySignatureController::class, 'destroy'])->name('kop.ttd.destroy');
    Route::post('/kop-surat/ttd-saya/preview', [MySignatureController::class, 'preview'])->name('kop.ttd.preview');

    // 10) Dev Helper
    Route::get('/dev/send-surat/{id}', function ($id) {
        SendSuratTugasEmail::dispatch((int) $id, 'to_recipients');
        return "Job dikirim untuk surat ID {$id}. Cek inbox (atau MailHog).";
    })->whereNumber('id');
});

// Di luar middleware group
Route::get('/test-entry', function () {
    // Simulasi Dashboard Menu set session
    session([
        'user_id' => 1, // Ganti dengan ID user yang ada di database
        'user_role' => 'admin',
    ]);

    return redirect()->route('home');
})->name('test.entry');
