# Panduan Deployment - Surat SIEGA

Dokumen ini berisi panduan lengkap untuk deploy dan konfigurasi sistem Surat SIEGA di server production.

---

## 1. Konfigurasi Environment (.env)

Salin `.env.example` ke `.env` lalu isi semua value yang diperlukan:

```bash
cp .env.example .env
php artisan key:generate
```

### Variable Wajib

| Variable | Contoh | Keterangan |
|----------|--------|------------|
| `APP_ENV` | `production` | Wajib `production` di server |
| `APP_DEBUG` | `false` | Wajib `false` di server |
| `APP_URL` | `https://siega.unika.ac.id` | URL lengkap aplikasi |
| `DB_DATABASE` | `surat_fikom` | Nama database |
| `DB_USERNAME` | `siega_user` | User database (jangan root) |
| `DB_PASSWORD` | `password_kuat` | Password database |
| `ENTRY_SHARED_SECRET` | *(lihat bawah)* | **WAJIB DIISI** - jika kosong, semua akses dari dashboard eksternal akan ditolak |
| `FIKOM_DASHBOARD_URL` | `https://fikom.unika.ac.id/index.php` | URL dashboard Fikom-App utama |

### Generate ENTRY_SHARED_SECRET

```bash
php -r "echo bin2hex(random_bytes(32));"
```

Salin output-nya ke `.env`:
```
ENTRY_SHARED_SECRET=hasil_generate_di_atas
```

**Penting:** Secret ini harus sama dengan yang digunakan di project Fikom-App utama untuk generate token HMAC.

### Variable Session (Production)

```env
SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
```

---

## 2. Setup Cron Job (Scheduler)

Sistem SIEGA memiliki 3 scheduled command yang berjalan otomatis:

| Jadwal | Command | Fungsi |
|--------|---------|--------|
| Setiap hari jam 08:00 | `surat:pending-reminder` | Kirim reminder ke approver jika surat pending > 3 hari |
| Setiap Senin jam 07:00 | `surat:weekly-digest` | Kirim ringkasan mingguan via email |
| Setiap jam | `surat:queue-health` | Cek kesehatan antrian email |

**Cukup 1 baris cron saja.** Laravel scheduler yang mengatur jadwal masing-masing command secara internal.

### Setup di Linux Server (VPS/Dedicated)

Jalankan:
```bash
crontab -e
```

Tambahkan **1 baris** ini di paling bawah:
```
* * * * * cd /path/ke/surat_siega && php artisan schedule:run >> /dev/null 2>&1
```

Ganti `/path/ke/surat_siega` dengan path absolut folder project di server.

Contoh:
```
* * * * * cd /var/www/Fikom-App/surat_siega && php artisan schedule:run >> /dev/null 2>&1
```

### Setup di Shared Hosting (cPanel)

1. Login ke **cPanel**
2. Cari menu **Cron Jobs** di bagian Advanced
3. Tambah cron baru:
   - Common Settings: **Once Per Minute**
   - Command:
     ```
     cd /home/username/public_html/Fikom-App/surat_siega && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
     ```
   - Sesuaikan path `/home/username/...` dengan path project di hosting
   - Cek path PHP dengan: `which php` di terminal SSH

### Setup di Windows (Development - Laragon)

Untuk testing di lokal, buka terminal di folder project lalu jalankan:
```bash
php artisan schedule:work
```
Command ini akan terus berjalan dan mengeksekusi scheduled tasks. Hentikan dengan `Ctrl+C`.

### Verifikasi

```bash
php artisan schedule:list
```

Harus muncul 3 scheduled command:
```
0 8 * * *   surat:pending-reminder --days=3
0 7 * * 1   surat:weekly-digest
0 * * * *   surat:queue-health
```

### Test Manual

```bash
# Test reminder (cari surat pending > 1 hari)
php artisan surat:pending-reminder --days=1

# Test weekly digest
php artisan surat:weekly-digest

# Test queue health
php artisan surat:queue-health
```

---

## 3. Setup Queue Worker

Sistem menggunakan queue untuk mengirim email. Jalankan queue worker:

