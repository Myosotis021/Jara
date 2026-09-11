# FILE: prd-kelola-workspace-2.md

# 1. Feature Overview
- **Nama Fitur**: Manajemen Workspace / Kategori Daftar Tugas (Workspace Management)
- **Tujuan Fitur**: Memungkinkan setiap pengguna untuk membuat, melihat, mengedit, dan menghapus workspace (wadah daftar tugas) yang terpisah-pisah untuk mengorganisir berbagai kategori aktivitas (misalnya "Kerjaan Kantor", "Tugas Kuliah", "Projek Web Kita").
- **Masalah yang Diselesaikan**: Menghindari tercampurnya berbagai jenis tugas yang berbeda domain, serta menyediakan ruang kerja bersama sebelum tugas dan anggota dapat dikolaborasikan.
- **Pengguna/Aktor**: Pengguna Terdaftar (`user`) dan Administrator (`admin`).
- **Dependency terhadap PRD Lain**: Bergantung pada `prd-autentikasi-1.md` (membutuhkan `auth()->id()` pengguna yang login).
- **Alasan Urutan Implementasi**: Berada pada Urutan 2 karena tugas (`tasks`) dan kolaborasi anggota (`memberships`) membutuhkan wadah workspace sebagai induknya. Fitur ini dapat dikerjakan paralel dengan `prd-kelola-user-2.md`.

---

# 2. User Story
- Sebagai **Pengguna**, saya ingin membuat workspace baru dengan nama dan deskripsi tertentu, sehingga saya dapat mengelompokkan tugas-tugas saya secara teratur.
- Sebagai **Pengguna**, saya ingin melihat daftar seluruh workspace yang saya miliki dan workspace di mana saya menjadi anggota, sehingga saya dapat dengan mudah memilih ruang kerja yang ingin saya akses.
- Sebagai **Pemilik Workspace**, saya ingin memperbarui nama dan deskripsi workspace saya, sehingga informasi wadah tugas tersebut selalu relevan.
- Sebagai **Pemilik Workspace**, saya ingin menghapus workspace yang sudah tidak digunakan lagi beserta seluruh isi tugasnya, sehingga daftar ruang kerja saya tetap rapi.

---

# 3. Scope
## MVP / Required
- Migrasi tabel `workspaces` (id, user_id, name, description, created_at, updated_at).
- Halaman daftar workspace (`GET /workspaces`): menampilkan card workspace milik sendiri (*My Workspaces*) dan workspace kolaborasi (*Shared with Me*).
- Form pembuatan workspace baru (`POST /workspaces`) dengan field `name` dan `description`.
- Form edit workspace (`PUT /workspaces/{workspace}`) khusus untuk pemilik (*owner*).
- Hapus workspace (`DELETE /workspaces/{workspace}`) khusus untuk pemilik (*owner*) dengan konfirmasi.
- Otorisasi kepemilikan workspace menggunakan Policy atau pengecekan di controller.

## If Time Permits
- Pemilihan warna/ikon untuk kartu workspace.

## Out of Scope
- Pengalihan kepemilikan workspace (*transfer ownership*).
- Fitur arsip workspace (*soft delete* / archiving).
- Template workspace otomatis.

---

# 4. Preconditions
- Fitur `prd-autentikasi-1.md` sudah selesai.
- Pengguna telah login ke dalam akun Jara.
- Tabel `users` sudah tersedia.

---

# 5. Main User Flow
### Flow Membuat Workspace:
1. Pengguna membuka halaman utama `/workspaces`.
2. Sistem menampilkan daftar workspace yang sudah ada dan tombol "+ Buat Workspace".
3. Pengguna menekan tombol "+ Buat Workspace".
4. Muncul form / halaman pembuatan workspace (`GET /workspaces/create` atau form modal).
5. Pengguna mengisi `Nama Workspace` (contoh: "Tugas Kuliah Semester 4") dan `Deskripsi Singkat` (opsional).
6. Pengguna menekan tombol "Simpan Workspace".
7. Browser mengirim request `POST /workspaces` dengan token CSRF.
8. Backend memvalidasi input.
9. Backend menetapkan `user_id` dari `auth()->id()` dan menyimpan data ke tabel `workspaces`.
10. Backend me-redirect pengguna ke `/workspaces/{id}` (halaman detail tugas) atau kembali ke `/workspaces` dengan pesan flash sukses *"Workspace berhasil dibuat."*.

