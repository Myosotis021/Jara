# FILE: prd-kolaborasi-workspace-3.md

# 1. Feature Overview
- **Nama Fitur**: Kolaborasi Workspace & Manajemen Anggota Tim (Workspace Collaboration)
- **Tujuan Fitur**: Memungkinkan pemilik workspace untuk mengundang pengguna lain yang telah terdaftar di sistem Jara agar bergabung ke dalam workspace yang sama, melihat tugas-tugas yang ada, serta memperbarui status pengerjaan secara bersama-sama.
- **Masalah yang Diselesaikan**: Menghilangkan hambatan koordinasi tugas tim, memungkinkan pembagian kerja pada tugas bersama (seperti tugas kuliah kelompok atau proyek web tim) tanpa perlu saling bertukar file secara manual atau membuat daftar ganda.
- **Pengguna/Aktor**: Pemilik Workspace (*Owner*) dan Anggota Terdaftar (*Members*).
- **Dependency terhadap PRD Lain**: Bergantung pada `prd-kelola-workspace-2.md` dan `prd-kelola-user-2.md`.
- **Alasan Urutan Implementasi**: Berada pada Urutan 3 karena membutuhkan keberadaan akun pengguna terdaftar dan entitas workspace yang telah dibuat. Fitur ini dapat dikerjakan secara paralel dengan fitur manajemen tugas (`prd-kelola-tugas-3.md`).

---

# 2. User Story
- Sebagai **Pemilik Workspace**, saya ingin mengundang pengguna terdaftar lain (rekan tim) ke dalam workspace saya, sehingga kami dapat mengelola dan mengerjakan tugas-tugas bersama.
- Sebagai **Pemilik Workspace**, saya ingin melihat daftar seluruh anggota yang tergabung di dalam workspace saya dan dapat mengeluarkan anggota jika sudah tidak terlibat, sehingga daftar kolaborator selalu relevan.
- Sebagai **Anggota Tim**, saya ingin melihat workspace tempat saya diundang dan dapat berkolaborasi di dalamnya, sehingga saya mengetahui tugas-tugas yang perlu diselesaikan.

---

# 3. Scope
## MVP / Required
- Migrasi tabel pivot `workspace_members`:
  - `id`, `workspace_id`, `user_id`, `created_at`, `updated_at`.
  - Constraint `UNIQUE(workspace_id, user_id)`.
- Bagian Kelola Anggota di halaman workspace (`/workspaces/{workspace}` atau sub-menu anggota):
  - Daftar anggota saat ini (Nama, Email, Tanggal Bergabung, dan Peran: *Pemilik* vs *Anggota*).
  - Form undang anggota: Memilih pengguna terdaftar dari dropdown atau memasukkan email pengguna terdaftar.
- Aksi tambah anggota (`POST /workspaces/{workspace}/members`).
- Aksi hapus anggota (`DELETE /workspaces/{workspace}/members/{user}`) yang hanya dapat dilakukan oleh pemilik workspace.
- Validasi: Tidak bisa mengundang diri sendiri, tidak bisa mengundang pengguna yang sudah menjadi anggota, hanya pengguna terdaftar yang dapat diundang.

## If Time Permits
- Fitur anggota keluar sendiri (*Leave Workspace*).

## Out of Scope
- Pengiriman undangan via link token email / link publik.
- Sistem role bertingkat dalam workspace (seperti *admin workspace*, *editor*, *viewer*). Cukup biner: *Pemilik (Owner)* dan *Anggota (Member)*.
- Pembatasan kuota maksimal anggota tim.

---

# 4. Preconditions
- Fitur `prd-autentikasi-1.md` dan `prd-kelola-workspace-2.md` sudah selesai.
- Pengguna-pengguna lain telah dibuatkan akunnya oleh Admin di sistem (`prd-kelola-user-2.md`).
- Pengguna yang melakukan undangan adalah pemilik sah dari workspace tersebut.

---

