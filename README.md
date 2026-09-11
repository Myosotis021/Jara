# Jara — Sistem Manajemen & Upload Tugas Tim/Pribadi

Aplikasi web untuk mengelola daftar tugas (*to-do list*) dan pengunggahan berkas tugas (*task upload & submission*) baik untuk keperluan personal maupun kolaborasi tim (misalnya: pekerjaan kantor, tugas kuliah, dan proyek bersama).

Jara memungkinkan pengguna mencatat tugas dengan cara penulisan yang natural (*"kaya nulis biasa"*), menetapkan prioritas (*Penting* dan *Menyusul*), menentukan tenggat waktu, mengunggah berkas lampiran pendukung, menandai tugas yang sudah selesai, serta memantau progres penyelesaian secara langsung. Sistem pendaftaran pengguna bersifat tertutup dan dikelola penuh oleh Administrator.

---

## Tech Stack

| Layer | Teknologi | Keterangan |
|---|---|---|
| **Backend** | Laravel 13, PHP 8.4 | Server-rendered monolithic application |
| **Frontend** | Blade, Tailwind CSS 4, Vite 8 | Clean & responsive UI |
| **Database** | MySQL 8.0 | Relational database dengan foreign key constraints |
| **Storage** | Laravel Local Storage | Penyimpanan berkas lampiran aman |
| **Containerization** | Docker, Docker Compose | Environment development terisolasi |
| **DB Admin** | phpMyAdmin | Web-based database management UI |

---

## Aktor Sistem

| Aktor | Peran & Hak Akses |
|---|---|
| **Administrator** | Memegang kendali penuh atas manajemen pengguna (*user management*). Mendaftarkan akun anggota tim baru dan menghapus pengguna. Tidak ada registrasi publik mandiri. |
| **Pengguna (User)** | Didaftarkan oleh Admin. Dapat membuat banyak workspace (daftar tugas), mengundang anggota terdaftar lain, membuat tugas dengan prioritas & tenggat waktu, menandai status pengerjaan, mengunggah berkas lampiran, dan memantau persentase progres tugas. |
| **Workspace Owner** | Pengguna pembuat workspace. Memiliki hak penuh untuk mengedit informasi workspace, menghapus workspace, mengundang anggota tim, dan mengeluarkan anggota dari workspace. |
| **Workspace Member** | Pengguna yang diundang ke dalam suatu workspace. Dapat melihat daftar tugas bersama, memperbarui status penyelesaian tugas, dan mengunggah/mengunduh berkas lampiran tugas. |

---

## Prasyarat Lingkungan

