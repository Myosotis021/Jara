# FILE: prd-autentikasi-1.md

# 1. Feature Overview
- **Nama Fitur**: Autentikasi Pengguna & Manajemen Sesi (Login, Logout, Role Guard)
- **Tujuan Fitur**: Menyediakan gerbang masuk yang aman bagi pengguna terdaftar (Admin dan User biasa) ke dalam sistem Jara melalui mekanisme sesi Laravel standar, serta mengarahkan pengguna ke halaman yang sesuai berdasarkan peran (*role*).
- **Masalah yang Diselesaikan**: Mencegah akses publik tanpa izin ke dalam workspace dan data tugas, serta membedakan hak akses dasar antara administrator sistem dan anggota pengguna biasa.
- **Pengguna/Aktor**: Pengguna Terdaftar (`user`) dan Administrator (`admin`).
- **Dependency terhadap PRD Lain**: Tidak ada. Ini adalah fondasi pertama sistem (Urutan 1).
- **Alasan Urutan Implementasi**: Seluruh fungsionalitas workspace, tugas, kolaborasi, dan manajemen user memerlukan identitas pengguna yang terautentikasi (*authenticated context*). Oleh karena itu, fitur autentikasi wajib diselesaikan paling awal.

---

# 2. User Story
- Sebagai **Pengguna Terdaftar** atau **Admin**, saya ingin masuk (*login*) menggunakan email dan kata sandi saya, sehingga saya dapat mengakses data workspace dan tugas saya secara aman.
- Sebagai **Pengguna yang Sedang Login**, saya ingin keluar (*logout*) dari sesi aktif saya, sehingga akun saya tidak dapat diakses oleh orang lain di perangkat yang sama.

---

# 3. Scope
## MVP / Required
- Form login dengan input `email` dan `password`, serta opsi `remember`.
- Validasi kredensial login terhadap tabel `users`.
- Regenerasi ID sesi setelah login sukses untuk mencegah *session fixation attack*.
- Mekanisme logout yang membatalkan (*invalidate*) sesi dan me-regenerasi CSRF token.
- Role checking sederhana (`admin` vs `user`):
  - `admin` diarahkan ke `/admin/users`.
  - `user` diarahkan ke `/workspaces`.
- Middleware proteksi route autentikasi (`auth`) dan tamu (`guest`).
- Database Seeder untuk akun default awal administrator (`admin@jara.local` / `password`).

## If Time Permits
- Fitur *throttle login* bawaan Laravel (misal batas 5 percobaan gagal per menit menggunakan `RateLimiter`).

## Out of Scope
- Registrasi mandiri publik (*self-registration*) karena pendaftaran dikontrol penuh oleh Admin (lihat `prd-kelola-user-2.md`).
- Reset password via email / verifikasi email SMTP.
- Two-Factor Authentication (2FA) / OAuth (Google Login).
- Remember me cookie berbasis enkripsi kustom di luar mekanisme default Laravel.

---

# 4. Preconditions
- Tabel dasar `users` dan `sessions` dari migrasi default Laravel sudah terpasang.
- Seeder untuk admin default telah dijalankan (`DatabaseSeeder`).
- Container Docker aplikasi Laravel dan MySQL berjalan normal.

---

# 5. Main User Flow
1. Pengguna membuka URL aplikasi `/login` melalui browser.
2. Sistem memeriksa status sesi: jika pengguna sudah login, diarahkan otomatis ke dashboard sesuai perannya.
3. Jika belum login, sistem menampilkan halaman form login.
4. Pengguna memasukkan `email` dan `password`.
5. Pengguna menekan tombol "Masuk".
6. Browser mengirimkan HTTP request `POST /login` dengan token CSRF.
7. Backend memvalidasi format input (email valid dan password terisi).
8. Backend mencoba mencocokkan kredensial menggunakan `Auth::attempt()`.
9. Kredensial valid:
   - Backend memanggil `$request->session()->regenerate()`.
   - Backend memeriksa kolom `role` pada model `User`.
   - Jika role = `admin`, redirect ke `/admin/users`.
   - Jika role = `user`, redirect ke `/workspaces`.
   - Flash message sukses ditampilkan di halaman tujuan.
10. Untuk keluar: Pengguna menekan tombol "Keluar / Logout" di header/navbar.
11. Browser mengirim HTTP request `POST /logout` dengan token CSRF.
12. Backend mengeksekusi `Auth::logout()`, meng-invalidate sesi, me-regenerasi token, dan me-redirect pengguna ke `/login` dengan flash message.

