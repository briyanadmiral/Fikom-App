<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TugasController;
use App\Http\Controllers\JenisTugasController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\MasterKopSuratController;
use App\Jobs\SendSuratTugasEmail;
// START PATCH: import controller TTD Saya
use App\Http\Controllers\MySignatureController;
// END PATCH

/*
|--------------------------------------------------------------------------
| Redirect root (‘/’) langsung ke login
|--------------------------------------------------------------------------
*/
Route::redirect('/', '/login');

/*
|--------------------------------------------------------------------------
| Routes untuk autentikasi
|--------------------------------------------------------------------------
*/
Auth::routes();

/*
|--------------------------------------------------------------------------
| Dashboard setelah login
|--------------------------------------------------------------------------
*/
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

/*
|--------------------------------------------------------------------------
| Semua route berikut wajib autentikasi
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    /*
    |----------------------------------------------------------------------
    | 1) CRUD Pengguna
    |----------------------------------------------------------------------
    */
    Route::resource('users', UserController::class);

    /*
    |----------------------------------------------------------------------
    | 2) CRUD Peran
    |----------------------------------------------------------------------
    */
    Route::resource('roles', RoleController::class);

    /*
    |----------------------------------------------------------------------
    | 3) CRUD Jenis Surat Tugas (di luar prefix surat_tugas)
    |   URL: /jenis_surat_tugas
    |----------------------------------------------------------------------
    */
    Route::resource('jenis_surat_tugas', JenisTugasController::class)->except(['show']);

    /*
    |----------------------------------------------------------------------
    | 4) Kumpulan route “Surat Tugas”
    |   Statis dulu, baru dinamis {tugas}
    |----------------------------------------------------------------------
    */
    Route::prefix('surat_tugas')
        ->name('surat_tugas.')
        ->group(function () {

            // Index → redirect ke "tugas saya"
            Route::get('/', fn() => redirect()->route('surat_tugas.mine'))->name('index');

            // ====== ROUTE STATIS ======
            // Create & Store
            Route::get('create', [TugasController::class, 'create'])->name('create');
            Route::post('/', [TugasController::class, 'store'])->name('store');

            // Listing
            Route::get('tugas_saya', [TugasController::class, 'mine'])->name('mine');
            Route::get('semua', [TugasController::class, 'all'])->name('all');

            // APPROVE LIST (hindari bentrok dengan {tugas})
            Route::get('approve-list', [TugasController::class, 'approveList'])->name('approveList');

            // Redirect path lama "approve" → "approve-list"
            Route::get('approve', function () {
                return redirect()->route('surat_tugas.approveList');
            })->name('approveRedirect');

            // --- AWAL MODIFIKASI: Standarisasi {id} menjadi {tugas} ---
            // Mengganti {id} menjadi {tugas} agar seragam dengan route resource di bawah
            // dan memaksimalkan Route Model Binding.

            // Aksi APPROVE by ID ✅ VERSI BARU distandarkan ke {tugas}
            Route::match(['post','patch'], '{tugas}/approve', [TugasController::class, 'approve'])
                ->name('approve')->whereNumber('tugas');

            // Highlight & Download PDF ✅ VERSI BARU distandarkan ke {tugas}
            Route::get('{tugas}/highlight', [TugasController::class, 'highlight'])
                ->name('highlight')->whereNumber('tugas');
            Route::get('{tugas}/download-pdf', [TugasController::class, 'downloadPdf'])
                ->name('downloadPdf')->whereNumber('tugas');

            // --- AKHIR MODIFIKASI ---

            // === Quick Preview (lihat cepat) === (Sudah benar)
            Route::get('{tugas}/preview', [TugasController::class, 'preview'])
                ->name('preview')->whereNumber('tugas');

            // Tambah penerima (policy addRecipient + binding) (Sudah benar)
            Route::post('{tugas}/penerima', function (\App\Models\TugasHeader $tugas) {
                request()->merge(['tugas_id' => $tugas->getKey()]);
                return app(\App\Http\Controllers\TugasController::class)->addRecipient(request());
            })->name('recipient.add')->middleware('can:addRecipient,tugas')->whereNumber('tugas');

            // ====== ROUTE DINAMIS (TERAKHIR) ====== (Ini sudah benar dari awal)
            Route::get('{tugas}', [TugasController::class, 'show'])->name('show')->middleware('can:view,tugas')->whereNumber('tugas');
            Route::get('{tugas}/edit', [TugasController::class, 'edit'])->name('edit')->middleware('can:update,tugas')->whereNumber('tugas');
            Route::put('{tugas}', [TugasController::class, 'update'])->name('update')->middleware('can:update,tugas')->whereNumber('tugas');
            Route::delete('{tugas}', [TugasController::class, 'destroy'])->name('destroy')->middleware('can:delete,tugas')->whereNumber('tugas');
        });

    /*
    |----------------------------------------------------------------------
    | 5) Notifikasi
    |----------------------------------------------------------------------
    */
    Route::get('notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::patch('notifikasi/{id}/read', [NotifikasiController::class, 'markAsRead'])->name('notifikasi.read');

    /*
    |----------------------------------------------------------------------
    | 6) Kumpulan route “Surat Keputusan”
    |----------------------------------------------------------------------
    */
    Route::prefix('surat_keputusan')
        ->name('surat_keputusan.')
        ->group(function () {
            Route::get('/', fn() => redirect()->route('surat_keputusan.mine'))->name('index');

            Route::get('keputusan_saya', [\App\Http\Controllers\KeputusanController::class, 'mine'])->name('mine');

            Route::get('approve', [\App\Http\Controllers\KeputusanController::class, 'approveList'])->name('approveList');

            Route::get('semua', [\App\Http\Controllers\KeputusanController::class, 'all'])
                ->name('all')
                ->middleware('can:viewAny,App\Models\KeputusanHeader');

            Route::get('{id}/highlight', [\App\Http\Controllers\KeputusanController::class, 'highlight'])
                ->name('highlight')
                ->middleware('can:view,App\Models\KeputusanHeader');

            Route::get('{id}/download-pdf', [\App\Http\Controllers\KeputusanController::class, 'downloadPdf'])
                ->name('downloadPdf')
                ->middleware('can:view,App\Models\KeputusanHeader');

            Route::patch('{id}/approve', [\App\Http\Controllers\KeputusanController::class, 'approve'])->name('approve');

            Route::resource('/', \App\Http\Controllers\KeputusanController::class)
                ->parameters(['' => 'id'])
                ->except(['index']);
        });

    /*
    |----------------------------------------------------------------------
    | 7) Pengaturan Kop Surat (hanya yang berwenang)
    |----------------------------------------------------------------------
    */
    Route::middleware('can:manage-kop-surat')->group(function () {
        Route::get('/pengaturan/kop-surat', [MasterKopSuratController::class, 'index'])->name('kop.index');
        Route::put('/pengaturan/kop-surat', [MasterKopSuratController::class, 'update'])->name('kop.update');
    });

    /*
    |----------------------------------------------------------------------
    | 8) TTD Saya (TTD personal & privat, untuk role penandatangan)
    |   URL: /kop-surat/ttd-saya
    |----------------------------------------------------------------------
    */
    // START PATCH: Routes TTD Saya
    Route::get('/kop-surat/ttd-saya', [MySignatureController::class, 'edit'])->name('kop.ttd.edit');
    Route::post('/kop-surat/ttd-saya', [MySignatureController::class, 'update'])->name('kop.ttd.update');
    // END PATCH

    Route::get('/dev/send-surat/{id}', function ($id) {
    SendSuratTugasEmail::dispatch((int)$id, 'to_recipients');
    return "Job dikirim untuk surat ID {$id}. Cek inbox (atau MailHog).";
});

});