### Flow Menghapus Workspace:
1. Pemilik workspace menekan tombol "Hapus" pada card workspace miliknya.
2. Tampil pop-up konfirmasi: *"Apakah Anda yakin ingin menghapus workspace ini beserta seluruh tugas di dalamnya?"*.
3. Pengguna mengonfirmasi.
4. Browser mengirimkan HTTP request `POST /workspaces/{id}` dengan method spoofing `DELETE`.
5. Backend memastikan bahwa `auth()->id() === $workspace->user_id`.
6. Data workspace dihapus dari database. Seluruh tugas dan data kolaborasi yang berelasi terhapus otomatis melalui cascade.
7. Backend me-redirect ke `/workspaces` dengan flash message sukses *"Workspace berhasil dihapus."*.

---

# 6. Alternative Flow
- **Nama Workspace Kosong**:
  - Pengguna mengosongkan nama workspace saat submit.
  - Backend menolak dengan validation error: *"Nama workspace wajib diisi."*.
- **Pengguna Non-Owner Mencoba Edit/Hapus Workspace**:
  - Pengguna yang bukan pemilik mencoba mengirim request edit atau delete pada workspace orang lain.
  - Backend menolak dengan HTTP 403 Forbidden atau redirect dengan pesan *"Anda tidak memiliki hak akses untuk mengubah workspace ini."*.
- **Workspace Tidak Ditemukan**:
  - Pengguna membuka ID workspace yang tidak ada di database.
  - Backend merespons dengan HTTP 404 Not Found standar.

---

# 7. Low-Fidelity UI Design
```text
+-------------------------------------------------------------------------------+
| JARA  |  [Workspaces]                                    Halo, Budi  [Logout] |
+-------------------------------------------------------------------------------+
|                                                                               |
| Workspace & Daftar Tugas Saya                           [+ Buat Workspace]    |
| [ Flash Alert: Sukses / Error ]                                               |
|                                                                               |
| +-------------------------+ +-------------------------+ +-------------------+ |
| | Tugas Kuliah            | | Kerjaan Kantor          | | Projek Web Kita   | |
| | Tugas mingguan sem 4    | | Laporan mingguan dev    | | Fitur auth & crud | |
| |                         | |                         | |                   | |
| | Pemilik: Anda           | | Pemilik: Anda           | | Anggota (Shared)  | |
| | [Buka]  [Edit]  [Hapus] | | [Buka]  [Edit]  [Hapus] | | [Buka]            | |
| +-------------------------+ +-------------------------+ +-------------------+ |
|                                                                               |
+-------------------------------------------------------------------------------+

Form Modal / Page Create Workspace:
+-------------------------------------------------------------+
| Buat Workspace Baru                                         |
+-------------------------------------------------------------+
| Nama Workspace                                              |
| [_________________________________________________________] |
| [ Error: nama wajib diisi ]                                 |
|                                                             |
| Deskripsi (Opsional)                                        |
| [_________________________________________________________] |
| [_________________________________________________________] |
|                                                             |
| [ Batal ]                               [ Simpan Workspace ]|
+-------------------------------------------------------------+
```

---

# 8. Visual Design & Tailwind Guidance
- **Grid Layout**: `grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6`
- **Workspace Card**:
  - Wrapper: `bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition p-5 flex flex-col justify-between`
  - Title: `text-lg font-bold text-gray-900 mb-1 hover:text-blue-600`
  - Description: `text-sm text-gray-500 mb-4 line-clamp-2`
  - Meta/Badge: `text-xs font-semibold px-2 py-1 rounded bg-gray-100 text-gray-600 self-start mb-4`
  - Card Footer Action: `flex items-center justify-between pt-3 border-t border-gray-100 text-xs`
- **Buttons**:
  - Primary ("+ Buat Workspace"): `bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition`
  - Buka Workspace: `text-blue-600 hover:text-blue-800 font-semibold`
  - Edit: `text-gray-600 hover:text-gray-900`
  - Hapus: `text-red-600 hover:text-red-800`

---

# 9. UI States
- **Default State**: Grid kartu workspace menampilkan seluruh workspace milik user dan workspace kolaborasi.
- **Empty State**: Jika user belum memiliki workspace sama sekali, tampilkan kartu ilustrasi kosong: *"Belum ada workspace. Buat workspace pertama Anda untuk mulai mengelola tugas!"* dengan tombol CTA.
- **Submitting State**: Native browser submission saat tombol "Simpan Workspace" ditekan.
- **Validation Error State**: Input nama workspace diberi border merah dengan teks error di bawahnya.
- **Success State**: Card workspace baru langsung muncul di daftar setelah redirect dengan flash banner hijau.
- **Loading State**: Tidak diperlukan karena rendering server-side murni.

