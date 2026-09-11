# FILE: prd-kelola-tugas-3.md

# 1. Feature Overview
- **Nama Fitur**: Manajemen Tugas, Prioritas, Tenggat Waktu, & Monitoring Progres (Task & Progress Tracking)
- **Tujuan Fitur**: Memungkinkan pengguna (baik pemilik maupun anggota workspace) untuk menambahkan tugas satu per satu dengan pendekatan penulisan sederhana (*"kaya nulis biasa"*), menentukan prioritas (*Penting* vs *Menyusul*), menetapkan tenggat waktu (*deadline*), menandai tugas yang sudah beres, serta memungkinkan pemilik workspace memantau persentase dan jumlah progres tugas yang telah rampung.
- **Masalah yang Diselesaikan**: Menghilangkan ketidakjelasan daftar pekerjaan, memastikan prioritas dan batas waktu terkelola dengan transparan, serta memberikan visibilitas langsung terhadap kemajuan proyek (*progress tracking*).
- **Pengguna/Aktor**: Pemilik Workspace (*Owner*) dan Anggota Kolaborasi (*Member*).
- **Dependency terhadap PRD Lain**: Bergantung pada `prd-kelola-workspace-2.md` (tugas harus berada di bawah suatu workspace tertentu).
- **Alasan Urutan Implementasi**: Berada pada Urutan 3 karena membutuhkan tabel dan halaman detail workspace yang sudah tersedia. Fitur ini dapat dikerjakan secara paralel dengan fitur kolaborasi workspace (`prd-kolaborasi-workspace-3.md`).

---

# 2. User Story
- Sebagai **Pengguna di Workspace**, saya ingin menambahkan tugas baru dengan judul, catatan/deskripsi sederhana, tingkat prioritas (`Penting` atau `Menyusul`), dan tenggat waktu, sehingga saya tahu apa yang harus dikerjakan dan batas waktunya.
- Sebagai **Pengguna di Workspace**, saya ingin menandai suatu tugas menjadi beres/selesai (atau membatalkan tanda selesai), sehingga status pekerjaan selalu terbarui.
- Sebagai **Pemilik Workspace**, saya ingin melihat ringkasan progres pengerjaan tugas (misal: "4 dari 10 tugas selesai - 40%"), sehingga saya dapat memantau produktivitas tim secara cepat.
- Sebagai **Pengguna di Workspace**, saya ingin mengedit atau menghapus tugas yang saya buat, sehingga daftar tugas selalu akurat.

---

# 3. Scope
## MVP / Required
- Migrasi tabel `tasks`:
  - `id`, `workspace_id`, `user_id` (pembuat tugas), `title`, `description`, `priority` (`penting`, `menyusul`), `due_date` (datetime/date nullable), `is_completed` (boolean default false), `completed_at` (nullable timestamp), `timestamps`.
- Halaman detail workspace (`GET /workspaces/{workspace}`):
  - Bagian atas: Bar dan statistik progres (*"X dari Y tugas selesai (Z%)"*).
  - Form input tugas cepat (*inline quick-add*) atau tombol tambah tugas.
  - Daftar tugas dengan pengelompokan/status (belum selesai dan selesai).
- Aksi toggle status tugas (`PATCH /workspaces/{workspace}/tasks/{task}/toggle-status`).
- Form edit tugas (`GET /workspaces/{workspace}/tasks/{task}/edit` dan `PUT /workspaces/{workspace}/tasks/{task}`).
- Aksi hapus tugas (`DELETE /workspaces/{workspace}/tasks/{task}`).
- Validasi input (judul wajib, prioritas valid: `penting` / `menyusul`, format tanggal valid).

## If Time Permits
- Filter daftar tugas berdasarkan status: "Semua", "Belum Selesai", "Selesai", atau "Penting".
- Tanda peringatan merah jika tenggat waktu sudah terlewati (*overdue badge*).