---

# 6. Alternative Flow
- **Input Kosong / Format Salah**:
  - Pengguna tidak mengisi email atau format bukan email valid.
  - Backend mengembalikan HTTP 302 redirect back ke `/login` dengan membawa `$errors` dan `old('email')`. Password dikosongkan.
- **Kredensial Tidak Cocok**:
  - Email tidak ditemukan atau password salah.
  - Backend mengembalikan redirect back ke `/login` dengan pesan error umum pada field email: *"Kredensial yang dimasukkan tidak cocok dengan data kami."*
- **Akses Halaman Terproteksi Tanpa Login**:
  - Pengguna anonim membuka `/workspaces` atau `/admin/users`.
  - Middleware `auth` mencegat dan me-redirect pengguna ke `/login` dengan pesan flash peringatan.
- **Pengguna Non-Admin Mencoba Akses Area Admin**:
  - Pengguna ber-role `user` membuka `/admin/users`.
  - Middleware role guard menolak dan me-redirect ke `/workspaces` dengan flash error *"Anda tidak memiliki akses ke halaman tersebut."* atau memunculkan HTTP 403 Forbidden.

---

# 7. Low-Fidelity UI Design
```text
+-------------------------------------------------------------+
|                            JARA                             |
|          Sistem Manajemen & Upload Tugas Tim/Pribadi        |
+-------------------------------------------------------------+
|                                                             |
|               +-----------------------------+               |
|               |        Masuk ke Jara        |               |
|               +-----------------------------+               |
|               | [ Flash Alert Container   ] |               |
|               |                             |               |
|               | Email                       |               |
|               | [ user@example.com        ] |               |
|               | [ error container: email  ] |               |
|               |                             |               |
|               | Kata Sandi                  |               |
|               | [ *****************       ] |               |
|               | [ error container: pass   ] |               |
|               |                             |               |
|               | [ ] Ingat Saya              |               |
|               |                             |               |
|               | [       Tombol Masuk      ] |               |
|               +-----------------------------+               |
|                                                             |
+-------------------------------------------------------------+
```

---

# 8. Visual Design & Tailwind Guidance
- **Page Background**: `bg-gray-50 min-h-screen flex items-center justify-center p-4`
- **Card Container**: `w-full max-w-md bg-white rounded-xl shadow-md border border-gray-100 p-8`
- **Header Branding**:
  - Teks Logo: `text-2xl font-bold text-blue-600 tracking-tight text-center`
  - Subtitle: `text-sm text-gray-500 text-center mt-1 mb-6`
- **Form Controls**:
  - Label: `block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1`
  - Input Default: `w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition`
  - Input Error: `w-full px-3 py-2 border border-red-500 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500`
- **Buttons**:
  - Primary Action (Masuk): `w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-4 rounded-lg transition duration-150 ease-in-out shadow-sm`
- **Flash Alert**:
  - Error: `bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm mb-4`
  - Success: `bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm mb-4`

---

# 9. UI States
- **Default State**: Form kosong (atau terisi `old('email')` bila redirect back), tombol "Masuk" aktif.
- **Submitting State**: Native browser form submit (tombol tertekan saat request dikirim).
- **Validation Error State**: Kotak input email/password berubah border merah, teks error merah tampil tepat di bawah input masing-masing.
- **Backend/Auth Failure State**: Container peringatan merah muncul di atas form yang menjelaskan bahwa kredensial tidak sesuai.
- **Success State**: Pasca login sukses, pengguna langsung dialihkan ke dashboard masing-masing dengan flash alert *"Selamat datang kembali, {User}!"*.
- **Loading State**: Tidak diperlukan karena menggunakan SSR Blade standar dan navigasi HTTP sinkron.
- **Empty State**: Tidak relevan untuk form login.

---

# 10. Error Container Specification
- **Field-level Error: Email**:
  - Lokasi: Tepat di bawah input `email`.
  - Blade Syntax:
    ```html
    @error('email')
        <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
    @enderror
    ```
- **Field-level Error: Password**:
  - Lokasi: Tepat di bawah input `password`.
  - Blade Syntax:
    ```html
    @error('password')
        <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
    @enderror
    ```
