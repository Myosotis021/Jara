# FILE: prd-kelola-user-2.md

# 1. Feature Overview
- **Nama Fitur**: Manajemen Pengguna oleh Administrator (Admin User Management)
- **Tujuan Fitur**: Memberikan wewenang penuh kepada administrator untuk menambah akun pengguna baru, melihat daftar seluruh pengguna terdaftar, dan menghapus akun pengguna dari sistem Jara.
- **Masalah yang Diselesaikan**: Mencegah pendaftaran sembarangan oleh publik (*closed user registration*), memastikan hanya pengguna yang sah (mahasiswa/dosen/staf/tim proyek) yang memiliki akun untuk berkolaborasi di dalam sistem.
- **Pengguna/Aktor**: Administrator (`admin`).
- **Dependency terhadap PRD Lain**: Bergantung pada `prd-autentikasi-1.md` (membutuhkan sesi login dan verifikasi peran `admin`).
- **Alasan Urutan Implementasi**: Berada pada Urutan 2 karena fungsionalitas ini memerlukan sistem autentikasi dan otorisasi admin yang telah berfungsi. Fitur ini dapat dikerjakan secara paralel dengan fitur manajemen workspace (`prd-kelola-workspace-2.md`).

---

# 2. User Story
- Sebagai **Administrator**, saya ingin mendaftarkan pengguna baru (nama, email, kata sandi, dan peran), sehingga anggota tim/mahasiswa baru dapat memperoleh akun untuk masuk ke Jara.
- Sebagai **Administrator**, saya ingin melihat daftar seluruh akun pengguna di sistem, sehingga saya dapat memantau siapa saja yang memiliki akses.
- Sebagai **Administrator**, saya ingin menghapus akun pengguna yang sudah tidak aktif atau menyalahi aturan, sehingga hak akses pengguna tersebut dicabut sepenuhnya.

---

# 3. Scope
## MVP / Required
- Halaman daftar pengguna (`/admin/users`) dengan tabel: Nama, Email, Peran (`admin` atau `user`), Tanggal Dibuat, dan Aksi.
- Form tambah pengguna baru dengan field: `name`, `email`, `role`, dan `password` (beserta konfirmasi password).
- Validasi input (email unik, password minimal 6 atau 8 karakter, nama wajib).
- Fitur hapus akun pengguna dengan validasi keamanan (Admin tidak dapat menghapus akun miliknya sendiri).
- Proteksi route khusus role `admin`.

## If Time Permits
- Opsi reset/ganti password pengguna oleh admin.
- Fitur pencarian pengguna sederhana berdasarkan nama atau email.

## Out of Scope
- Registrasi mandiri publik.
- Aktivasi akun via link email / verifikasi OTP.
- Manajemen hak akses modular/permission matriks yang kompleks.

---

# 4. Preconditions
- Fitur `prd-autentikasi-1.md` sudah selesai diimplementasikan.
- Admin sudah login ke dalam sistem.
- Middleware proteksi peran admin sudah aktif.

---

# 5. Main User Flow
### Flow Menambah Pengguna:
1. Admin membuka menu "Kelola Pengguna" (`GET /admin/users`).
2. Halaman menampilkan daftar pengguna yang ada dan tombol "+ Tambah Pengguna".
3. Admin menekan tombol "+ Tambah Pengguna", sistem menampilkan modal atau halaman form tambah pengguna (`GET /admin/users/create` atau form di halaman yang sama).
4. Admin mengisi: Nama Lengkap, Email, Peran (`User` atau `Admin`), dan Password awal.
5. Admin menekan tombol "Simpan Pengguna".
6. Browser mengirimkan HTTP request `POST /admin/users` dengan token CSRF.
7. Backend memvalidasi input data pengguna.
8. Backend mengenkripsi password dengan `Hash::make()` dan menyimpan data ke tabel `users`.
9. Backend me-redirect kembali ke `/admin/users` dengan membawa flash message sukses *"Pengguna [Nama] berhasil didaftarkan."*.
10. Pengguna baru langsung muncul di tabel daftar pengguna.