## Out of Scope
- Sub-tugas bertingkat (*nested subtasks*).
- Fitur drag-and-drop Kanban board kompleks.
- Pengingat notifikasi email otomatis saat deadline mendekat.

---

# 4. Preconditions
- Fitur `prd-autentikasi-1.md` dan `prd-kelola-workspace-2.md` sudah selesai.
- Pengguna yang mengakses adalah pemilik workspace atau anggota yang telah bergabung.

---

# 5. Main User Flow
### Flow Menambah Tugas Baru:
1. Pengguna membuka halaman detail workspace (`GET /workspaces/{workspace}`).
2. Halaman menampilkan kartu monitoring progres dan formulir tambah tugas sederhana.
3. Pengguna mengisi:
   - Judul Tugas (contoh: "Membuat dokumen SRS bab 1")
   - Catatan / Deskripsi (opsional, teks bebas)
   - Prioritas: Memilih opsi "Penting" atau "Menyusul"
   - Tenggat Waktu: Memilih tanggal dan jam (opsional atau wajib sesuai kebutuhan proyek)
4. Pengguna menekan tombol "Tambah Tugas".
5. Browser mengirimkan HTTP request `POST /workspaces/{workspace}/tasks` dengan CSRF token.
6. Backend memverifikasi bahwa pengguna memiliki akses ke workspace (pemilik atau anggota).
7. Backend memvalidasi input data tugas.
8. Data disimpan ke tabel `tasks` dengan status `is_completed = false`.
9. Backend me-redirect kembali ke halaman workspace dengan flash success *"Tugas berhasil ditambahkan."*.
10. Daftar tugas dan bar progres persentase otomatis terhitung ulang dan terbarui.

### Flow Menandai Tugas Selesai (Toggle Status):
1. Pengguna melihat daftar tugas pada workspace.
2. Pengguna mengklik tombol centang / checkbox status di sebelah tugas.
3. Form mengirimkan HTTP request `PATCH /workspaces/{workspace}/tasks/{task}/toggle` dengan token CSRF.
4. Backend membalikkan nilai `is_completed`:
   - Jika sebelumnya `false`, ubah jadi `true` dan isi `completed_at = now()`.
   - Jika sebelumnya `true`, ubah jadi `false` dan set `completed_at = null`.
5. Backend me-redirect kembali ke halaman detail workspace.
6. Tampilan tugas berubah (diberi coretan teks / *line-through* atau dipindahkan ke seksi Selesai), dan indikator progres bertambah.

---

# 6. Alternative Flow
- **Akses oleh Pengguna Luar**:
  - Pengguna terdaftar yang bukan pemilik dan bukan anggota mencoba membuka `/workspaces/{workspace}` atau mengirim request manipulasi tugas.
  - Backend memblokir dengan status HTTP 403 Forbidden.
- **Input Judul Kosong**:
  - Pengguna submit form tanpa judul tugas.
  - Backend me-redirect back dengan validation error *"Judul tugas wajib diisi."*.
- **Pilihan Prioritas Tidak Valid**:
  - Manipulasi request dengan nilai prioritas di luar `penting` atau `menyusul`.
  - Backend validator `in:penting,menyusul` menolak request.

---