# 5. Main User Flow
### Flow Mengundang Anggota:
1. Pemilik workspace membuka detail workspace miliknya (`GET /workspaces/{workspace}`).
2. Pada panel samping (*sidebar*) atau tab "Anggota Tim", pemilik melihat form "Undang Anggota".
3. Pemilik memilih nama/email pengguna terdaftar yang belum bergabung (atau mengetikkan alamat email pengguna).
4. Pemilik menekan tombol "Tambah ke Workspace".
5. Browser mengirimkan HTTP request `POST /workspaces/{workspace}/members` dengan token CSRF.
6. Backend memverifikasi bahwa pengirim request adalah pemilik workspace.
7. Backend memvalidasi bahwa email/user ID yang dimasukkan ada di database dan belum terdaftar di workspace tersebut.
8. Backend menyisipkan relasi baru ke tabel `workspace_members`.
9. Backend me-redirect kembali ke halaman workspace dengan pesan flash sukses *"Pengguna [Nama] berhasil ditambahkan ke workspace."*.
10. Ketika anggota yang diundang tersebut login, workspace ini otomatis muncul di bagian "Shared with Me" pada halaman daftar workspacenya.

### Flow Mengeluarkan Anggota:
1. Pemilik workspace melihat daftar anggota pada tabel anggota tim.
2. Pemilik menekan tombol "Keluarkan" di samping nama anggota terkait.
3. Muncul konfirmasi native browser.
4. Browser mengirimkan HTTP request `DELETE /workspaces/{workspace}/members/{user}` dengan CSRF.
5. Backend memastikan pemohon adalah pemilik dan target bukan pemilik itu sendiri.
6. Record keanggotaan dihapus dari tabel `workspace_members`.
7. Backend me-redirect kembali dengan pesan flash sukses *"Anggota berhasil dikeluarkan dari workspace."*.

---

# 6. Alternative Flow
- **Mengundang Diri Sendiri**:
  - Pemilik memilih akunnya sendiri untuk diundang.
  - Backend menolak dengan error: *"Anda adalah pemilik workspace ini."*.
- **Pengguna Sudah Menjadi Anggota**:
  - Pemilik mencoba mengundang pengguna yang sudah terdaftar di workspace tersebut.
  - Backend validator menolak dengan pesan: *"Pengguna ini sudah menjadi anggota workspace."*.
- **Email Pengguna Tidak Ditemukan**:
  - Memasukkan email yang belum terdaftar di sistem.
  - Backend menolak dengan pesan: *"Pengguna dengan email tersebut tidak ditemukan di sistem Jara."*.
- **Bukan Pemilik Mencoba Mengundang**:
  - Anggota biasa mencoba mengirimkan POST request untuk mengundang orang lain.
  - Backend memblokir dengan status HTTP 403 Forbidden.

---

# 7. Low-Fidelity UI Design
```text
+-------------------------------------------------------------------------------+
| JARA  |  [< Kembali ke Workspaces]                       Halo, Budi  [Logout] |
+-------------------------------------------------------------------------------+
|                                                                               |
| Workspace: Projek Web Kita                                                    |
|                                                                               |
| +--- Tab / Seksi Anggota Kolaborasi ----------------------------------------+ |
| |                                                                           | |
| | UNDANG ANGGOTA BARU                                                       | |
| | Pilih Pengguna Terdaftar:                                                 | |
| | [ Siti Rahma (siti@kampus.ac.id)                                        v]| |
| | [ Error: pengguna sudah menjadi anggota ]                                 | |
| |                                                [ + Undang ke Workspace ]  | |
| |                                                                           | |
| | DAFTAR ANGGOTA TIM (Total: 2 Orang)                                       | |
| | +-----------------------------------------------------------------------+ | |
| | | Nama             | Email             | Peran    | Aksi                | | |
| | +-----------------------------------------------------------------------+ | |
| | | Budi Santoso     | budi@kampus.ac.id | Pemilik  | -                   | | |
| | | Siti Rahma       | siti@kampus.ac.id | Anggota  | [Keluarkan]         | | |
| | +-----------------------------------------------------------------------+ | |
| +---------------------------------------------------------------------------+ |
|                                                                               |
+-------------------------------------------------------------------------------+
```

---

# 8. Visual Design & Tailwind Guidance
- **Member Section Container**: `bg-white p-5 rounded-xl border border-gray-200 shadow-sm mt-8`
- **Invite Form Layout**: `flex flex-col sm:flex-row gap-3 items-end mb-6`
  - Select Dropdown: `w-full sm:w-80 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500`
  - Invite Button: `bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition whitespace-nowrap`
- **Member Table / List**:
  - Head: `text-xs font-semibold text-gray-500 uppercase bg-gray-50 p-2.5 rounded-lg`
  - Badges:
    - Pemilik: `px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-800`
    - Anggota: `px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800`
  - Remove Action: `text-red-600 hover:text-red-800 text-xs font-medium hover:underline`