### Flow Menghapus Pengguna:
1. Admin melihat baris pengguna yang ingin dihapus pada tabel di `/admin/users`.
2. Admin menekan tombol "Hapus".
3. Browser memunculkan dialog konfirmasi bawaan (*confirm dialog*).
4. Jika disetujui, browser mengirimkan HTTP request `DELETE /admin/users/{id}` dengan token CSRF.
5. Backend memeriksa apakah ID yang dihapus adalah akun admin yang sedang login. Jika ya, batalkan aksi dan kirim pesan peringatan.
6. Jika bukan, backend menghapus baris pengguna dari tabel `users`.
7. Backend me-redirect kembali ke `/admin/users` dengan flash message sukses *"Pengguna berhasil dihapus."*.

---

# 6. Alternative Flow
- **Email Sudah Terdaftar**:
  - Admin memasukkan email yang sudah ada di database.
  - Backend menolak dengan validation error pada field email: *"Email ini sudah terdaftar di sistem."*.
  - Form kembali menampilkan nilai lama (`old()`) kecuali password.
- **Admin Mencoba Menghapus Akun Sendiri**:
  - Admin menekan tombol hapus pada baris akunnya sendiri.
  - Backend mendeteksi `$user->id === auth()->id()`.
  - Backend membatalkan penghapusan dan me-redirect dengan flash error: *"Anda tidak dapat menghapus akun Anda sendiri."*.
- **Akses oleh Pengguna Biasa**:
  - Pengguna dengan role `user` mencoba membuka URL `/admin/users`.
  - Sistem mengembalikan HTTP 403 Forbidden atau me-redirect ke `/workspaces` dengan flash error *"Akses ditolak. Khusus Administrator."*.

---

# 7. Low-Fidelity UI Design
```text
+-------------------------------------------------------------------------------+
| JARA  |  [Workspaces]  [Kelola Pengguna (Admin)]         Halo, Admin [Logout] |
+-------------------------------------------------------------------------------+
|                                                                               |
| Manajemen Pengguna                                      [+ Tambah Pengguna]   |
| [ Flash Alert: Sukses / Error ]                                               |
|                                                                               |
| +---------------------------------------------------------------------------+ |
| | Nama              | Email             | Peran | Terdaftar   | Aksi        | |
| +---------------------------------------------------------------------------+ |
| | Admin Jara        | admin@jara.local  | Admin | 10 Jan 2026 | -           | |
| | Budi Santoso      | budi@kampus.ac.id | User  | 11 Jan 2026 | [Hapus]     | |
| | Siti Rahma        | siti@kampus.ac.id | User  | 11 Jan 2026 | [Hapus]     | |
| +---------------------------------------------------------------------------+ |
|                                                                               |
+-------------------------------------------------------------------------------+

Form Tambah Pengguna (/admin/users/create):
+-------------------------------------------------------------+
| Tambah Pengguna Baru                                        |
+-------------------------------------------------------------+
| Nama Lengkap                                                |
| [_________________________________________________________] |
| [ Error: nama ]                                             |
|                                                             |
| Alamat Email                                                |
| [_________________________________________________________] |
| [ Error: email ]                                            |
|                                                             |
| Peran                                                       |
| [ User                                                   v] |
| [ Error: role ]                                             |
|                                                             |
| Kata Sandi Awal                                             |
| [_________________________________________________________] |
| [ Error: password ]                                         |
|                                                             |
| [ Batal ]                                 [ Simpan Pengguna ]|
+-------------------------------------------------------------+
```

---

# 8. Visual Design & Tailwind Guidance
- **Page Container**: `max-w-6xl mx-auto px-4 py-8`
- **Header Section**: `flex justify-between items-center mb-6`
  - Title: `text-2xl font-bold text-gray-800`
  - Primary Button ("+ Tambah Pengguna"): `bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition`
- **Table Card**: `bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden`
  - Table Head: `bg-gray-50 text-gray-500 uppercase text-xs font-semibold tracking-wider`
  - Table Body Rows: `divide-y divide-gray-200 text-sm text-gray-700`
  - Role Badges:
    - Admin: `px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800`
    - User: `px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800`