- **Form-level Flash Error**:
  - Lokasi: Di bagian atas card form sebelum input pertama.
  - Blade Syntax:
    ```html
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-2.5 rounded-lg mb-4">
            {{ session('error') }}
        </div>
    @endif
    ```

---

# 11. Form Specification
### 1. Email
- HTML: `type="email"`
- Name: `email`
- Frontend: `required`, `maxlength="255"`, `autocomplete="email"`
- Backend: `required|string|email|max:255`
- Database: `VARCHAR(255) NOT NULL UNIQUE`
- Old input: `value="{{ old('email') }}"`

### 2. Password
- HTML: `type="password"`
- Name: `password`
- Frontend: `required`
- Backend: `required|string`
- Database: `VARCHAR(255) NOT NULL` (Hashed using Bcrypt / Argon2)
- Old input: Dikosongkan (tidak pernah mengembalikan value password lama demi keamanan).

### 3. Remember Me
- HTML: `type="checkbox"`
- Name: `remember`
- Frontend: Optional
- Backend: `nullable|boolean`

---

# 12. Frontend Validation
- `required` pada atribut input email dan password.
- `type="email"` pada input email untuk format checking dasar di browser.
- Catatan: Frontend validation hanya membantu UX agar form tidak dikirim dalam keadaan kosong; backend tetap menjadi penjaga utama.

---

# 13. Backend Validation
Validation Rules pada `AuthController@login`:
```php
$credentials = $request->validate([
    'email' => ['required', 'string', 'email', 'max:255'],
    'password' => ['required', 'string'],
    'remember' => ['nullable', 'boolean'],
]);
```
- Jika validasi gagal, Laravel otomatis melakukan `302 redirect back` dengan `$errors` dan `old('email')`.

---

# 14. Input Sanitization & XSS Prevention
- Semua rendering nama atau email pengguna di Blade wajib menggunakan sintaks kurung kurawal ganda `{{ $user->name }}` yang secara otomatis menjalankan `htmlspecialchars()`.
- Hindari penggunaan `{!! !!}` untuk data user manapun.
- Data input di-*trim* otomatis oleh middleware default Laravel `TrimStrings`.

---

# 15. SQL Injection Prevention
- Autentikasi memanfaatkan `Auth::attempt()` yang di bawahnya menggunakan Eloquent / PDO Prepared Statements dengan parameter binding penuh.
- Tidak ada raw query string concatenation (`WHERE email = '$email'`).

---

# 16. Database Design
Tabel `users` (modifikasi/tambahan kolom `role` pada migrasi standar):
```text
users
--------------------------------------------------------
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
name                VARCHAR(255) NOT NULL
email               VARCHAR(255) NOT NULL UNIQUE
password            VARCHAR(255) NOT NULL
role                ENUM('admin', 'user') NOT NULL DEFAULT 'user'
remember_token      VARCHAR(100) NULL
created_at          TIMESTAMP NULL
updated_at          TIMESTAMP NULL
```

---

# 17. Database Constraints
- `email`: `UNIQUE INDEX` pada tabel `users`.
- `role`: `NOT NULL` dengan nilai default `'user'`.
- `password`: `NOT NULL`.

---

# 18. Foreign Key Behavior
- Tidak ada foreign key langsung pada tabel `users` untuk fitur autentikasi ini.

---

# 19. Duplicate Handling
- Pada login, duplikasi email di database dicegah oleh constraint `UNIQUE` di tabel `users`. Jika ada 2 input email yang sama di request, hanya 1 baris di DB yang cocok.

---

# 20. HTTP Contract
### 1. Tampilkan Halaman Login
- **Method & URI**: `GET /login`
- **Middleware**: `guest`
- **Response**: Render view `auth.login`.

### 2. Proses Login
- **Method & URI**: `POST /login`
- **Middleware**: `guest`
- **Request Payload**:
  - `_token`: string (CSRF)
  - `email`: string, required
  - `password`: string, required
  - `remember`: optional boolean
- **Success Response**: `302 Redirect` ke `/admin/users` jika `role == 'admin'`, atau `/workspaces` jika `role == 'user'`. Flash message: `"Berhasil masuk ke akun Anda."`
- **Validation Failure**: `302 Redirect Back` ke `/login` dengan session `$errors` dan input `email`.
- **Authentication Failure**: `302 Redirect Back` ke `/login` dengan `$errors->add('email', 'Kredensial yang dimasukkan tidak cocok.')`.