---

# 9. UI States
- **Default State**: Menampilkan dropdown daftar calon anggota yang bisa diundang dan tabel anggota aktif.
- **Empty State (Hanya Pemilik)**: Tabel menampilkan pemilik dan baris informasi *"Belum ada anggota tim lain. Undang rekan Anda untuk mulai berkolaborasi."*.
- **Submitting State**: Native form submit browser saat tombol undang ditekan.
- **Validation Error State**: Menampilkan teks error merah di bawah dropdown input.
- **Success State**: Anggota baru langsung tampil di tabel setelah redirect dengan flash banner hijau.
- **Non-Owner View**: Jika yang membuka halaman adalah anggota (bukan pemilik), form undang dan tombol keluarkan disembunyikan (hanya bisa melihat daftar rekan se-tim).

---

# 10. Error Container Specification
- **Field-level Error**:
  - Field `user_id` / `email`:
    ```html
    @error('user_id')
        <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
    @enderror
    ```
- **Flash Messages**:
  - Menggunakan flash notification standar di atas konten halaman.
- **Delete Confirmation**:
  - `onsubmit="return confirm('Apakah Anda yakin ingin mengeluarkan anggota ini dari workspace?')"`

---

# 11. Form Specification
### 1. User ID / Email Pengguna yang Diundang
- HTML: `<select>` atau `type="email"`
- Name: `user_id` (jika menggunakan dropdown) atau `email` (jika input ketik)
  - *Rekomendasi untuk praktikum 60 menit*: Menggunakan dropdown `<select name="user_id">` yang menampilkan daftar pengguna yang belum bergabung. Ini meminimalkan kesalahan ketik pengguna.
- Frontend: `required`
- Backend: `required|exists:users,id`
- Database: `BIGINT UNSIGNED` mengarah ke `users.id`
- Old input: `old('user_id')`

---

# 12. Frontend Validation
- Atribut `required` pada elemen `<select>`.
- Dropdown default menampilkan opsi: `-- Pilih Pengguna --` dengan nilai kosong.

---

# 13. Backend Validation
Validation Rules pada `WorkspaceMemberController@store`:
```php
$validated = $request->validate([
    'user_id' => [
        'required',
        'exists:users,id',
        Rule::notIn([$workspace->user_id]), // Tidak boleh mengundang pemilik sendiri
    ],
]);
```
- Pengecekan tambahan sebelum insert:
```php
if ($workspace->members()->where('user_id', $validated['user_id'])->exists()) {
    return back()->withErrors(['user_id' => 'Pengguna ini sudah menjadi anggota workspace.']);
}
```

---

# 14. Input Sanitization & XSS Prevention
- Nama dan email anggota pada tabel dirender menggunakan sintaks Blade kurung kurawal ganda `{{ $member->name }}` dan `{{ $member->email }}`.

---

# 15. SQL Injection Prevention
- Relasi Eloquent `belongsToMany` dan method `$workspace->members()->attach($userId)` serta `detach($userId)` secara otomatis menggunakan parameterized query yang aman.

---

# 16. Database Design
Tabel pivot `workspace_members`:
```text
workspace_members
--------------------------------------------------------
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
workspace_id        BIGINT UNSIGNED NOT NULL
user_id             BIGINT UNSIGNED NOT NULL
created_at          TIMESTAMP NULL
updated_at          TIMESTAMP NULL

FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
UNIQUE KEY unique_workspace_user (workspace_id, user_id)
```

---

# 17. Database Constraints
- `workspace_id`: `NOT NULL`, FK `workspaces.id`.
- `user_id`: `NOT NULL`, FK `users.id`.
- `UNIQUE (workspace_id, user_id)`: Mencegah duplikasi data keanggotaan pada tingkat basis data.

---

# 18. Foreign Key Behavior
- `workspace_id` -> `workspaces.id`: `ON DELETE CASCADE`. Jika workspace dihapus, semua baris pivot keanggotaan otomatis terhapus.
- `user_id` -> `users.id`: `ON DELETE CASCADE`. Jika user dihapus oleh admin, keanggotaannya di seluruh workspace otomatis terhapus.

---