- **Action Buttons**:
  - Hapus (Destructive): `text-red-600 hover:text-red-800 text-xs font-semibold py-1 px-2.5 rounded hover:bg-red-50 transition`
- **Alert Messages**:
  - Success: `bg-green-50 border border-green-200 text-green-700 text-sm p-3 rounded-lg mb-4`
  - Error: `bg-red-50 border border-red-200 text-red-700 text-sm p-3 rounded-lg mb-4`

---

# 9. UI States
- **Default State**: Tabel menampilkan daftar akun pengguna yang terdaftar.
- **Empty State**: Jika belum ada pengguna selain admin, baris tabel menampilkan pesan khusus (*"Belum ada pengguna tambahan. Klik Tambah Pengguna untuk mendaftarkan akun baru."*).
- **Submitting State**: Native browser submission saat tombol "Simpan Pengguna" ditekan.
- **Validation Error State**: Form tambah pengguna menampilkan border merah dan pesan error tepat di bawah field yang tidak valid.
- **Success State**: Pasca penambahan atau penghapusan pengguna, halaman me-refresh ke tabel dengan flash message hijau.
- **Disabled State**: Tombol hapus pada akun diri sendiri (admin aktif) dinonaktifkan atau disembunyikan untuk mencegah *accidental self-deletion*.
- **Loading State**: Tidak diperlukan karena navigasi server-rendered Blade penuh.

---

# 10. Error Container Specification
- **Field-level Error**:
  - Field `name`: Tepat di bawah input nama (`@error('name')`).
  - Field `email`: Tepat di bawah input email (`@error('email')`).
  - Field `role`: Tepat di bawah dropdown peran (`@error('role')`).
  - Field `password`: Tepat di bawah input password (`@error('password')`).
  - Styling: `text-xs text-red-600 mt-1 font-medium`.
- **Form-level Flash Error**:
  - Di atas form atau tabel jika ada kegagalan otorisasi atau operasi database:
    ```html
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-4">
            {{ session('error') }}
        </div>
    @endif
    ```
- **Delete Confirmation**:
  - Menggunakan dialog native JavaScript `onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun pengguna ini? Seluruh data yang terkait akan terpengaruh.')"` pada form delete.

---

# 11. Form Specification
### 1. Nama Pengguna
- HTML: `type="text"`
- Name: `name`
- Frontend: `required`, `maxlength="255"`, `placeholder="Misal: Budi Santoso"`
- Backend: `required|string|max:255`
- Database: `VARCHAR(255) NOT NULL`
- Old input: `value="{{ old('name') }}"`

### 2. Alamat Email
- HTML: `type="email"`
- Name: `email`
- Frontend: `required`, `maxlength="255"`, `placeholder="user@kampus.ac.id"`
- Backend: `required|string|email|max:255|unique:users,email`
- Database: `VARCHAR(255) NOT NULL UNIQUE`
- Old input: `value="{{ old('email') }}"`

### 3. Peran (Role)
- HTML: `<select>`
- Name: `role`
- Options: `'user'` (Default, label: "Pengguna / Anggota"), `'admin'` (label: "Administrator")
- Backend: `required|in:admin,user`
- Database: `ENUM('admin', 'user') NOT NULL`
- Old input: Dipilih sesuai `old('role', 'user')`

### 4. Password Awal
- HTML: `type="password"`
- Name: `password`
- Frontend: `required`, `minlength="6"`
- Backend: `required|string|min:6`
- Database: `VARCHAR(255) NOT NULL` (Hashed)
- Old input: Dikosongkan demi keamanan.

---

# 12. Frontend Validation
- Atribut `required` pada field nama, email, role, dan password.
- Atribut `type="email"` pada input email.
- Atribut `minlength="6"` pada input password.

---

# 13. Backend Validation
Validation Rules pada `AdminUserController@store`:
```php
$validated = $request->validate([
    'name' => ['required', 'string', 'max:255'],
    'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
    'role' => ['required', 'in:admin,user'],
    'password' => ['required', 'string', 'min:6'],
]);
```

