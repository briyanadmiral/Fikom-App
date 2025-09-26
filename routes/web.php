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
use App\Http\Controllers\KeputusanController; // SK Controller
use App\Jobs\SendSuratTugasEmail;

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
    | 3) CRUD Jenis Surat Tugas
    |   URL: /jenis_surat_tugas
    |----------------------------------------------------------------------
    */
    Route::resource('jenis_surat_tugas', JenisTugasController::class)->except(['show']);

    /*
    |----------------------------------------------------------------------
    | 4) Kumpulan route “Surat Tugas” (kanonik: /surat_tugas)
    |----------------------------------------------------------------------
    */
    Route::prefix('surat_tugas')->name('surat_tugas.')->group(function () {

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
        Route::get('approve-list', [TugasController::class, 'approveList'])
            ->name('approveList')
            ->middleware('can:view-approve-list');

        // Redirect path lama "approve" → "approve-list"
        Route::get('approve', function () {
            return redirect()->route('surat_tugas.approveList');
        })->name('approveRedirect')->middleware('can:view-approve-list');

        // ====== APPROVE FORM & PREVIEW ======
        Route::get('{tugas}/review-approve', [TugasController::class, 'approveForm'])
            ->name('approveForm')->whereNumber('tugas')->middleware('can:approve-tugas,tugas');

        Route::get('{tugas}/approve/preview', [TugasController::class, 'approvePreview'])
            ->name('approvePreview')->whereNumber('tugas')->middleware('can:approve-tugas,tugas');

        // Aksi APPROVE
        Route::match(['post', 'patch'], '{tugas}/approve', [TugasController::class, 'approve'])
            ->name('approve')->whereNumber('tugas')->middleware('can:approve-tugas,tugas');

        // Highlight & Download PDF
        Route::get('{tugas}/highlight', [TugasController::class, 'highlight'])
            ->name('highlight')->whereNumber('tugas')->middleware('can:view,tugas');

        Route::get('{tugas}/download-pdf', [TugasController::class, 'downloadPdf'])
            ->name('downloadPdf')->whereNumber('tugas')->middleware('can:view,tugas');

        // Quick Preview (lihat cepat)
        Route::get('{tugas}/preview', [TugasController::class, 'preview'])
            ->name('preview')->whereNumber('tugas')->middleware('can:view,tugas');

        // Tambah penerima (policy addRecipient + binding)
        Route::post('{tugas}/penerima', function (\App\Models\TugasHeader $tugas) {
            request()->merge(['tugas_id' => $tugas->getKey()]);
            return app(\App\Http\Controllers\TugasController::class)->addRecipient(request());
        })->name('recipient.add')->middleware('can:addRecipient,tugas')->whereNumber('tugas');

        // ====== ROUTE DINAMIS (TERAKHIR) ======
        Route::get('{tugas}', [TugasController::class, 'show'])->name('show')->middleware('can:view,tugas')->whereNumber('tugas');
        Route::get('{tugas}/edit', [TugasController::class, 'edit'])->name('edit')->middleware('can:update,tugas')->whereNumber('tugas');
        Route::put('{tugas}', [TugasController::class, 'update'])->name('update')->middleware('can:update,tugas')->whereNumber('tugas');
        Route::delete('{tugas}', [TugasController::class, 'destroy'])->name('destroy')->middleware('can:delete,tugas')->whereNumber('tugas');
    });

    // Kompatibilitas URL lama: /surat-tugas/... → redirect 301 ke /surat_tugas/...
    Route::get('/surat-tugas/{any?}', function (string $any = null) {
        $to = '/surat_tugas' . ($any ? '/' . $any : '');
        $qs = request()->getQueryString(); // pertahankan query string
        return redirect()->to($qs ? "{$to}?{$qs}" : $to, 301);
    })->where('any', '.*');

    /*
    |----------------------------------------------------------------------
    | 5) Pengaturan Akun (Profile & Password)
    |----------------------------------------------------------------------
    */
    Route::prefix('pengaturan')->name('account.')->group(function () {
        Route::get('akun', [AccountSettingsController::class, 'edit'])->name('settings');
        Route::put('akun/profile', [AccountSettingsController::class, 'updateProfile'])->name('updateProfile');
        Route::put('akun/password', [AccountSettingsController::class, 'updatePassword'])->name('updatePassword');
    });

    /*
    |----------------------------------------------------------------------
    | 6) Notifikasi
    |----------------------------------------------------------------------
    */
    Route::get('notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::patch('notifikasi/{id}/read', [NotifikasiController::class, 'markAsRead'])->name('notifikasi.read')->whereNumber('id');

    /*
    |----------------------------------------------------------------------
    | 7) Kumpulan route “Surat Keputusan” (SK)
    |----------------------------------------------------------------------
    */
    Route::prefix('surat_keputusan')->name('surat_keputusan.')->group(function () {
    Route::get('/',       [KeputusanController::class, 'index'])->name('index');
    Route::get('/mine',   [KeputusanController::class, 'mine'])->name('mine');

    // Create & Store (batasi sesuai policy)
    Route::get('/create', [KeputusanController::class, 'create'])
        ->name('create')->middleware('can:create-keputusan');
    Route::post('/', [KeputusanController::class, 'store'])
        ->name('store')->middleware('can:create-keputusan');

    /* ===== Aksi proses (WAJIB di atas catch-all /{keputusan}) ===== */
    Route::post('/{keputusan}/submit',  [KeputusanController::class, 'submit'])
        ->name('submit')->whereNumber('keputusan')->middleware('can:submit-keputusan,keputusan');

    Route::match(['post','patch'], '/{keputusan}/approve', [KeputusanController::class, 'approve'])
        ->name('approve')->whereNumber('keputusan')->middleware('can:approve-keputusan,keputusan');

    Route::match(['post','patch'], '/{keputusan}/reject',  [KeputusanController::class, 'reject'])
        ->name('reject')->whereNumber('keputusan')->middleware('can:reject-keputusan,keputusan');

    Route::post('/{keputusan}/sign',    [KeputusanController::class, 'sign'])
        ->name('sign')->whereNumber('keputusan')->middleware('can:sign-keputusan,keputusan');

    Route::post('/{keputusan}/publish', [KeputusanController::class, 'publish'])
        ->name('publish')->whereNumber('keputusan')->middleware('can:publish-keputusan,keputusan');

    Route::post('/{keputusan}/read',    [KeputusanController::class, 'markRead'])
        ->name('read')->whereNumber('keputusan'); // validasi penerima di controller

    Route::post('/{keputusan}/sign-config', [KeputusanController::class, 'saveSignConfig'])
        ->name('sign_config')->whereNumber('keputusan'); // cek hak di controller (approve/sign)

    /* ===== Review UI & Preview (hanya approver) ===== */
    Route::get('/{keputusan}/review-approve',  [KeputusanController::class, 'approveForm'])
        ->name('approveForm')->whereNumber('keputusan')->middleware('can:approve-keputusan,keputusan');

    Route::get('/{keputusan}/approve/preview', [KeputusanController::class, 'approvePreview'])
        ->name('approvePreview')->whereNumber('keputusan')->middleware('can:approve-keputusan,keputusan');

    /* ===== Detail & Edit ===== */
    Route::get('/{keputusan}', [KeputusanController::class, 'show'])
        ->name('show')->whereNumber('keputusan')->middleware('can:view-keputusan,keputusan');

    Route::get('/{keputusan}/edit', [KeputusanController::class, 'edit'])
        ->name('edit')->whereNumber('keputusan')->middleware('can:update-keputusan,keputusan');

    Route::put('/{keputusan}', [KeputusanController::class, 'update'])
        ->name('update')->whereNumber('keputusan')->middleware('can:update-keputusan,keputusan');

    /* ===== Preview highlight & Download ===== */
    Route::get('/{keputusan}/highlight', [KeputusanController::class, 'highlight'])
        ->name('highlight')->whereNumber('keputusan')->middleware('can:view-keputusan,keputusan');

    Route::get('/{keputusan}/download-pdf', [KeputusanController::class, 'downloadPdf'])
        ->name('downloadPdf')->whereNumber('keputusan')->middleware('can:view-keputusan,keputusan');
});


    /*
    |----------------------------------------------------------------------
    | 8) Pengaturan Kop Surat (hanya yang berwenang)
    |----------------------------------------------------------------------
    */
    Route::middleware('can:manage-kop-surat')->group(function () {
        Route::get('/pengaturan/kop-surat', [MasterKopSuratController::class, 'index'])->name('kop.index');
        Route::put('/pengaturan/kop-surat', [MasterKopSuratController::class, 'update'])->name('kop.update');
    });

    /*
    |----------------------------------------------------------------------
    | 9) TTD Saya (TTD personal & privat, untuk role penandatangan)
    |   URL: /kop-surat/ttd-saya
    |----------------------------------------------------------------------
    */
    Route::get('/kop-surat/ttd-saya',  [MySignatureController::class, 'edit'])->name('kop.ttd.edit');
    Route::post('/kop-surat/ttd-saya', [MySignatureController::class, 'update'])->name('kop.ttd.update');

    /*
    |----------------------------------------------------------------------
    | 10) Dev helper: kirim email surat tugas (opsional)
    |----------------------------------------------------------------------
    */
    Route::get('/dev/send-surat/{id}', function ($id) {
        SendSuratTugasEmail::dispatch((int) $id, 'to_recipients');
        return "Job dikirim untuk surat ID {$id}. Cek inbox (atau MailHog).";
    })->whereNumber('id');
});