# 19. Duplicate Handling
- **Penyebab**: Menambahkan pengguna yang sudah ada di workspace.
- **Penanganan**: Validator backend memeriksa via `exists()` relasi, dan database menjamin melalui `UNIQUE KEY (workspace_id, user_id)`.
- **Pesan**: *"Pengguna ini sudah menjadi anggota workspace."*.

---

# 20. HTTP Contract
### 1. Tambah Anggota ke Workspace
- **Method & URI**: `POST /workspaces/{workspace}/members`
- **Middleware**: `auth`
- **Request Payload**:
  - `_token`: string (CSRF)
  - `user_id`: integer, required, exists:users,id
- **Success**: `302 Redirect` ke `/workspaces/{workspace}` dengan flash success *"Anggota berhasil ditambahkan."*.
- **Failure**: `302 Redirect Back` dengan session `$errors`.

### 2. Keluarkan Anggota dari Workspace
- **Method & URI**: `DELETE /workspaces/{workspace}/members/{user}`
- **Middleware**: `auth`
- **Request Payload**:
  - `_token`: string (CSRF)
  - `_method`: `DELETE`
- **Success**: `302 Redirect` ke `/workspaces/{workspace}` dengan flash success *"Anggota berhasil dikeluarkan."*.

---

# 21. Controller Responsibilities
`app/Http/Controllers/WorkspaceMemberController.php`:
- `store(Request $request, Workspace $workspace)`:
  1. Memeriksa otorisasi: hanya pemilik workspace yang boleh menambah anggota (`$workspace->user_id === auth()->id()`).
  2. Validasi `user_id`.
  3. Memeriksa duplikasi keanggotaan.
  4. Menyimpan relasi via `$workspace->members()->attach($request->user_id)`.
  5. Redirect dengan pesan sukses.
- `destroy(Workspace $workspace, User $user)`:
  1. Memeriksa otorisasi pemilik.
  2. Menghapus relasi via `$workspace->members()->detach($user->id)`.
  3. Redirect dengan pesan sukses.

---

# 22. Model Responsibilities
- `Workspace.php`:
  ```php
  public function members()
  {
      return $this->belongsToMany(User::class, 'workspace_members')->withTimestamps();
  }
  ```
- `User.php`:
  ```php
  public function memberWorkspaces()
  {
      return $this->belongsToMany(Workspace::class, 'workspace_members')->withTimestamps();
  }
  ```

---

# 23. Authorization
- Hanya pemilik workspace (`$workspace->user_id === auth()->id()`) yang diizinkan untuk:
  - Melihat form undang anggota.
  - Mengirim request `POST /workspaces/{workspace}/members`.
  - Mengirim request `DELETE /workspaces/{workspace}/members/{user}`.
- Anggota biasa dapat melihat daftar anggota tim, namun tidak melihat form input atau tombol hapus.
- Pengguna di luar workspace tidak dapat mengakses URL workspace tersebut sama sekali.

---

# 24. CSRF
- Request penambahan anggota (`POST`) dan pengeluaran anggota (`DELETE`) menyertakan `@csrf`.

---

# 25. Mass Assignment Protection
- Operasi menggunakan `$workspace->members()->attach($validatedId)` yang secara eksplisit hanya memasukkan ID yang telah divalidasi ke tabel pivot.

---

# 26. Error Handling
- Jika pengguna bukan pemilik mencoba menambah/menghapus anggota: HTTP 403 Forbidden.
- Jika user ID yang dikirim tidak valid: Kembali dengan pesan error validasi.

---

# 27. Transactions
- Operasi attach/detach pada 1 tabel pivot bersifat atomik di database MySQL.

---

# 28. Race Conditions
- Jika 2 admin/pemilik submit invite user yang sama pada detik yang sama: `UNIQUE KEY (workspace_id, user_id)` pada database mencegah duplikasi data baris ganda.

---

# 29. Delete Behavior
- Mengeluarkan anggota hanya menghapus baris dari tabel pivot `workspace_members`. Akun pengguna yang bersangkutan di tabel `users` **tidak** terhapus.

---

# 30. Empty State
- Bagian anggota tim selalu menampilkan minimal 1 orang, yaitu Pemilik Workspace.

---

# 31. Pagination
- Tidak diperlukan untuk daftar anggota tim di tingkat praktikum (jumlah anggota per workspace rata-rata <20 orang).

---

# 32. Search / Filter
- Dropdown form menampilkan daftar pengguna sistem yang belum bergabung di workspace tersebut untuk memudahkan pemilihan tanpa perlu search manual.