---

# 14. Input Sanitization & XSS Prevention
- Nama dan email pengguna ditampilkan menggunakan `{{ $user->name }}` dan `{{ $user->email }}` pada Blade.
- Field string di-trim otomatis oleh middleware Laravel.
- Tidak pernah menggunakan `{!! $user->name !!}`.

---

# 15. SQL Injection Prevention
- Seluruh query menggunakan Eloquent ORM: `User::latest()->get()`, `User::create(...)`, `$user->delete()`.
- Pengecekan keunikan email menggunakan validator internal `unique:users,email` yang memanfaatkan prepared statements.

---

# 16. Database Design
Menggunakan tabel `users` (didefinisikan pada `prd-autentikasi-1.md`):
- `id` (BIGINT UNSIGNED, PK)
- `name` (VARCHAR(255), NOT NULL)
- `email` (VARCHAR(255), NOT NULL, UNIQUE)
- `password` (VARCHAR(255), NOT NULL)
- `role` (ENUM('admin', 'user'), NOT NULL, DEFAULT 'user')
- `created_at`, `updated_at` (TIMESTAMP)

---

# 17. Database Constraints
- `email`: `UNIQUE` index pada tabel `users`.
- `name`, `email`, `password`, `role`: `NOT NULL`.

---

# 18. Foreign Key Behavior
- Pengguna yang dihapus akan memicu penghapusan cascade pada relasi workspace dan keanggotaan (dijelaskan detail di `prd-kelola-workspace-2.md` dan `prd-kolaborasi-workspace-3.md`).
- Di controller disediakan pengecekan tambahan agar admin tidak bisa menghapus diri sendiri.

---

# 19. Duplicate Handling
- **Definisi Duplikat**: Email pengguna tidak boleh sama dengan akun lain yang sudah terdaftar.
- **Backend Validation**: `'email' => 'unique:users,email'`.
- **Database Constraint**: `UNIQUE (email)` pada tabel `users`.
- **Error Message**: *"Email ini sudah terdaftar di sistem."*

---

# 20. HTTP Contract
### 1. Daftar Pengguna
- **Method & URI**: `GET /admin/users`
- **Middleware**: `auth`, `can:admin` (atau middleware `isAdmin`)
- **Response**: Render view `admin.users.index` dengan data `$users`.

### 2. Form Tambah Pengguna
- **Method & URI**: `GET /admin/users/create`
- **Response**: Render view `admin.users.create`.

### 3. Simpan Pengguna Baru
- **Method & URI**: `POST /admin/users`
- **Request Payload**:
  - `_token`: string (CSRF)
  - `name`: string, required, max 255
  - `email`: string, required, email, unique:users,email
  - `role`: string, required, in:admin,user
  - `password`: string, required, min:6
- **Success Response**: `302 Redirect` ke `/admin/users` dengan flash success: *"Pengguna berhasil ditambahkan."*
- **Validation Failure**: `302 Redirect Back` dengan session `$errors` dan old input.

### 4. Hapus Pengguna
- **Method & URI**: `DELETE /admin/users/{user}`
- **Request Payload**:
  - `_token`: string (CSRF)
  - `_method`: `DELETE`
- **Success Response**: `302 Redirect` ke `/admin/users` dengan flash success: *"Pengguna berhasil dihapus."*
- **Rejection (Self-deletion)**: `302 Redirect` ke `/admin/users` dengan flash error: *"Anda tidak dapat menghapus akun Anda sendiri."*

---

# 21. Controller Responsibilities
`app/Http/Controllers/Admin/UserController.php`:
- `index()`: Mengambil list seluruh user (`User::orderBy('name')->get()`) dan menampilkan view index.
- `create()`: Menampilkan view form tambah user.
- `store(Request $request)`:
  1. Validasi input (`name`, `email`, `role`, `password`).
  2. Hash password menggunakan `Hash::make()`.
  3. Simpan user baru via `User::create(...)`.
  4. Redirect dengan flash success message.