# 7. Low-Fidelity UI Design
```text
+-------------------------------------------------------------------------------+
| JARA  |  [< Kembali ke Workspaces]                       Halo, Budi  [Logout] |
+-------------------------------------------------------------------------------+
|                                                                               |
| Workspace: Tugas Kuliah Semester 4                                            |
| Deskripsi: Pengelolaan tugas mingguan dan praktikum                           |
|                                                                               |
| +---------------------------------------------------------------------------+ |
| | PROGRES TUGAS WORKSPACE                                                   | |
| | [=============>                                  ] 40% (2 dari 5 selesai) | |
| | 2 Selesai  •  3 Belum Selesai  •  1 Penting                               | |
| +---------------------------------------------------------------------------+ |
|                                                                               |
| +--- Tambah Tugas Baru ("Kaya Nulis Biasa") --------------------------------+ |
| | Judul Tugas:                                                              | |
| | [ Tulis apa yang perlu dikerjakan...                                    ] | |
| | Deskripsi / Catatan Tambahan:                                             | |
| | [ Tulis catatan detail di sini...                                       ] | |
| | Prioritas:                    Tenggat Waktu (Deadline):                   | |
| | [ [!] Penting  /  Menyusul v ] [ 2026-09-15 23:59                      ] | |
| |                                                       [ + Tambah Tugas ]  | |
| +---------------------------------------------------------------------------+ |
|                                                                               |
| DAFTAR TUGAS                                                                  |
|                                                                               |
| [ ] [PENTING] Membuat ERD Database                                            |
|     Tenggat: 12 Sep 2026 18:00  •  Oleh: Budi           [Edit]  [Hapus]      |
|                                                                               |
| [ ] [MENYUSUL] Merapikan file presentasi PPT                                  |
|     Tenggat: 15 Sep 2026 20:00  •  Oleh: Siti           [Edit]  [Hapus]      |
|                                                                               |
| [X] [PENTING] Membaca instruksi modul praktikum                               |
|     Selesai pada: 11 Sep 2026   •  Oleh: Budi           [Edit]  [Hapus]      |
|                                                                               |
+-------------------------------------------------------------------------------+
```

---

# 8. Visual Design & Tailwind Guidance
- **Progress Card**:
  - Container: `bg-white p-5 rounded-xl border border-gray-200 shadow-sm mb-6`
  - Progress Bar Background: `w-full bg-gray-200 rounded-full h-3 overflow-hidden mt-2`
  - Progress Bar Fill: `bg-green-600 h-3 rounded-full transition-all duration-300`
  - Progress Stats Text: `text-sm font-medium text-gray-700 mt-2 flex justify-between`
- **Task Form ("Kaya Nulis Biasa")**:
  - Container: `bg-white p-5 rounded-xl border border-blue-100 shadow-sm mb-6`
  - Title Input: `w-full px-3 py-2 border border-gray-300 rounded-lg text-base font-medium focus:ring-2 focus:ring-blue-500`
  - Textarea Notes: `w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 mt-2`
  - Row Controls: `grid grid-cols-1 md:grid-cols-3 gap-3 mt-3`
- **Priority Badges**:
  - Penting: `px-2.5 py-0.5 rounded text-xs font-bold bg-red-100 text-red-700 uppercase`
  - Menyusul: `px-2.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 uppercase`
- **Task Row Item**:
  - Active: `bg-white p-4 rounded-xl border border-gray-200 hover:border-blue-300 transition shadow-sm mb-3 flex items-start justify-between`
  - Completed: `bg-gray-50 p-4 rounded-xl border border-gray-200 opacity-75 shadow-none mb-3 flex items-start justify-between`
  - Completed Text: `line-through text-gray-400`
- **Status Toggle Button / Form**:
  - Unchecked: `w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer`
  - Checked: `w-5 h-5 rounded border-green-500 bg-green-500 text-white cursor-pointer`

---

# 9. UI States
- **Default State**: Menampilkan progress bar di atas, form input tugas, dan daftar tugas yang sudah dibuat.
- **Empty State (No Tasks)**:
  - Teks: *"Belum ada tugas di workspace ini. Tulis tugas pertama Anda pada form di atas!"*.
  - Progres menampilkan: `0% (0 dari 0 selesai)`.
- **Validation Error State**: Input border merah dengan pesan kesalahan di bawah kolom yang gagal divalidasi.
- **Completed State**: Tugas yang dicentang otomatis menampilkan coretan dan timestamp penyelesaian.
- **Loading / Submitting State**: Menggunakan native browser submit standar tanpa AJAX.

---

# 10. Error Container Specification
- **Field-level Error (Form Tambah/Edit Tugas)**:
  - `title`: Tepat di bawah input judul tugas (`@error('title')`).
  - `priority`: Tepat di bawah dropdown/radio prioritas (`@error('priority')`).
  - `due_date`: Tepat di bawah input tanggal tenggat (`@error('due_date')`).
  - `description`: Tepat di bawah catatan deskripsi (`@error('description')`).
  - Styling: `text-xs text-red-600 mt-1 font-medium`.