---

# 10. Error Container Specification
- **Field-level Error**:
  - Field `name`: Tepat di bawah input nama workspace.
    ```html
    @error('name')
        <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
    @enderror
    ```
  - Field `description`: Tepat di bawah textarea deskripsi (`@error('description')`).
- **Form-level Flash Error**:
  - Ditampilkan di atas daftar workspace jika ada pelanggaran otorisasi atau kegagalan sistem.
- **Delete Confirmation**:
  - Native browser dialog: `onsubmit="return confirm('Hapus workspace ini? Seluruh tugas di dalamnya akan ikut terhapus.')"`

---

# 11. Form Specification
### 1. Nama Workspace
- HTML: `type="text"`
- Name: `name`
- Frontend: `required`, `maxlength="100"`, `placeholder="Misal: Tugas Kuliah Semester 4"`
- Backend: `required|string|max:100`
- Database: `VARCHAR(100) NOT NULL`
- Old input: `value="{{ old('name', $workspace->name ?? '') }}"`

### 2. Deskripsi
- HTML: `<textarea>`
- Name: `description`
- Frontend: Optional, `maxlength="500"`, `placeholder="Deskripsi singkat mengenai daftar tugas ini..."`
- Backend: `nullable|string|max:500`
- Database: `TEXT NULL`
- Old input: `{{ old('description', $workspace->description ?? '') }}`

---

# 12. Frontend Validation
- Atribut `required` dan `maxlength="100"` pada input nama workspace.
- Atribut `maxlength="500"` pada textarea deskripsi.

---

# 13. Backend Validation
Validation Rules pada `WorkspaceController`:
```php
$validated = $request->validate([
    'name' => ['required', 'string', 'max:100'],
    'description' => ['nullable', 'string', 'max:500'],
]);
```

---

# 14. Input Sanitization & XSS Prevention
- Nama dan deskripsi workspace ditampilkan menggunakan escaping Blade `{{ $workspace->name }}` dan `{{ $workspace->description }}`.
- String di-trim otomatis oleh framework.

---

# 15. SQL Injection Prevention
- Semua operasi memanfaatkan Eloquent Query Builder: `Workspace::create(...)`, `$workspace->update(...)`, `$workspace->delete()`.
- Tidak ada raw concatenation SQL.

---

# 16. Database Design
Tabel `workspaces`:
```text
workspaces
--------------------------------------------------------
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
user_id             BIGINT UNSIGNED NOT NULL (Owner/Creator)
name                VARCHAR(100) NOT NULL
description         TEXT NULL
created_at          TIMESTAMP NULL
updated_at          TIMESTAMP NULL

FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
```

---

# 17. Database Constraints
- `user_id`: `NOT NULL`, Foreign Key mengarah ke `users.id`.
- `name`: `NOT NULL`.

---

# 18. Foreign Key Behavior
- `user_id` -> `users.id`:
  - `ON DELETE CASCADE`: Jika akun pengguna dihapus oleh admin, maka workspace milik pengguna tersebut otomatis terhapus agar tidak meninggalkan data yatim (*orphaned records*).

---

# 19. Duplicate Handling
- Pengguna diperbolehkan membuat workspace dengan nama yang sama (misal 2 proyek berbeda bernama "Projek"), namun jika ingin dibedakan pengguna dapat mengedit deskripsinya. Tidak ada constraint unique ketat pada nama agar fleksibel.

---

# 20. HTTP Contract
### 1. Daftar Workspace
- **Method & URI**: `GET /workspaces`
- **Middleware**: `auth`
- **Response**: Render view `workspaces.index` membawa data `$myWorkspaces` dan `$sharedWorkspaces`.

### 2. Form Tambah Workspace
- **Method & URI**: `GET /workspaces/create`
- **Middleware**: `auth`
- **Response**: Render view `workspaces.create`.

### 3. Simpan Workspace Baru
- **Method & URI**: `POST /workspaces`
- **Request Payload**:
  - `_token`: string (CSRF)
  - `name`: string, required, max 100
  - `description`: optional string, max 500
- **Success**: `302 Redirect` ke `/workspaces` dengan flash success *"Workspace berhasil dibuat."*.
- **Failure**: `302 Redirect Back` dengan session `$errors` dan old input.

### 4. Form Edit Workspace
- **Method & URI**: `GET /workspaces/{workspace}/edit`
- **Middleware**: `auth`
- **Response**: Render view `workspaces.edit`.