- `destroy(User $user)`:
  1. Validasi proteksi diri: pastikan `$user->id !== auth()->id()`.
  2. Panggil `$user->delete()`.
  3. Redirect dengan flash success message.

---

# 22. Model Responsibilities
`app/Models/User.php`:
- Mass assignment attributes: `$fillable = ['name', 'email', 'password', 'role']`.
- Casts: `'password' => 'hashed'`.
- Scopes/Helpers:
  ```php
  public function scopeUsersOnly($query)
  {
      return $query->where('role', 'user');
  }
  ```

---

# 23. Authorization
- Seluruh endpoint di bawah prefix `/admin/*` wajib melewati middleware yang memeriksa apakah pengguna ber-role `admin`.
- Jika bukan admin: kirim HTTP 403 Forbidden atau redirect ke `/workspaces` dengan pesan peringatan.

---

# 24. CSRF
- Form tambah user menggunakan method `POST` dengan direktif `@csrf`.
- Form hapus user menggunakan method spoofing:
  ```html
  <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus?')">
      @csrf
      @method('DELETE')
      <button type="submit" class="text-red-600 hover:underline">Hapus</button>
  </form>
  ```

---

# 25. Mass Assignment Protection
- Menggunakan data hasil `$request->validate()` yang diekstrak secara spesifik:
  ```php
  User::create([
      'name' => $validated['name'],
      'email' => $validated['email'],
      'role' => $validated['role'],
      'password' => Hash::make($validated['password']),
  ]);
  ```
- Jangan gunakan `User::create($request->all())`.

---

# 26. Error Handling
- Validasi gagal: Form otomatis me-redirect balik dengan old input dan error container di bawah masing-masing input.
- User ID tidak ditemukan pada route model binding (`/admin/users/{user}`): Laravel otomatis menampilkan HTTP 404 Not Found.
- Self-deletion dicegah dengan flash error yang jelas dan aman.

---

# 27. Transactions
- Operasi pembuatan dan penghapusan 1 entitas user sederhana tidak memerlukan transaksi database eksplisit.

---

# 28. Race Conditions
- Jika 2 admin secara bersamaan mendaftarkan email yang sama: Constraint `UNIQUE` pada kolom `email` di level MySQL akan menolak eksekusi kedua secara atomik.

---

# 29. Delete Behavior
- **Strategi**: Hard delete menggunakan `$user->delete()`.
- **Konfirmasi**: Native JavaScript confirm pop-up saat tombol hapus diklik.
- **Relasi**: Menghapus user akan otomatis menghapus keterikatan pada workspace yang dia miliki atau ikuti melalui foreign key `ON DELETE CASCADE`.

---

# 30. Empty State
- Jika tabel user hanya berisi 1 akun (hanya admin yang sedang login):
  - Tampilkan pesan: *"Belum ada pengguna lain yang terdaftar. Klik tombol Tambah Pengguna di atas untuk mendaftarkan mahasiswa atau rekan kerja."*

---

# 31. Pagination
- Pada tahap praktikum 60 menit dengan jumlah user terbatas (<50 orang), list ditampilkan sekaligus (`User::all()`) atau menggunakan pagination sederhana `User::paginate(15)`.

---

# 32. Search / Filter
- Tidak diwajibkan pada MVP untuk menghemat waktu pengerjaan 60 menit. Diberikan sebagai opsi *If Time Permits*.

---

# 33. Accessibility Minimum
- Label form eksplisit dengan atribut `for=""` yang cocok dengan `id=""`.
- Tombol aksi hapus memiliki teks yang jelas ("Hapus Pengguna").
- Dialog konfirmasi native memberikan kesempatan pengguna membatalkan aksi yang tidak sengaja tertekan.

---

# 34. Responsive Behavior
- Tampilan tabel dibungkus dengan wrapper `overflow-x-auto` agar tidak terpotong pada layar mobile/tablet.
- Form tambah dibatasi lebar `max-w-xl` di tengah layar.

---