- **Flash Message**:
  - Sukses atau error otorisasi tampil di bagian atas konten workspace (`@if(session('success'))`).
- **Delete Confirmation**:
  - `onsubmit="return confirm('Apakah Anda yakin ingin menghapus tugas ini?')"` pada tombol hapus tugas.

---

# 11. Form Specification
### 1. Judul Tugas (Title)
- HTML: `type="text"`
- Name: `title`
- Frontend: `required`, `maxlength="255"`, `placeholder="Tulis judul tugas..."`
- Backend: `required|string|max:255`
- Database: `VARCHAR(255) NOT NULL`
- Old input: `value="{{ old('title') }}"`

### 2. Deskripsi / Catatan (Description)
- HTML: `<textarea>`
- Name: `description`
- Frontend: Optional, `maxlength="2000"`, `placeholder="Catatan tambahan (opsional)..."`
- Backend: `nullable|string|max:2000`
- Database: `TEXT NULL`
- Old input: `{{ old('description') }}`

### 3. Prioritas (Priority)
- HTML: `<select>`
- Name: `priority`
- Options:
  - `penting` (Label: "Penting / High Priority")
  - `menyusul` (Label: "Menyusul / Normal")
- Frontend: `required`
- Backend: `required|in:penting,menyusul`
- Database: `ENUM('penting', 'menyusul') NOT NULL DEFAULT 'menyusul'`
- Old input: `old('priority', 'menyusul')`

### 4. Tenggat Waktu (Due Date)
- HTML: `type="datetime-local"`
- Name: `due_date`
- Frontend: Optional
- Backend: `nullable|date`
- Database: `DATETIME NULL`
- Old input: `value="{{ old('due_date') }}"`

---

# 12. Frontend Validation
- Atribut `required` pada judul tugas.
- Dropdown prioritas wajib memilih antara `penting` atau `menyusul`.
- Atribut `type="datetime-local"` untuk pemilihan tanggal dan waktu yang valid secara native.

---

# 13. Backend Validation
Validation Rules pada `TaskController@store` & `update`:
```php
$validated = $request->validate([
    'title' => ['required', 'string', 'max:255'],
    'description' => ['nullable', 'string', 'max:2000'],
    'priority' => ['required', 'in:penting,menyusul'],
    'due_date' => ['nullable', 'date'],
]);
```

---

# 14. Input Sanitization & XSS Prevention
- Seluruh teks judul dan deskripsi tugas di-render menggunakan kurung kurawal ganda `{{ $task->title }}` dan `{{ $task->description }}`.
- Jika deskripsi memiliki multi-baris, gunakan `{!! nl2br(e($task->description)) !!}` secara aman (fungsi `e()` meng-escape HTML terlebih dahulu sebelum `nl2br`).

---

# 15. SQL Injection Prevention
- Semua interaksi query menggunakan Eloquent:
  ```php
  $workspace->tasks()->create([
      'user_id' => auth()->id(),
      'title' => $validated['title'],
      'description' => $validated['description'],
      'priority' => $validated['priority'],
      'due_date' => $validated['due_date'],
  ]);
  ```
- Tidak ada raw query concatenation.

---

# 16. Database Design
Tabel `tasks`:
```text
tasks
--------------------------------------------------------
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
workspace_id        BIGINT UNSIGNED NOT NULL
user_id             BIGINT UNSIGNED NOT NULL (Pembuat Tugas)
title               VARCHAR(255) NOT NULL
description         TEXT NULL
priority            ENUM('penting', 'menyusul') NOT NULL DEFAULT 'menyusul'
due_date            DATETIME NULL
is_completed        BOOLEAN NOT NULL DEFAULT 0
completed_at        DATETIME NULL
created_at          TIMESTAMP NULL
updated_at          TIMESTAMP NULL

FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
INDEX (workspace_id, is_completed)
```