### 5. Update Workspace
- **Method & URI**: `PUT /workspaces/{workspace}`
- **Request Payload**:
  - `_token`: string
  - `_method`: `PUT`
  - `name`: string, required
  - `description`: optional string
- **Success**: `302 Redirect` ke `/workspaces` dengan flash success *"Workspace berhasil diperbarui."*.

### 6. Hapus Workspace
- **Method & URI**: `DELETE /workspaces/{workspace}`
- **Success**: `302 Redirect` ke `/workspaces` dengan flash success *"Workspace berhasil dihapus."*.

---

# 21. Controller Responsibilities
`app/Http/Controllers/WorkspaceController.php`:
- `index()`: Mengambil workspace milik user aktif (`auth()->user()->ownedWorkspaces`) dan workspace yang di-share (`auth()->user()->memberWorkspaces`).
- `create()`: Menampilkan form tambah.
- `store(Request $request)`: Validasi input, tambahkan `user_id => auth()->id()`, simpan via `Workspace::create()`, redirect.
- `edit(Workspace $workspace)`: Otorisasi pemilik, lalu tampilkan form edit.
- `update(Request $request, Workspace $workspace)`: Otorisasi pemilik, validasi, update data, redirect.
- `destroy(Workspace $workspace)`: Otorisasi pemilik, hapus workspace, redirect.

---

# 22. Model Responsibilities
`app/Models/Workspace.php`:
- `$fillable = ['user_id', 'name', 'description']`.
- Relasi:
  ```php
  public function owner()
  {
      return $this->belongsTo(User::class, 'user_id');
  }

  public function tasks()
  {
      return $this->hasMany(Task::class);
  }

  public function members()
  {
      return $this->belongsToMany(User::class, 'workspace_members')->withTimestamps();
  }
  ```

---

# 23. Authorization
- Setiap aksi `edit`, `update`, dan `destroy` wajib memastikan bahwa pengguna yang sedang login adalah pemilik:
  ```php
  if ($workspace->user_id !== auth()->id()) {
      abort(403, 'Hanya pemilik yang dapat mengubah atau menghapus workspace ini.');
  }
  ```
- Otorisasi ini dapat dienkapsulasi menggunakan Laravel Policy `WorkspacePolicy` (`update`, `delete`).

---

# 24. CSRF
- Seluruh form Blade mutating request (`POST`, `PUT`, `DELETE`) menyertakan `@csrf` dan direktif `@method()` jika diperlukan.

---

# 25. Mass Assignment Protection
- `user_id` selalu diambil dari konteks sesi server: `auth()->id()`. Jangan pernah mengambil `user_id` dari input form hidden `$request->input('user_id')`.
- `$fillable` dibatasi hanya pada `user_id`, `name`, dan `description`.

---

# 26. Error Handling
- Akses ke workspace yang tidak ada: Mengembalikan 404 otomatis melalui Laravel Route Model Binding.
- Percobaan manipulasi workspace milik orang lain: Mengembalikan 403 Forbidden.

---

# 27. Transactions
- Operasi pembuatan dan pengubahan workspace sederhana tidak memerlukan transaksi manual.

---

# 28. Race Conditions
- Tidak ada critical race condition pada penamaan workspace biasa.

---

# 29. Delete Behavior
- **Strategi**: Hard delete (`$workspace->delete()`).
- **Cascade**: Menghapus workspace akan secara otomatis menghapus seluruh tugas (`tasks`) dan data keanggotaan (`workspace_members`) yang berada di bawah workspace tersebut.
- **Konfirmasi**: Dialog konfirmasi di frontend mencegah klik tidak sengaja.

---

# 30. Empty State
- Tampilan saat user belum memiliki workspace:
  ```text
  +-------------------------------------------------------+
  |              Belum Ada Workspace Dibuat               |
  |  Kelompokkan tugas kuliah, kantor, atau proyek Anda   |
  |                  dalam satu wadah.                    |
  |                                                       |
  |                [ + Buat Workspace Baru ]              |
  +-------------------------------------------------------+
  ```

---

# 31. Pagination
- Pada tahap praktikum 60 menit, jumlah workspace per pengguna umumnya sedikit (<20). Data diambil langsung tanpa paginasi kompleks atau paginasi sederhana 9 item per halaman (`paginate(9)`).

---

# 32. Search / Filter
- Tidak diwajibkan untuk MVP 60 menit.

---