### 3. Proses Logout
- **Method & URI**: `POST /logout`
- **Middleware**: `auth`
- **Request Payload**:
  - `_token`: string (CSRF)
- **Success Response**: `302 Redirect` ke `/login`. Flash message: `"Anda telah keluar dari sistem."`

---

# 21. Controller Responsibilities
`app/Http/Controllers/AuthController.php`:
- `showLoginForm()`: Menampilkan view `auth.login`.
- `login(Request $request)`:
  1. Memvalidasi request email dan password.
  2. Mengeksekusi `Auth::attempt()`.
  3. Menangani kegagalan autentikasi dengan pesan yang aman.
  4. Me-regenerasi session ID `$request->session()->regenerate()`.
  5. Melakukan redirect sesuai peran user (`admin` vs `user`).
- `logout(Request $request)`:
  1. Menjalankan `Auth::logout()`.
  2. `$request->session()->invalidate()`.
  3. `$request->session()->regenerateToken()`.
  4. Redirect ke `/login`.

---

# 22. Model Responsibilities
`app/Models/User.php`:
- `$fillable`: `['name', 'email', 'password', 'role']`
- `$hidden`: `['password', 'remember_token']`
- `$casts`: `['password' => 'hashed']`
- Helper method:
  ```php
  public function isAdmin(): bool
  {
      return $this->role === 'admin';
  }
  ```

---

# 23. Authorization
- Route `/login` (GET dan POST) hanya boleh diakses oleh `guest`.
- Route `/logout` (POST) hanya boleh diakses oleh pengguna terautentikasi `auth`.
- Middleware kustom sederhana atau pengecekan di controller untuk route admin:
  - Buat middleware `EnsureUserIsAdmin` atau periksa `auth()->user()->isAdmin()` sebelum membuka area admin.

---

# 24. CSRF
- Semua form POST (`/login` dan `/logout`) wajib menyertakan direktif Blade `@csrf`.
- Form logout di navbar menggunakan POST terproteksi:
  ```html
  <form action="{{ route('logout') }}" method="POST">
      @csrf
      <button type="submit">Keluar</button>
  </form>
  ```

---

# 25. Mass Assignment Protection
- Pembuatan user awal atau seeder hanya menggunakan array field eksplisit `$fillable`.
- Input form login tidak menggunakan mass assignment ke database melainkan hanya dicocokkan via `Auth::attempt()`.

---

# 26. Error Handling
- Kredensial tidak valid: Mengembalikan error umum pada field email untuk mencegah enumerasi username/password secara spesifik.
- Sesi kedaluwarsa (*TokenMismatchException* / Error 419): Laravel akan menampilkan pesan 419 Page Expired atau me-redirect kembali ke `/login`.

---

# 27. Transactions
- Tidak diperlukan transaksi database untuk operasi login/logout murni.

---

# 28. Race Conditions
- Tidak ada isu race condition signifikan pada proses autentikasi session standar.

---

# 29. Delete Behavior
- Tidak ada operasi penghapusan data pada fitur autentikasi.

---

# 30. Empty State
- Tidak berlaku pada halaman login.

---

# 31. Pagination
- Tidak diperlukan pada fitur autentikasi.

---

# 32. Search / Filter
- Tidak diperlukan pada fitur autentikasi.

---

# 33. Accessibility Minimum
- Label eksplisit terhubung ke input dengan atribut `for="email"` dan `id="email"`.
- Error text menggunakan warna kontras merah dengan indikator teks jelas.
- Tombol submit memiliki label teks yang jelas ("Masuk").

---

# 34. Responsive Behavior
- Tampilan form login berada di tengah layar (*centered card*).
- Pada layar mobile (<640px): Card mengambil lebar penuh dengan margin horizontal (`w-full px-4`).
- Pada layar desktop: Card dibatasi maksimal lebar 448px (`max-w-md`).

---

# 35. Edge Cases
- Pengguna memasukkan spasi di awal/akhir email: Ditangani otomatis oleh `TrimStrings` middleware.
- Pengguna menekan tombol "Back" setelah logout: Session sudah dibatalkan; jika mengakses URL terproteksi, otomatis di-redirect kembali ke login.
- Huruf besar/kecil pada email: Validasi dan pencarian MySQL default bersifat case-insensitive, namun format email divalidasi dengan regex standar.

---