---

# 17. Database Constraints
- `workspace_id`: `NOT NULL`, Foreign Key `workspaces.id`.
- `user_id`: `NOT NULL`, Foreign Key `users.id`.
- `title`: `NOT NULL`.
- `priority`: `NOT NULL`, default `'menyusul'`.
- `is_completed`: `NOT NULL`, default `0`.

---

# 18. Foreign Key Behavior
- `workspace_id` -> `workspaces.id`: `ON DELETE CASCADE`. Jika workspace dihapus, semua tugas di dalamnya ikut terhapus.
- `user_id` -> `users.id`: `ON DELETE CASCADE`. Jika akun pembuat dihapus, tugas miliknya terhapus.

---

# 19. Duplicate Handling
- Pengguna boleh membuat tugas dengan nama yang sama di workspace yang sama jika memang ada kebutuhan subtugas berulang. Tidak ada constraint unique pada judul tugas.

---

# 20. HTTP Contract
### 1. Lihat Workspace & Daftar Tugas
- **Method & URI**: `GET /workspaces/{workspace}`
- **Middleware**: `auth`
- **Response**: Render view `workspaces.show` membawa objek `$workspace`, `$tasks`, `$totalTasks`, `$completedTasks`, dan `$progressPercentage`.

### 2. Tambah Tugas Baru
- **Method & URI**: `POST /workspaces/{workspace}/tasks`
- **Request Payload**:
  - `_token`: string (CSRF)
  - `title`: string, required, max 255
  - `description`: optional string, max 2000
  - `priority`: string, required, in:penting,menyusul
  - `due_date`: optional datetime
- **Success**: `302 Redirect` ke `/workspaces/{workspace}` dengan flash success *"Tugas berhasil ditambahkan."*.
- **Failure**: `302 Redirect Back` dengan session `$errors` dan old input.

### 3. Toggle Status Selesai / Belum Selesai
- **Method & URI**: `PATCH /workspaces/{workspace}/tasks/{task}/toggle-status`
- **Request Payload**:
  - `_token`: string (CSRF)
  - `_method`: `PATCH`
- **Success**: `302 Redirect` ke `/workspaces/{workspace}` dengan flash success *"Status tugas berhasil diperbarui."*.

### 4. Edit Tugas
- **Method & URI**: `GET /workspaces/{workspace}/tasks/{task}/edit`
- **Response**: Render view `tasks.edit`.

### 5. Update Tugas
- **Method & URI**: `PUT /workspaces/{workspace}/tasks/{task}`
- **Success**: `302 Redirect` ke `/workspaces/{workspace}` dengan flash success *"Tugas berhasil diubah."*.

### 6. Hapus Tugas
- **Method & URI**: `DELETE /workspaces/{workspace}/tasks/{task}`
- **Success**: `302 Redirect` ke `/workspaces/{workspace}` dengan flash success *"Tugas berhasil dihapus."*.

---

# 21. Controller Responsibilities
`app/Http/Controllers/TaskController.php`:
- `store(Request $request, Workspace $workspace)`:
  1. Otorisasi: Pastikan `auth()->user()` adalah pemilik atau anggota workspace.
  2. Validasi input task.
  3. Simpan ke database via `$workspace->tasks()->create(...)`.
  4. Redirect back ke workspace detail.
- `toggleStatus(Workspace $workspace, Task $task)`:
  1. Otorisasi keanggotaan workspace.
  2. Update status:
     ```php
     $task->update([
         'is_completed' => !$task->is_completed,
         'completed_at' => !$task->is_completed ? now() : null,
     ]);
     ```
  3. Redirect back.
- `edit(...)` & `update(...)`: Mengubah isi tugas.
- `destroy(...)`: Menghapus tugas dari workspace.

---