```bash
# Development
php artisan queue:work

# Production (dengan supervisor)
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

### Setup Supervisor (Production - Linux)

Buat file `/etc/supervisor/conf.d/siega-worker.conf`:

```ini
[program:siega-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/ke/surat_siega/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/ke/surat_siega/storage/logs/worker.log
stopwaitsecs=3600
```

Lalu:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start siega-worker:*
```

---

## 4. Checklist Deploy

### Sebelum Deploy

- [ ] `APP_ENV=production` dan `APP_DEBUG=false`
- [ ] `ENTRY_SHARED_SECRET` sudah di-generate dan diisi
- [ ] `FIKOM_DASHBOARD_URL` sudah diisi dengan URL production
- [ ] `SESSION_SECURE_COOKIE=true` (server harus HTTPS)
- [ ] `SESSION_ENCRYPT=true`
- [ ] Database sudah di-migrate: `php artisan migrate --force`
- [ ] Storage link sudah dibuat: `php artisan storage:link`

### Setelah Deploy

- [ ] Jalankan: `php artisan config:cache`
- [ ] Jalankan: `php artisan route:cache`
- [ ] Jalankan: `php artisan view:cache`
- [ ] Cron job sudah dikonfigurasi (lihat Section 2)
- [ ] Queue worker sudah berjalan (lihat Section 3)
- [ ] Test login via `/entry?user_id=X&token=Y` - harus berhasil dengan token valid
- [ ] Test login via `/entry?user_id=X` tanpa token - harus ditolak (403)
- [ ] Test akses `/users` dengan user non-admin - harus ditolak (403)
- [ ] Test download PDF surat draft - harus ada watermark "DRAFT"
- [ ] Test download PDF surat final - tidak boleh ada watermark

### Setelah Update Code

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 5. Daftar Perubahan Keamanan

Berikut ringkasan semua perbaikan keamanan yang telah diterapkan:

### Kritis
1. **Auth bypass dicegah** - `/entry` sekarang wajib token HMAC (tidak bisa diakses tanpa secret)
2. **User/Role CRUD dilindungi** - hanya Admin TU yang bisa kelola pengguna dan peran
3. **15+ route dilindungi** - semua route admin-only sekarang memerlukan middleware `admin`
4. **Session fixation dicegah** - session di-regenerate sebelum login

### Tinggi
5. **Mass assignment dicegah** - `peran_id` dihapus dari User `$fillable`
6. **Query bug diperbaiki** - dashboard hanya tampilkan surat yang ditugaskan ke approver yang benar
7. **Transaction diperbaiki** - orphaned `DB::rollBack()` sudah diganti `DB::transaction()`
8. **SSRF dicegah** - DomPDF `isRemoteEnabled` dimatikan
9. **Null-safe property access** - mencegah crash saat relasi peran null

### Sedang
10. **CSP header ditambahkan** - pertahanan utama terhadap XSS
11. **HSTS header ditambahkan** - cegah downgrade ke HTTP
12. **Rate limiting ditambahkan** - 120 request/menit per session
13. **HTMLPurifier digunakan** - sanitasi HTML lebih aman dari strip_tags
14. **Password policy diperkuat** - min 8 karakter + huruf besar + huruf kecil + angka

---

## 6. Fitur Baru

### Watermark PDF
- Surat draft: watermark "D R A F T" (abu-abu)
- Preview surat: watermark "C O N T O H" (hijau)
- Surat final (disetujui): tanpa watermark

### Reminder Surat Pending
- Otomatis kirim notifikasi ke approver jika surat pending > 3 hari
- Berjalan setiap hari jam 08:00 WIB via scheduler
- Tidak mengirim duplikat (1 reminder per surat per hari)
- Test manual: `php artisan surat:pending-reminder --days=1`

### Lampiran SK
- Lampiran bisa ditambah/hapus saat status: draft, ditolak, pending
- Admin TU bisa kelola lampiran di semua status kecuali arsip
- Download lampiran tersedia untuk semua user yang bisa melihat SK

---

## 7. SQL Cleanup (Manual)

Jalankan di phpMyAdmin untuk menghapus tabel log duplikat:

```sql
-- Verifikasi dulu bahwa tugas_logs kosong
SELECT COUNT(*) AS total_rows FROM tugas_logs;

-- Verifikasi tabel yang benar masih ada datanya
SELECT COUNT(*) AS total_rows FROM tugas_log;

-- Drop tabel duplikat (HANYA jika query pertama hasilnya 0)
DROP TABLE IF EXISTS tugas_logs;
```