# 33. Accessibility Minimum
- Form field memiliki label yang terhubung dengan `id` input.
- Tombol aksi memiliki kontras warna standar (biru untuk primary, merah untuk hapus).
- Tombol hapus memiliki konfirmasi eksplisit sebelum terkirim.

---

# 34. Responsive Behavior
- Layout grid 1 kolom di mobile (`grid-cols-1`), 2 kolom di tablet (`md:grid-cols-2`), dan 3 kolom di desktop (`lg:grid-cols-3`).

---

# 35. Edge Cases
- Pengguna memasukkan nama workspace berupa spasi saja: Validator `required|string` otomatis menolak.
- Deskripsi sangat panjang (>500 karakter): Dibatasi validator `max:500`.

---

# 36. Security Checklist
- [x] Input `user_id` diambil dari `auth()->id()`, bukan dari request form.
- [x] Otorisasi ketat: Hanya pemilik workspace yang dapat mengedit atau menghapus.
- [x] Output nama dan deskripsi di-escape dengan `{{ }}` untuk mencegah XSS.
- [x] CSRF protection aktif pada semua form mutasi.
- [x] Foreign key constraint `ON DELETE CASCADE` diterapkan pada database MySQL.

---

# 37. Testing Strategy
## Happy Path
- Pengguna login, masuk ke `/workspaces/create`, isi `name: Tugas Kuliah`, klik Simpan. Sistem redirect ke index dan card "Tugas Kuliah" tampil.
- Klik Edit pada card, ubah nama menjadi "Tugas Kuliah & Praktikum", submit -> Nama terbarui.
- Klik Hapus, konfirmasi OK -> Card terhapus dari daftar.

## Validation Tests
- Submit nama kosong -> Muncul pesan validasi *"The name field is required."*.
- Submit nama >100 karakter -> Validasi gagal.

## Security Tests
- Pengguna B mencoba mengirim `DELETE /workspaces/1` (milik Pengguna A) -> Sistem memblokir dengan status 403 Forbidden.

---

# 38. Acceptance Criteria
- [ ] Pengguna dapat melihat daftar workspace miliknya.
- [ ] Pengguna dapat membuat workspace baru dengan nama dan deskripsi.
- [ ] Workspace yang dibuat secara otomatis tercatat dengan pemilik `auth()->id()`.
- [ ] Pemilik dapat mengubah nama dan deskripsi workspace.
- [ ] Pemilik dapat menghapus workspace miliknya.
- [ ] Pengguna lain tidak dapat mengedit atau menghapus workspace yang bukan miliknya.

---

# 39. Definition of Done
- Migrasi tabel `workspaces` selesai dan berhasil dieksekusi.
- Model `Workspace` dengan relasi ke `User` selesai dibuat.
- Controller `WorkspaceController` mengimplementasikan CRUD lengkap dengan otorisasi.
- View Blade `workspaces/index.blade.php`, `create.blade.php`, dan `edit.blade.php` tampil rapi dan responsif.
- Fitur lolos pengujian manual happy path dan authorization check.

---

# 40. Implementation Order Inside Feature
1. Buat file migrasi `create_workspaces_table`.
2. Buat model `Workspace.php` dengan `$fillable` dan relasi ke `User`.
3. Daftarkan resourceful routes untuk workspaces di `routes/web.php` (dilindungi middleware `auth`).
4. Buat `WorkspaceController.php` dengan method `index`, `create`, `store`, `edit`, `update`, `destroy`.
5. Buat view Blade `resources/views/workspaces/index.blade.php`.
6. Buat view Blade `resources/views/workspaces/create.blade.php` dan `edit.blade.php`.
7. Tambahkan otorisasi di controller atau buat `WorkspacePolicy`.
8. Uji coba pembuatan, pengubahan, dan penghapusan workspace.

---

# 41. Estimated 60-Minute Breakdown
- **00–10 menit**: Migrasi database `workspaces` dan konfigurasi model Eloquent.
- **10–25 menit**: Routes & Controller CRUD (`index`, `create`, `store`).
- **25–40 menit**: Pembuatan view Blade (`index` grid card & `create` form).
- **40–50 menit**: Fitur `edit`, `update`, `destroy` beserta otorisasi pemilik (owner check).
- **50–60 menit**: Manual testing, validasi error state, dan verifikasi tampilan responsif.

---

# 42. Explicit Non-Requirements
- Tidak menggunakan modal JavaScript dinamis jika form halaman biasa lebih cepat dibuat.
- Tidak ada pengalihan kepemilikan workspace (*ownership transfer*).
- Tidak ada fitur sub-folder atau hirarki workspace bertingkat.
