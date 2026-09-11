# Jara — Agent Guidelines

Proyek ini adalah aplikasi Laravel yang berjalan **sepenuhnya di dalam Docker**. Jangan menginstal PHP atau Composer di mesin host.

## Lingkungan Development

### Prasyarat

- Docker & Docker Compose sudah terinstal dan berjalan di mesin host.
- PHP dan Composer **tidak** tersedia di host. Semua perintah PHP/Composer/Artisan harus dijalankan di dalam container `app`.

### Menjalankan Perintah

Semua perintah yang memerlukan PHP, Composer, atau Artisan harus dijalankan melalui Docker:

```sh
# Artisan
docker compose exec app php artisan <command>

# Composer
docker compose exec app composer <command>

# PHP
docker compose exec app php <script>

# Masuk ke shell container
docker compose exec app bash
```

**Jangan** jalankan `php`, `composer`, atau `php artisan` secara langsung di host.

### Container Services

| Service | Container | Port | Keterangan |
|---|---|---|---|
| `app` | `ppk_app` | 8000 | Laravel (PHP 8.4-cli + Composer) |
| `db` | `ppk_db` | 3306 | MySQL 8.0 |
| `phpmyadmin` | `ppk_phpmyadmin` | 8080 | phpMyAdmin |

### Memulai Container

```sh
docker compose up -d
```

### Menghentikan Container

```sh
docker compose down
```

### Rebuild Setelah Mengubah Dockerfile

```sh
docker compose build app
docker compose up -d app
```

## Konvensi Proyek

### Tech Stack

- **Backend:** Laravel 13, PHP 8.4
- **Frontend:** Blade templates, Tailwind CSS 4, Vite 8
- **Database:** MySQL 8.0 (container `db`, host: `db`, port: `3306`)
- **Session/Cache/Queue:** Database driver

### Struktur Kode

Ikuti struktur standar Laravel:

- `app/Models/` — Eloquent models
- `app/Http/Controllers/` — Controllers
- `app/Http/Middleware/` — Middleware
- `app/Http/Requests/` — Form request validation
- `resources/views/` — Blade templates
- `routes/web.php` — Web routes
- `database/migrations/` — Database migrations
- `database/seeders/` — Database seeders

### Database

- Koneksi: MySQL via container `db`
- Database name: `ppk_room_reservation`
- Migrasi: `docker compose exec app php artisan migrate`
- Rollback: `docker compose exec app php artisan migrate:rollback`

### Testing

```sh
docker compose exec app php artisan test
```

## Konteks Proyek

Lihat folder `docs/` untuk spesifikasi lengkap dan PRD setiap fitur (`docs/prd-*.md`). Sistem **Jara** mengelola:

- Manajemen daftar tugas (*to-do list*) dan upload berkas lampiran tugas (*personal & team*)
- Pengelompokan tugas ke dalam workspace terpisah (misal: kerjaan kantor, tugas kuliah, projek web)
- Setiap tugas memiliki judul/catatan, prioritas (`Penting`, `Menyusul`), tenggat waktu, dan toggle status selesai
- Fitur kolaborasi: mengundang pengguna terdaftar ke workspace, melihat tugas bersama, dan update status
- Pemilik workspace dapat memantau progres (persentase dan jumlah tugas yang selesai)
- Manajemen pengguna terkontrol: registrasi publik ditutup; akun dibuat dan dihapus oleh Admin
- Aktor: Administrator (`admin`), Pengguna/Anggota (`user`), Pemilik Workspace (*Owner*), dan Anggota Workspace (*Member*)