---

# 33. Accessibility Minimum
- Label form "Pilih Pengguna" jelas dan terkait dengan elemen `<select>`.
- Tombol aksi hapus anggota memiliki konfirmasi native jelas.

---

# 34. Responsive Behavior
- Pada layar kecil/mobile, form undang anggota dan tombol disusun vertikal penuh (`w-full flex-col`).
- Tabel anggota menggunakan pembungkus `overflow-x-auto`.

---

# 35. Edge Cases
- Pengguna mencoba menghapus pemilik dari daftar anggota: Tidak dimungkinkan karena pemilik tidak berada di tabel pivot `workspace_members` melainkan di kolom `workspaces.user_id`.

---

# 36. Security Checklist
- [x] Otorisasi ketat: Hanya pemilik workspace yang dapat menambah atau mengeluarkan anggota.
- [x] Database constraint `UNIQUE(workspace_id, user_id)` mencegah duplikasi keanggotaan.
- [x] Token CSRF aktif pada request POST dan DELETE.
- [x] Parameter binding penuh pada relasi Eloquent pivot.

---

# 37. Testing Strategy
## Happy Path
- Pemilik membuka workspace, memilih user "Siti", klik Undang -> Siti muncul di daftar anggota.
- Siti login dengan akunnya -> Workspace "Projek Web Kita" muncul di daftar workspacenya di bawah bagian "Shared with Me".
- Pemilik mengklik "Keluarkan" pada Siti -> Siti hilang dari daftar anggota dan tidak bisa lagi mengakses workspace tersebut.

## Validation Tests
- Coba undang pengguna yang sudah menjadi anggota -> Muncul pesan error bahwa pengguna sudah tergabung.
- Coba undang user ID yang tidak ada di database -> Validasi `exists` menolak request.

## Security Tests
- Anggota biasa mencoba mengirim request POST invite -> Mendapatkan respon HTTP 403 Forbidden.

---

# 38. Acceptance Criteria
- [ ] Tersedia migrasi tabel pivot `workspace_members` dengan unique constraint.
- [ ] Pemilik workspace dapat melihat daftar anggota tim di workspacenya.
- [ ] Pemilik dapat mengundang pengguna terdaftar lainnya ke dalam workspace.
- [ ] Anggota yang diundang dapat melihat dan mengakses workspace tersebut saat login.
- [ ] Pemilik dapat mengeluarkan anggota dari workspace.
- [ ] Anggota biasa dilarang mengundang atau mengeluarkan anggota lain.

---

# 39. Definition of Done
- Migrasi tabel `workspace_members` berhasil dijalankan.
- Relasi `belongsToMany` di `Workspace.php` dan `User.php` aktif.
- `WorkspaceMemberController` selesai diimplementasikan.
- Komponen tampilan anggota di Blade selesai dan rapi.
- Hak akses pemilik vs anggota teruji dengan benar.

---

# 40. Implementation Order Inside Feature
1. Buat migrasi `create_workspace_members_table` dengan foreign keys dan unique index.
2. Definisikan relasi `members()` pada model `Workspace` dan `memberWorkspaces()` pada model `User`.
3. Tambahkan routes anggota di `routes/web.php`.
4. Buat `WorkspaceMemberController.php` dengan method `store` dan `destroy`.
5. Tambahkan seksi panel anggota tim di `resources/views/workspaces/show.blade.php`.
6. Uji coba fungsionalitas undang dan keluarkan anggota tim.

---

# 41. Estimated 60-Minute Breakdown
- **00–10 menit**: Migrasi pivot `workspace_members` dan konfigurasi relasi Eloquent.
- **10–25 menit**: Controller logic untuk mengundang dan mengeluarkan anggota tim.
- **25–40 menit**: Desain UI daftar anggota dan form dropdown invite di Blade.
- **40–50 menit**: Penerapan otorisasi (owner only) dan handling validasi duplikat.
- **50–60 menit**: Pengujian multi-user (login sebagai akun A, undang akun B, login akun B verifikasi akses).

---

# 42. Explicit Non-Requirements
- Tidak ada fitur kirim email notifikasi/undangan SMTP.
- Tidak ada sistem perizinan bertingkat (hanya pemilik dan anggota biasa).
- Tidak ada fitur chat atau komentar langsung di workspace.