# 22. Model Responsibilities
`app/Models/Task.php`:
- `$fillable = ['workspace_id', 'user_id', 'title', 'description', 'priority', 'due_date', 'is_completed', 'completed_at']`.
- `$casts = ['is_completed' => 'boolean', 'due_date' => 'datetime', 'completed_at' => 'datetime']`.
- Relasi:
  ```php
  public function workspace()
  {
      return $this->belongsTo(Workspace::class);
  }

  public function creator()
  {
      return $this->belongsTo(User::class, 'user_id');
  }

  public function attachments()
  {
      return $this->hasMany(TaskAttachment::class);
  }
  ```

---

# 23. Authorization
- Pengguna hanya boleh melihat, menambah, mengubah, atau menandai selesai tugas jika pengguna tersebut terdaftar sebagai:
  1. Pemilik workspace (`$workspace->user_id === auth()->id()`), ATAU
  2. Anggota workspace (`$workspace->members()->where('user_id', auth()->id())->exists()`).
- Jika tidak memenuhi salah satu, lemparkan HTTP 403 Forbidden.

---

# 24. CSRF
- Semua form penambahan tugas (`POST`), toggle status (`PATCH`), pembaruan (`PUT`), dan penghapusan (`DELETE`) dilindungi `@csrf`.

---

# 25. Mass Assignment Protection
- Nilai `workspace_id` dan `user_id` tidak diambil dari input pengguna bebas, melainkan ditetapkan langsung dari rute dan sesi pengguna:
  `$validated['user_id'] = auth()->id();`
  `$workspace->tasks()->create($validated);`

---

# 26. Error Handling
- Jika task tidak ditemukan pada workspace yang diberikan: Laravel mengembalikan HTTP 404.
- Jika pengguna bukan anggota: HTTP 403.
- Validasi gagal: Kembali ke halaman dengan pesan error dan old input terisi.

---

# 27. Transactions
- Tidak diperlukan database transaction manual untuk operasi pembuatan atau update 1 record task.

---

# 28. Race Conditions
- Jika 2 pengguna menekan tombol selesai bersamaan: Status akan mencerminkan eksekusi terakhir tanpa merusak konsistensi relasional database.

---

# 29. Delete Behavior
- **Strategi**: Hard delete (`$task->delete()`).
- **Cascade**: Menghapus tugas akan menghapus record lampiran berkas yang terkait secara otomatis (lihat `prd-upload-tugas-4.md`).

---

# 30. Empty State
- Jika belum ada tugas di workspace:
  - Tampilkan banner informatif: *"Belum ada tugas. Gunakan form di atas untuk menulis tugas pertama Anda."*.

---

# 31. Pagination
- Untuk sesi praktikum 60 menit, tugas dalam satu workspace di-load langsung atau dibatasi sederhana (`$tasks = $workspace->tasks()->latest()->get()`).

---

# 32. Search / Filter
- Filter status sederhana via query parameter URL:
  - `/workspaces/{id}?status=active` (tampilkan yang belum beres)
  - `/workspaces/{id}?status=completed` (tampilkan yang sudah beres)
  - Default: Menampilkan seluruh tugas.

---

# 33. Accessibility Minimum
- Label form terhubung secara tepat ke setiap field input.
- Checkbox toggle status memiliki deskripsi teks pendamping yang jelas.
- Badges prioritas menggunakan kontras warna yang cukup dan teks tegas ("PENTING" / "MENYUSUL").

---

# 34. Responsive Behavior
- Pada layar mobile, baris tugas menampilkan judul, tenggat, dan tombol aksi dalam susunan vertikal (*flex-col*).
- Pada layar desktop, baris tugas tertata rapi secara horizontal (*flex-row items-center justify-between*).

---

# 35. Edge Cases
- Tenggat waktu di masa lampau: Tetap diperbolehkan disimpan untuk keperluan pencatatan tugas yang terlambat dikerjakan, namun diberi indikator visual teks merah *(Terlewat)*.
- Deskripsi tugas berisi baris baru / paragraf: Ditampilkan dengan `nl2br(e($task->description))`.