- [Docker](https://docs.docker.com/get-docker/) & [Docker Compose](https://docs.docker.com/compose/install/)
- Git

> **Catatan Penting:** PHP dan Composer **tidak** perlu diinstal di mesin host. Seluruh dependensi dan perintah Artisan dijalankan di dalam container `ppk_app`.

---

## Quick Start

### 1. Otomatis (Skrip Setup)

```powershell
# Windows PowerShell
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass
.\scripts\setup.ps1
```

```bash
# Linux / macOS
chmod +x scripts/setup.sh
./scripts/setup.sh
```

### 2. Manual

```bash
# 1. Salin file environment
cp .env.example .env

# 2. Build dan jalankan container
docker compose build
docker compose up -d

# 3. Install dependensi PHP (pertama kali saja)
docker compose exec app composer install

# 4. Generate application key
docker compose exec app php artisan key:generate

# 5. Jalankan migrasi database
docker compose exec app php artisan migrate

# 6. Buat storage symlink untuk upload berkas
docker compose exec app php artisan storage:link

# 7. Jalankan seeder akun default
docker compose exec app php artisan db:seed
```

---

## Akses Layanan

| Layanan | URL | Keterangan |
|---|---|---|
| **Aplikasi Jara** | [http://localhost:8000](http://localhost:8000) | Antarmuka web utama Laravel |
| **phpMyAdmin** | [http://localhost:8080](http://localhost:8080) | UI manajemen database MySQL |
| **MySQL Database** | `localhost:3306` | Akses koneksi langsung basis data |

### Akun Awal Default (Seeder)

- **Email**: `admin@jara.local`
- **Kata Sandi**: `password`
- **Peran**: `admin`

---

## Panduan Spesifikasi & Dokumen PRD

Implementasi fitur Jara dipecah ke dalam modul-modul Product Requirement Document (PRD) yang siap dieksekusi per sesi praktikum (~60 menit):

```text
Urutan 1: Fondasi Sistem
├── docs/prd-autentikasi-1.md            # Login, Logout, Session Auth, Admin Seeder

Urutan 2: Manajemen Pengguna & Ruang Kerja (Paralel)
├── docs/prd-kelola-user-2.md             # Admin User Management (Tambah & Hapus Pengguna)
└── docs/prd-kelola-workspace-2.md        # CRUD Workspace / Kategori Daftar Tugas

Urutan 3: Inti Tugas & Kolaborasi Tim (Paralel)
├── docs/prd-kelola-tugas-3.md            # Task CRUD, Prioritas, Tenggat Waktu, Toggle Selesai & Progress Bar
└── docs/prd-kolaborasi-workspace-3.md    # Undang Anggota, List Anggota, Hapus Anggota

Urutan 4: Pengunggahan Berkas Tugas
└── docs/prd-upload-tugas-4.md            # Upload Lampiran Berkas Tugas, Download & Delete File
```

Detail teknis, HTTP contract, UI state, database constraint, dan validasi lengkap tersedia pada setiap file di folder `docs/`.

---

## Perintah Pengembangan Umum

```bash
# Menjalankan seluruh container
docker compose up -d

# Menghentikan semua container
docker compose down

# Melihat live logs container aplikasi
docker compose logs -f app

# Masuk ke terminal bash container app
docker compose exec app bash

# Menjalankan artisan command
docker compose exec app php artisan <command>

# Menjalankan migrasi database
docker compose exec app php artisan migrate

# Rollback migrasi database
docker compose exec app php artisan migrate:rollback

# Menjalankan pengujian otomatis
docker compose exec app php artisan test
```

---

## Struktur Proyek

```
├── app/                    # Logika aplikasi (Models, Controllers, Middleware)
│   ├── Http/Controllers/  # Auth, Admin\User, Workspace, Task, TaskAttachment Controllers
│   └── Models/             # User, Workspace, Task, TaskAttachment
├── bootstrap/              # Laravel bootstrap files
├── config/                 # Konfigurasi aplikasi
├── database/
│   ├── factories/          # Model factories
│   ├── migrations/         # Database migrations (users, workspaces, tasks, attachments)
│   └── seeders/            # Database seeders (Admin default seeder)
├── docs/                   # Dokumen spesifikasi teknis fitur (PRD)
│   ├── prd-autentikasi-1.md
│   ├── prd-kelola-user-2.md
│   ├── prd-kelola-workspace-2.md
│   ├── prd-kelola-tugas-3.md
│   ├── prd-kolaborasi-workspace-3.md
│   └── prd-upload-tugas-4.md
├── docker/                 # Konfigurasi PHP custom & Dockerfiles
├── public/                 # Entry point & assets terkompilasi
├── resources/              # Blade views, Tailwind CSS, JS
├── routes/                 # Definisi rute web (routes/web.php)
├── storage/                # Lampiran berkas tugas, logs, framework cache
├── tests/                  # Feature & Unit tests
├── .env.example            # Template environment variables
├── docker-compose.yml      # Orkestrasi Docker (app, db, phpmyadmin)
└── README.md               # Dokumentasi utama proyek
```

---

## Konfigurasi Database Default

| Variabel | Nilai Default |
|---|---|
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | `db` |
| `DB_PORT` | `3306` |
| `DB_DATABASE` | `ppk_room_reservation` (atau sesuaikan `jara_db` di `.env`) |
| `DB_USERNAME` | `laravel_user` |
| `DB_PASSWORD` | `laravel_password` |
| `DB_ROOT_PASSWORD` | `root_password` |