# 35. Edge Cases
- Admin menghapus akunnya sendiri: Diblokir di controller dengan peringatan.
- Email dimasukkan dengan huruf kapital/spasi: Otomatis di-trim oleh middleware, divalidasi format email.
- Password kurang dari 6 karakter: Ditolak oleh backend validator.

---

# 36. Security Checklist
- [x] Hanya pengguna ber-role `admin` yang dapat mengakses route `/admin/users*`.
- [x] Email diverifikasi unik baik di backend validation maupun database constraint.
- [x] Password disimpan dalam bentuk hash (`Hash::make`).
- [x] Proteksi CSRF aktif pada request POST dan DELETE.
- [x] Pencegahan penghapusan akun diri sendiri (*self-deletion protection*).
- [x] Output nama dan email di-escape (`{{ }}`) untuk mencegah XSS.

---

# 37. Testing Strategy
## Happy Path
- Admin membuka `/admin/users/create`, mengisi form dengan data valid (`name: Budi`, `email: budi@test.local`, `password: secret123`), submit. Sistem redirect ke index dengan flash success dan data Budi muncul di tabel.
- Admin mengklik tombol Hapus pada baris Budi, konfirmasi disetujui, akun Budi terhapus dari tabel.

## Validation Tests
- Submit email yang sudah ada -> Muncul error *"The email has already been taken."*.
- Submit password 3 karakter -> Muncul error *"The password must be at least 6 characters."*.

## Security Tests
- Login sebagai pengguna biasa (`role: user`), coba akses URL `/admin/users` -> Sistem menolak dengan HTTP 403 atau me-redirect kembali.
- Coba submit request DELETE untuk akun admin yang sedang login -> Muncul flash error pencegahan self-delete.

---

# 38. Acceptance Criteria
- [ ] Route `/admin/users` hanya dapat diakses oleh akun admin.
- [ ] Admin dapat melihat daftar semua user yang terdaftar.
- [ ] Admin dapat membuat akun baru dengan mengisi nama, email, role, dan password.
- [ ] Akun baru dapat digunakan untuk login ke sistem Jara.
- [ ] Admin dapat menghapus akun user lain.
- [ ] Admin dicegah menghapus akunnya sendiri.
- [ ] Konfirmasi dialog tampil sebelum proses hapus dieksekusi.

---

# 39. Definition of Done
- Controller `AdminUserController` terimplementasi dengan method `index`, `create`, `store`, dan `destroy`.
- View `admin/users/index.blade.php` dan `admin/users/create.blade.php` tersedia dan berpenampilan bersih dengan Tailwind CSS.
- Route terdaftar dengan rapi di `routes/web.php` di bawah grup middleware admin.
- Berhasil diuji coba manual di browser tanpa error.

---

# 40. Implementation Order Inside Feature
1. Daftarkan routes grup admin di `routes/web.php`.
2. Buat controller `Admin/UserController.php`.
3. Implementasikan method `index()` dan view `admin/users/index.blade.php`.
4. Implementasikan method `create()`, `store()`, dan view `admin/users/create.blade.php`.
5. Implementasikan method `destroy()` dengan pengecekan `$user->id !== auth()->id()`.
6. Tambahkan pesan flash notifikasi di master layout / view.
7. Uji coba pembuatan user dan login menggunakan akun yang baru dibuat.

---

# 41. Estimated 60-Minute Breakdown
- **00–10 menit**: Routes admin & kerangka `AdminUserController`.
- **10–25 menit**: View form tambah user & method `store` (validasi + hash password).
- **25–40 menit**: View tabel list user & integrasi flash notification.
- **40–50 menit**: Method `destroy` + proteksi pencegahan self-deletion + konfirmasi dialog.
- **50–60 menit**: Pengujian hak akses (admin vs regular user) dan testing validasi.

---

# 42. Explicit Non-Requirements
- Tidak ada fitur upload avatar/foto profil user.
- Tidak ada fitur kirim email aktivasi/verifikasi SMTP.
- Tidak ada fitur import/export user dari berkas Excel/CSV.
- Tidak ada sistem log audit aktivitas admin.