# 36. Security Checklist
- [x] CSRF protection aktif (`@csrf` di setiap form).
- [x] Password di-hash menggunakan algoritma Bcrypt/Argon2 standar Laravel.
- [x] Session ID diregenerasi setelah login sukses (`$request->session()->regenerate()`).
- [x] Sesi dihapus tuntas saat logout (`invalidate()` + `regenerateToken()`).
- [x] Input user ditampilkan dengan kurung kurawal ganda `{{ }}` untuk mitigasi XSS.
- [x] Tidak ada raw SQL concatenation.
- [x] Pesan kesalahan autentikasi tidak memberitahu apakah email atau password yang salah (mencegah user enumeration).

---

# 37. Testing Strategy
## Happy Path
- Kunjungi `/login`, isi `admin@jara.local` dan `password`, klik submit. Sistem berhasil redirect ke `/admin/users` dan session terisi.
- Klik tombol Logout di navbar, sistem redirect ke `/login` dan session terhapus.

## Validation Tests
- Submit form kosong -> Validasi browser mencegah submit atau backend mengembalikan error *"The email field is required."* dan *"The password field is required."*.
- Masukkan format email salah `user@` -> Gagal validasi format email.

## Security Tests
- Coba akses `/workspaces` tanpa login -> Ter-redirect ke `/login`.
- Masukkan password salah -> Kredensial ditolak dengan pesan ramah.
- Submit POST `/login` tanpa token CSRF -> Muncul HTTP 419 Page Expired.

## UI Tests / Manual Checks
- Kotak input memiliki outline fokus biru.
- Pesan kesalahan tampil berwarna merah tepat di bawah kolom yang keliru.

---

# 38. Acceptance Criteria
- [ ] Tersedia migrasi yang menyertakan kolom `role` (enum `'admin'`, `'user'`) di tabel `users`.
- [ ] Database seeder berhasil membuat akun default `admin@jara.local` dengan password `password`.
- [ ] Form login dapat diakses di `/login` oleh pengguna yang belum login.
- [ ] Pengguna yang berhasil login sebagai `admin` diarahkan ke `/admin/users`.
- [ ] Pengguna yang berhasil login sebagai `user` diarahkan ke `/workspaces`.
- [ ] Kredensial salah menghasilkan pesan error tanpa membocorkan eksistensi akun.
- [ ] Tombol logout berhasil mengeluarkan pengguna dari sesi aktif.

---

# 39. Definition of Done
- Route `/login` (GET, POST) dan `/logout` (POST) terdaftar dan berfungsi normal.
- Middleware auth bekerja mengunci halaman internal.
- View Blade `auth/login.blade.php` tampil rapi dan responsif dengan Tailwind CSS.
- Seeder berhasil dijalankan tanpa error di container Docker.
- Tidak ada pesan debug atau stack trace terbuka saat login gagal.

---

# 40. Implementation Order Inside Feature
1. Modifikasi migrasi `create_users_table` untuk menambahkan kolom `role` (`enum('admin', 'user')->default('user')`).
2. Update model `User.php` (tambahkan `role` ke `$fillable` dan helper `isAdmin()`).
3. Buat seeder akun default Admin di `database/seeders/DatabaseSeeder.php`.
4. Definisikan routes di `routes/web.php` (`/login`, `/logout`).
5. Buat `AuthController.php` beserta method `showLoginForm`, `login`, dan `logout`.
6. Buat middleware `EnsureUserIsAdmin.php` atau proteksi role di controller.
7. Buat view Blade `resources/views/auth/login.blade.php` dengan Tailwind CSS.
8. Uji coba flow login admin, user, dan logout.

---

# 41. Estimated 60-Minute Breakdown
- **00–10 menit**: Update migrasi users (`role`), seeder admin default, dan update model `User`.
- **10–25 menit**: Setup routes dan logika di `AuthController` (login, logout, role redirect).
- **25–45 menit**: Pembuatan view Blade `login.blade.php` (form, error container, styling Tailwind).
- **45–55 menit**: Setup middleware proteksi guest & auth serta testing skenario gagal dan sukses.
- **55–60 menit**: Verifikasi checklist security dan perapihan kode.

---

# 42. Explicit Non-Requirements
- Tidak membuat endpoint REST API / JWT token.
- Tidak membuat form registrasi mandiri publik.
- Tidak membuat fitur forgot/reset password email.
- Tidak menggunakan SPA atau JavaScript framework tambahan.
