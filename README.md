# Project Surat FIKOM

## Deskripsi

Project sistem manajemen surat (Surat FIKOM) berbasis Laravel.

## Prerequisites (Persyaratan)

Sebelum menginstall, pastikan PC anda sudah terinstall aplikasi berikut:

- [Git](https://git-scm.com/downloads)
- [Composer](https://getcomposer.org/) (PHP Dependency Manager)
- [Node.js](https://nodejs.org/) (Untuk frontend assets)
- PHP >= 8.1 (Disarankan menggunakan Laragon/XAMPP)

## Panduan Instalasi (Installation Guide)

Ikuti langkah-langkah berikut untuk menginstall project ini di device baru:

### 1. Clone Repository

Buka terminal (CMD/Powershell/Git Bash) di folder tujuan (misal `C:\laragon\www`), lalu jalankan:

```bash
git clone <URL_REPOSITORY_ANDA> surat_siega
cd surat_siega
```

### 2. Install Dependencies

Install library PHP dan library JavaScript yang dibutuhkan:

```bash
composer install
npm install
```

### 3. Konfigurasi Environment (.env)

Copy file `.env.example` menjadi `.env`:

```bash
cp .env.example .env
```

_(Atau jika di Windows CMD: `copy .env.example .env`)_

Buka file `.env` dengan text editor, lalu sesuaikan bagian database:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=surat_siega
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Setup Key & Storage

Generate application key dan link storage:

```bash
php artisan key:generate
php artisan storage:link
```

### 5. Setup Database

Pastikan anda sudah membuat database kosong bernama `surat_siega` di MySQL (phpMyAdmin/HeidiSQL).

**Import Database:**
Project ini menyertakan file SQL dump `DB_Surat_siega.sql` di root folder.

- Buka phpMyAdmin / Database Manager anda.
- Pilih database `surat_siega`.
- Pilih menu **Import**.
- Upload file `DB_Surat_siega.sql` yang ada di dalam folder project ini.
- Klik **Go/Kirim**.

### 6. Build Assets & Jalankan

Compile asset frontend:

```bash
npm run build
```

Jalankan server lokal:

```bash
php artisan serve
```

Akses aplikasi di browser melalui: `http://localhost:8000`

---

## Catatan Tambahan

- Jika ada error permission di folder storage, jalankan `chmod -R 775 storage` (Linux/Mac) atau pastikan user windows memiliki akses write ke folder `storage` dan `bootstrap/cache`.
- Dokumentasi framework Laravel asli telah dipindahkan ke file `README.laravel.md`.
