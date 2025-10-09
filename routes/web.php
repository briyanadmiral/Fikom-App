<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TugasController;
use App\Http\Controllers\JenisTugasController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\MasterKopSuratController;
use App\Http\Controllers\MySignatureController;
use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\SuratKeputusanController;
use App\Http\Controllers\SuratKeputusan\NomorSuratController;
use App\Http\Controllers\RedirectController; // <-- NEW
use App\Jobs\SendSuratTugasEmail;
use App\Models\KeputusanHeader;

/*
|--------------------------------------------------------------------------
| Redirect root ('/') langsung ke login
|--------------------------------------------------------------------------
*/
Route::redirect('/', '/login');

/*
|--------------------------------------------------------------------------
| Auth routes
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
    | 1) CRUD Pengguna & 2) Peran
    |----------------------------------------------------------------------
    */
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);

    /*
    |----------------------------------------------------------------------
    | 3) CRUD Jenis Surat Tugas
    |----------------------------------------------------------------------
    */
    Route::resource('jenis_surat_tugas', JenisTugasController::class)->except(['show']);

    /*
    |----------------------------------------------------------------------
    | 4) Kumpulan route “Surat Tugas”
    |----------------------------------------------------------------------
    */
    Route::prefix('surat_tugas')->name('surat_tugas.')->group(function () {

        Route::get('/', [TugasController::class, 'mine'])->name('index'); // redirect->mine tanpa closure
        Route::get('create', [TugasController::class, 'create'])->name('create');
        Route::post('/', [TugasController::class, 'store'])->name('store');

        Route::get('tugas_saya', [TugasController::class, 'mine'])->name('mine');
        Route::get('semua', [TugasController::class, 'all'])->name('all');

        Route::get('approve-list', [TugasController::class, 'approveList'])
            ->name('approveList')->middleware('can:view-approve-list');

        // was closure -> now controller method
        Route::get('approve', [RedirectController::class, 'toApproveListSt'])
            ->name('approveRedirect')->middleware('can:view-approve-list');

        Route::get('{tugas}/review-approve', [TugasController::class, 'approveForm'])
            ->name('approveForm')->whereNumber('tugas')->middleware('can:approve-tugas,tugas');

        Route::get('{tugas}/approve/preview', [TugasController::class, 'approvePreview'])
            ->name('approvePreview')->whereNumber('tugas')->middleware('can:approve-tugas,tugas');

        Route::match(['post', 'patch'], '{tugas}/approve', [TugasController::class, 'approve'])
            ->name('approve')->whereNumber('tugas')->middleware('can:approve-tugas,tugas');

        Route::get('{tugas}/highlight', [TugasController::class, 'highlight'])
            ->name('highlight')->whereNumber('tugas')->middleware('can:view,tugas');

        Route::get('{tugas}/download-pdf', [TugasController::class, 'downloadPdf'])
            ->name('downloadPdf')->whereNumber('tugas')->middleware('can:view,tugas');

        Route::get('{tugas}/preview', [TugasController::class, 'preview'])
            ->name('preview')->whereNumber('tugas')->middleware('can:view,tugas');

        // was closure -> now direct controller method (route:cache safe)
        Route::post('{tugas}/penerima', [TugasController::class, 'addRecipient'])
            ->name('recipient.add')->middleware('can:addRecipient,tugas')->whereNumber('tugas');

        Route::get('{tugas}', [TugasController::class, 'show'])
            ->name('show')->middleware('can:view,tugas')->whereNumber('tugas');

        Route::get('{tugas}/edit', [TugasController::class, 'edit'])
            ->name('edit')->middleware('can:update,tugas')->whereNumber('tugas');

        Route::put('{tugas}', [TugasController::class, 'update'])
            ->name('update')->middleware('can:update,tugas')->whereNumber('tugas');

        Route::delete('{tugas}', [TugasController::class, 'destroy'])
            ->name('destroy')->middleware('can:delete,tugas')->whereNumber('tugas');
    });

    // Kompatibilitas URL lama (closure -> controller)
    Route::get('/surat-tugas/{any?}', [RedirectController::class, 'legacySt'])
        ->where('any', '.*');

    /*
    |----------------------------------------------------------------------
    | 5) Pengaturan Akun & 6) Notifikasi
    |----------------------------------------------------------------------
    */
    Route::prefix('pengaturan')->name('account.')->group(function () {
        Route::get('akun', [AccountSettingsController::class, 'edit'])->name('settings');
        Route::put('akun/profile', [AccountSettingsController::class, 'updateProfile'])->name('updateProfile');
        Route::put('akun/password', [AccountSettingsController::class, 'updatePassword'])->name('updatePassword');
    });

    Route::get('notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::patch('notifikasi/{id}/read', [NotifikasiController::class, 'markAsRead'])
        ->name('notifikasi.read')->whereNumber('id');

    /*
    |----------------------------------------------------------------------
    | 7) Kumpulan route “Surat Keputusan” (SK)
    |----------------------------------------------------------------------
    */
    Route::prefix('surat_keputusan')->name('surat_keputusan.')->group(function () {
        Route::get('/', [SuratKeputusanController::class, 'index'])->name('index');

        Route::get('/approve-list', [SuratKeputusanController::class, 'approveList'])
            ->name('approveList')->middleware('can:view-approve-list');

        // was closure -> now controller method
        Route::get('/approve', [RedirectController::class, 'toApproveListSk'])
            ->name('approveRedirect')->middleware('can:view-approve-list');

        Route::get('/saya', [SuratKeputusanController::class, 'mine'])->name('mine');

        Route::get('/create', [SuratKeputusanController::class, 'create'])
            ->name('create')->middleware('can:create,' . KeputusanHeader::class);
        Route::post('/', [SuratKeputusanController::class, 'store'])
            ->name('store')->middleware('can:create,' . KeputusanHeader::class);

        Route::get('/{surat_keputusan}/edit', [SuratKeputusanController::class, 'edit'])
            ->name('edit')->whereNumber('surat_keputusan')->middleware('can:update,surat_keputusan');
        Route::put('/{surat_keputusan}', [SuratKeputusanController::class, 'update'])
            ->name('update')->whereNumber('surat_keputusan')->middleware('can:update,surat_keputusan');

        Route::post('/{surat_keputusan}/submit',  [SuratKeputusanController::class, 'submit'])
            ->name('submit')->whereNumber('surat_keputusan')->middleware('can:submit,surat_keputusan');
        Route::post('/{surat_keputusan}/approve', [SuratKeputusanController::class, 'approve'])
            ->name('approve')->whereNumber('surat_keputusan')->middleware('can:approve,surat_keputusan');
        Route::post('/{surat_keputusan}/reject', [SuratKeputusanController::class, 'reject'])
            ->name('reject')->whereNumber('surat_keputusan')->middleware('can:reject,surat_keputusan');
        Route::post('/{surat_keputusan}/reopen', [SuratKeputusanController::class, 'reopen'])
            ->name('reopen')->whereNumber('surat_keputusan')->middleware('can:reopen,surat_keputusan');
        Route::post('/{surat_keputusan}/publish', [SuratKeputusanController::class, 'publish'])
            ->name('publish')->whereNumber('surat_keputusan')->middleware('can:publish,surat_keputusan');
        Route::post('/{surat_keputusan}/archive', [SuratKeputusanController::class, 'archive'])
            ->name('archive')->whereNumber('surat_keputusan')->middleware('can:archive,surat_keputusan');

        Route::get('/{surat_keputusan}/approve-form', [SuratKeputusanController::class, 'approveForm'])
            ->name('approveForm')->whereNumber('surat_keputusan')->middleware('can:approve,surat_keputusan');
        Route::get('/{surat_keputusan}/approve-preview', [SuratKeputusanController::class, 'approvePreview'])
            ->name('approvePreview')->whereNumber('surat_keputusan')->middleware('can:approve,surat_keputusan');

        Route::get('/{surat_keputusan}/preview', [SuratKeputusanController::class, 'preview'])
            ->name('preview')->whereNumber('surat_keputusan')->middleware('can:view,surat_keputusan');
        Route::get('/{surat_keputusan}/download', [SuratKeputusanController::class, 'downloadPdf'])
            ->name('downloadPdf')->whereNumber('surat_keputusan')->middleware('can:view,surat_keputusan');

        Route::get('/{surat_keputusan}', [SuratKeputusanController::class, 'show'])
            ->name('show')->whereNumber('surat_keputusan')->middleware('can:view,surat_keputusan');
    });

    // Kompatibilitas URL lama (closure -> controller)
    Route::get('/surat-keputusan/{any?}', [RedirectController::class, 'legacySk'])
        ->where('any', '.*');

    // Reserve nomor SK (tambahan throttle)
    Route::post('/surat_keputusan/nomor/reserve', [NomorSuratController::class, 'reserve'])
        ->name('surat_keputusan.nomor.reserve')
        ->middleware(['throttle:20,1']); // sudah di dalam group auth, jadi tak perlu 'auth' lagi

    /*
    |----------------------------------------------------------------------
    | 8) Pengaturan Kop Surat & 9) TTD Saya
    |----------------------------------------------------------------------
    */
    Route::middleware('can:manage-kop-surat')->group(function () {
        Route::get('/pengaturan/kop-surat', [MasterKopSuratController::class, 'index'])->name('kop.index');
        Route::put('/pengaturan/kop-surat', [MasterKopSuratController::class, 'update'])->name('kop.update');
    });
    Route::get('/kop-surat/ttd-saya', [MySignatureController::class, 'edit'])->name('kop.ttd.edit');
    Route::post('/kop-surat/ttd-saya', [MySignatureController::class, 'update'])->name('kop.ttd.update');

    /*
    |----------------------------------------------------------------------
    | 10) Dev helper
    |----------------------------------------------------------------------
    */
    Route::get('/dev/send-surat/{id}', function ($id) {
        SendSuratTugasEmail::dispatch((int) $id, 'to_recipients');
        return "Job dikirim untuk surat ID {$id}. Cek inbox (atau MailHog).";
    })->whereNumber('id');
});