---

# 36. Security Checklist
- [x] Otorisasi keanggotaan workspace diverifikasi sebelum mengeksekusi operasi task.
- [x] `workspace_id` dan `user_id` dipasang secara terpercaya dari server, bukan dari form input user.
- [x] Output judul dan catatan di-escape untuk pencegahan XSS.
- [x] Token CSRF disertakan pada setiap request POST, PATCH, PUT, dan DELETE.
- [x] Foreign key `ON DELETE CASCADE` aktif di tabel `tasks`.

---

# 37. Testing Strategy
## Happy Path
- Buka workspace, isi judul "Tugas Modul 1", pilih prioritas "Penting", submit -> Tugas tampil di daftar tugas.
- Klik tombol centang status -> Tugas tercoret, counter bertambah menjadi "1 dari 1 tugas selesai (100%)".
- Klik toggle kembali -> Tugas kembali aktif, counter kembali menjadi "0%".

## Validation Tests
- Submit form tugas tanpa judul -> Menampilkan error *"The title field is required."*.
- Submit nilai prioritas sembarangan -> Menampilkan error validasi prioritas.

## Security Tests
- Pengguna yang bukan anggota workspace mencoba mengirim `POST /workspaces/1/tasks` -> Sistem mengembalikan HTTP 403 Forbidden.

---

# 38. Acceptance Criteria
- [ ] Pengguna dalam workspace dapat menambah tugas dengan judul, deskripsi, prioritas, dan tenggat waktu.
- [ ] Prioritas wajib berlabel `Penting` atau `Menyusul`.
- [ ] Pengguna dapat mengubah status tugas menjadi selesai atau belum selesai secara instan via toggle form.
- [ ] Progress bar dan statistik (jumlah selesai vs total) terhitung secara akurat dan otomatis.
- [ ] Pengguna luar yang tidak berhak dilarang mengakses atau memanipulasi tugas.

---

# 39. Definition of Done
- Migrasi tabel `tasks` berhasil dibuat dan dijalankan di database.
- Controller `TaskController` mengimplementasikan pembuatan tugas, edit, update, destroy, dan toggle status.
- Tampilan detail workspace (`workspaces/show.blade.php`) menampilkan form tambah, progress bar, dan daftar tugas.
- Seluruh acceptance criteria lolos pengujian manual.

---

# 40. Implementation Order Inside Feature
1. Buat migrasi tabel `tasks` dan jalankan `php artisan migrate`.
2. Buat model `Task.php` dengan `$fillable`, `$casts`, dan relasi ke `Workspace` dan `User`.
3. Tambahkan route untuk tasks dan toggle-status di `routes/web.php`.
4. Buat `TaskController.php` dengan method `store`, `toggleStatus`, `edit`, `update`, `destroy`.
5. Update view `workspaces/show.blade.php` untuk menampilkan progress bar, form tambah tugas, dan daftar tugas.
6. Uji coba fungsionalitas penambahan tugas, penandaan status selesai, dan perhitungan progress bar.

---

# 41. Estimated 60-Minute Breakdown
- **00–10 menit**: Migrasi tabel `tasks`, konfigurasi model `Task`, dan relasi di `Workspace`.
- **10–25 menit**: Controller logic untuk `store` dan `toggleStatus`.
- **25–45 menit**: Desain Blade view `workspaces/show.blade.php` (kartu progres, form input, daftar tugas, badge prioritas).
- **45–55 menit**: Implementasi edit & delete tugas beserta otorisasi akses workspace.
- **55–60 menit**: Verifikasi fungsionalitas progress bar kalkulasi dan edge cases.

---

# 42. Explicit Non-Requirements
- Tidak menggunakan AJAX atau reactive frontend library (seperti Livewire/Vue/React) untuk toggle status; gunakan server-rendered form submission standar.
- Tidak ada fitur estimasi jam pengerjaan (story points/hours).
- Tidak ada fitur sub-tugas (*checklist item* bertingkat).
