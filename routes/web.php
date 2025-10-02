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
use App\Jobs\SendSuratTugasEmail;
use App\Models\KeputusanHeader;
use App\Http\Controllers\SuratKeputusan\NomorSuratController;

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
        // ... (Semua rute Surat Tugas Anda tetap sama, tidak perlu diubah)
        Route::get('/', fn() => redirect()->route('surat_tugas.mine'))->name('index');
        Route::get('create', [TugasController::class, 'create'])->name('create');
        Route::post('/', [TugasController::class, 'store'])->name('store');
        Route::get('tugas_saya', [TugasController::class, 'mine'])->name('mine');
        Route::get('semua', [TugasController::class, 'all'])->name('all');
        Route::get('approve-list', [TugasController::class, 'approveList'])
            ->name('approveList')
            ->middleware('can:view-approve-list');
        Route::get('approve', function () {
            return redirect()->route('surat_tugas.approveList');
        })->name('approveRedirect')->middleware('can:view-approve-list');
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
        Route::post('{tugas}/penerima', function (\App\Models\TugasHeader $tugas) {
            request()->merge(['tugas_id' => $tugas->getKey()]);
            return app(\App\Http\Controllers\TugasController::class)->addRecipient(request());
        })->name('recipient.add')->middleware('can:addRecipient,tugas')->whereNumber('tugas');
        Route::get('{tugas}', [TugasController::class, 'show'])->name('show')->middleware('can:view,tugas')->whereNumber('tugas');
        Route::get('{tugas}/edit', [TugasController::class, 'edit'])->name('edit')->middleware('can:update,tugas')->whereNumber('tugas');
        Route::put('{tugas}', [TugasController::class, 'update'])->name('update')->middleware('can:update,tugas')->whereNumber('tugas');
        Route::delete('{tugas}', [TugasController::class, 'destroy'])->name('destroy')->middleware('can:delete,tugas')->whereNumber('tugas');
    });

    // Kompatibilitas URL lama
    Route::get('/surat-tugas/{any?}', function (string $any = null) {
        $to = '/surat_tugas' . ($any ? '/' . $any : '');
        $qs = request()->getQueryString();
        return redirect()->to($qs ? "{$to}?{$qs}" : $to, 301);
    })->where('any', '.*');

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
    Route::patch('notifikasi/{id}/read', [NotifikasiController::class, 'markAsRead'])->name('notifikasi.read')->whereNumber('id');

    /*
|----------------------------------------------------------------------
| 7) Kumpulan route “Surat Keputusan” (SK)
|----------------------------------------------------------------------
*/
    Route::prefix('surat_keputusan')->name('surat_keputusan.')->group(function () {
        // Daftar umum & daftar yang menunggu persetujuan
        Route::get('/', [SuratKeputusanController::class, 'index'])->name('index');
        Route::get('/approve-list', [SuratKeputusanController::class, 'approveList'])
            ->name('approveList')
            ->middleware('can:view-approve-list'); // jika Anda punya Gate/Policy ini

        // Alias /approve -> /approve-list (opsional)
        Route::get('/approve', fn() => redirect()->route('surat_keputusan.approveList'))
            ->name('approveRedirect')
            ->middleware('can:view-approve-list');

        // Halaman "Surat Keputusan Saya" (per penerima)
        Route::get('/saya', [SuratKeputusanController::class, 'mine'])
            ->name('mine');

        // Create & Store (hanya role yang diizinkan policy)
        Route::get('/create', [SuratKeputusanController::class, 'create'])
            ->name('create')
            ->middleware('can:create,' . KeputusanHeader::class);
        Route::post('/', [SuratKeputusanController::class, 'store'])
            ->name('store')
            ->middleware('can:create,' . KeputusanHeader::class);

        // Edit & Update (otorisasi per record)
        Route::get('/{surat_keputusan}/edit', [SuratKeputusanController::class, 'edit'])
            ->name('edit')->whereNumber('surat_keputusan')
            ->middleware('can:update,surat_keputusan');
        Route::put('/{surat_keputusan}', [SuratKeputusanController::class, 'update'])
            ->name('update')->whereNumber('surat_keputusan')
            ->middleware('can:update,surat_keputusan');

        // Workflow
        Route::post('/{surat_keputusan}/submit',  [SuratKeputusanController::class, 'submit'])
            ->name('submit')->whereNumber('surat_keputusan')
            ->middleware('can:submit,surat_keputusan');
        Route::post('/{surat_keputusan}/approve', [SuratKeputusanController::class, 'approve'])
            ->name('approve')->whereNumber('surat_keputusan')
            ->middleware('can:approve,surat_keputusan');
        Route::post('/{surat_keputusan}/reject', [SuratKeputusanController::class, 'reject'])
            ->name('reject')->whereNumber('surat_keputusan')
            ->middleware('can:reject,surat_keputusan');
        Route::post('/{surat_keputusan}/reopen', [SuratKeputusanController::class, 'reopen'])
            ->name('reopen')->whereNumber('surat_keputusan')
            ->middleware('can:reopen,surat_keputusan');
        Route::post('/{surat_keputusan}/publish', [SuratKeputusanController::class, 'publish'])
            ->name('publish')->whereNumber('surat_keputusan')
            ->middleware('can:publish,surat_keputusan');
        Route::post('/{surat_keputusan}/archive', [SuratKeputusanController::class, 'archive'])
            ->name('archive')->whereNumber('surat_keputusan')
            ->middleware('can:archive,surat_keputusan');

        // Approve form & live preview (dipakai di approve.blade + JS fetch)
        Route::get('/{surat_keputusan}/approve-form', [SuratKeputusanController::class, 'approveForm'])
            ->name('approveForm')->whereNumber('surat_keputusan')
            ->middleware('can:approve,surat_keputusan');
        Route::get('/{surat_keputusan}/approve-preview', [SuratKeputusanController::class, 'approvePreview'])
            ->name('approvePreview')->whereNumber('surat_keputusan')
            ->middleware('can:approve,surat_keputusan');

        // Preview & Download (dipakai di index/keputusan_saya/aksi)
        Route::get('/{surat_keputusan}/preview', [SuratKeputusanController::class, 'preview'])
            ->name('preview')->whereNumber('surat_keputusan')
            ->middleware('can:view,surat_keputusan');
        Route::get('/{surat_keputusan}/download', [SuratKeputusanController::class, 'downloadPdf'])
            ->name('downloadPdf')->whereNumber('surat_keputusan')
            ->middleware('can:view,surat_keputusan');

        // TERAKHIR: Halaman detail (show) — dipakai tombol “Halaman Detail”
        Route::get('/{surat_keputusan}', [SuratKeputusanController::class, 'show'])
            ->name('show')->whereNumber('surat_keputusan')
            ->middleware('can:view,surat_keputusan');
    });

    // Kompatibilitas URL lama: /surat-keputusan/* -> /surat_keputusan/*
    Route::get('/surat-keputusan/{any?}', function (string $any = null) {
        $to = '/surat_keputusan' . ($any ? '/' . $any : '');
        $qs = request()->getQueryString();
        return redirect()->to($qs ? "{$to}?{$qs}" : $to, 301);
    })->where('any', '.*');

    Route::post('/surat_keputusan/nomor/reserve', [NomorSuratController::class, 'reserve'])
        ->name('surat_keputusan.nomor.reserve')
        ->middleware('auth');

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
