# FILE: prd-upload-tugas-4.md

# 1. Feature Overview
- **Nama Fitur**: Pengunggahan dan Pengunduhan Berkas Lampiran Tugas (Task File Upload & Attachment Management)
- **Tujuan Fitur**: Memungkinkan pengguna (pemilik tugas maupun anggota tim) untuk mengunggah berkas pendukung atau hasil pengerjaan tugas (dokumen PDF, Word, arsip ZIP, atau gambar screenshot) ke dalam suatu tugas, serta mengunduh dan menghapus berkas tersebut secara aman.
- **Masalah yang Diselesaikan**: Menjawab kebutuhan utama aplikasi *"app buat upload tugas"* sehingga pengguna tidak hanya mencatat daftar pekerjaan berupa teks, tetapi juga dapat melampirkan berkas bukti pengerjaan, laporan praktikum, atau materi referensi secara terpusat di dalam tugas terkait.
- **Pengguna/Aktor**: Pengguna Terdaftar dalam Workspace (Pembuat Tugas, Pemilik Workspace, atau Anggota Tim).
- **Dependency terhadap PRD Lain**: Bergantung pada `prd-kelola-tugas-3.md` (berkas lampiran melekat pada entitas tugas tertentu).
- **Alasan Urutan Implementasi**: Berada pada Urutan 4 karena membutuhkan keberadaan tabel tugas dan workspace yang sudah beroperasi secara stabil. Fitur ini melengkapi fungsionalitas pencatatan tugas dengan kemampuan upload file.

---

# 2. User Story
- Sebagai **Pengguna di Workspace**, saya ingin mengunggah file (seperti PDF, DOCX, ZIP, JPG, PNG) ke dalam suatu tugas, sehingga hasil pengerjaan atau berkas acuan tugas dapat disimpan dan diakses oleh saya dan rekan tim.
- Sebagai **Pengguna di Workspace**, saya ingin mengunduh berkas yang telah diunggah pada suatu tugas, sehingga saya dapat memeriksa atau menggunakan berkas tersebut di perangkat lokal saya.
- Sebagai **Pengunggah atau Pemilik Workspace**, saya ingin menghapus berkas lampiran yang keliru atau usang, sehingga kapasitas penyimpanan tetap bersih dan file yang ditampilkan selalu yang terbaru.

---

# 3. Scope
## MVP / Required
- Migrasi tabel `task_attachments`:
  - `id`, `task_id`, `user_id` (pengunggah), `original_name`, `file_path`, `file_size`, `mime_type`, `created_at`, `updated_at`.
- Form upload file pada halaman detail/edit tugas atau langsung di kartu tugas:
  - Input file (`<input type="file">`) dengan atribut accept `.pdf,.doc,.docx,.zip,.jpg,.jpeg,.png`.
- Validasi berkas di backend:
  - MIME types: `pdf,doc,docx,zip,jpg,jpeg,png`.
  - Ukuran maksimal berkas: 10 MB (10.240 KB).
- Penyimpanan berkas menggunakan disk `local` atau `public` bawaan Laravel (`Storage::disk('public')->putFile('task-attachments', $file)`).
- Penamaan berkas otomatis menggunakan hash acak untuk mencegah *path traversal* dan tabrakan nama.
- Endpoint pengunduhan berkas yang aman (`Storage::download()`).
- Fitur penghapusan berkas lampiran (menghapus record database dan menghapus file fisik di disk penyimpanan).

## If Time Permits
- Pratinjau (*preview*) berkas gambar langsung di halaman tugas.

## Out of Scope
- Antivirus scanner cloud atau integrasi Amazon S3.
- Drag-and-drop file upload dengan chunking besar (>50MB).
- Versi berkas bertingkat (*file versioning history*).

---

# 4. Preconditions
- Fitur `prd-kelola-tugas-3.md` telah selesai diimplementasikan.
- Folder penyimpanan storage Laravel (`storage/app/public`) sudah dibuat dan perintah `php artisan storage:link` telah dijalankan.
- Pengguna yang melakukan upload adalah anggota atau pemilik workspace dari tugas yang bersangkutan.

---

# 5. Main User Flow
### Flow Mengunggah Berkas Tugas:
1. Pengguna membuka halaman workspace dan melihat tugas terkait (atau membuka modal/halaman detail tugas).
2. Di bawah kartu tugas, terdapat seksi "Lampiran / Berkas Tugas" dan tombol "Unggah Berkas".
3. Pengguna memilih berkas dari komputernya melalui input file (misal: `laporan_akhir.pdf`).
4. Pengguna menekan tombol "Unggah".
5. Browser mengirimkan HTTP request `POST /workspaces/{workspace}/tasks/{task}/attachments` dengan `enctype="multipart/form-data"` dan token CSRF.
6. Backend memverifikasi otorisasi pengguna terhadap workspace.
7. Backend memvalidasi ekstensi berkas, MIME type, dan ukuran berkas (maksimal 10MB).
8. Backend menyimpan file ke folder penyimpanan `storage/app/public/attachments/` dengan nama acak baru.
9. Backend mencatat metadata berkas (`original_name`, `file_path`, `file_size`, `mime_type`) ke tabel `task_attachments`.
10. Backend me-redirect kembali ke halaman workspace dengan flash message sukses *"Berkas berhasil diunggah."*.
11. Lampiran baru langsung tampil di bawah tugas terkait dengan tombol "Unduh" dan tombol "Hapus".

### Flow Mengunduh Berkas:
1. Pengguna mengklik tautan / tombol "Unduh" pada berkas yang diinginkan.
2. Browser mengirimkan HTTP request `GET /workspaces/{workspace}/tasks/{task}/attachments/{attachment}/download`.
3. Backend memeriksa otorisasi pengguna di workspace tersebut.
4. Backend memverifikasi keberadaan fisik file di storage.
5. Backend merespons dengan `Storage::download($filePath, $originalName)`.
6. File terunduh ke komputer pengguna dengan nama aslinya.

### Flow Menghapus Berkas:
1. Pengguna menekan tombol "Hapus" pada berkas lampiran.
2. Konfirmasi dialog native tampil: *"Hapus lampiran ini?"*.
3. Browser mengirimkan HTTP request `DELETE /workspaces/{workspace}/tasks/{task}/attachments/{attachment}` dengan token CSRF.
4. Backend memverifikasi bahwa pemohon adalah pengunggah berkas atau pemilik workspace.
5. Backend menghapus fisik berkas dari disk storage (`Storage::delete($filePath)`).
6. Backend menghapus baris data dari tabel `task_attachments`.
7. Backend me-redirect kembali dengan flash success *"Berkas berhasil dihapus."*.

---

# 6. Alternative Flow
- **Ukuran Berkas Melebihi Batas**:
  - Pengguna mencoba mengunggah berkas berukuran >10MB.
  - Backend validator `max:10240` menolak request dan me-redirect balik dengan pesan error: *"Ukuran berkas tidak boleh melebihi 10MB."*.
- **Tipe Berkas Dilarang**:
  - Pengguna mencoba mengunggah file berekstensi berbahaya (misal: `.exe`, `.php`, `.sh`).
  - Backend validator `mimes:pdf,doc,docx,zip,jpg,jpeg,png` memblokir berkas dengan pesan error: *"Format berkas tidak didukung. Hanya diperbolehkan PDF, DOC, DOCX, ZIP, JPG, dan PNG."*.
- **File Fisik Hilang**:
  - Record ada di database tetapi file fisik di storage terhapus.
  - Backend saat download memeriksa `Storage::exists()`, jika tidak ada kembalikan HTTP 404 dengan pesan *"Berkas fisik tidak ditemukan di server."*.
- **Akses Ilegal dari Luar**:
  - Pengguna yang bukan anggota mencoba mengunduh atau mengunggah lampiran.
  - Backend menolak dengan status HTTP 403 Forbidden.

---

# 7. Low-Fidelity UI Design
```text
+-------------------------------------------------------------------------------+
| DETAIL TUGAS & LAMPIRAN                                                       |
+-------------------------------------------------------------------------------+
| Judul: Membuat Laporan Praktikum Modul 1                                      |
| Status: [ Belum Selesai ]  •  Prioritas: [ PENTING ]                          |
|                                                                               |
| BERKAS LAMPIRAN TUGAS (2 Berkas):                                             |
| +---------------------------------------------------------------------------+ |
| | [PDF] draft_laporan_v1.pdf (2.4 MB)                                       | |
| |       Diunggah oleh: Budi  •  11 Sep 2026 09:30       [Unduh]  [Hapus]    | |
| |                                                                           | |
| | [ZIP] source_code_praktikum.zip (5.1 MB)                                  | |
| |       Diunggah oleh: Siti  •  11 Sep 2026 10:15       [Unduh]  [Hapus]    | |
| +---------------------------------------------------------------------------+ |
|                                                                               |
| FORM UNGGAH BERKAS BARU                                                       |
| +---------------------------------------------------------------------------+ |
| | Pilih Berkas (Maksimal 10 MB - PDF, DOCX, ZIP, PNG, JPG):                 | |
| | [ Choose File ]  No file chosen                                           | |
| | [ Error: ukuran file terlalu besar ]                                      | |
| |                                                                           | |
| |                                                        [ + Unggah Berkas ]| |
| +---------------------------------------------------------------------------+ |
+-------------------------------------------------------------------------------+
```

---

# 8. Visual Design & Tailwind Guidance
- **Attachment Section**: `mt-4 pt-3 border-t border-gray-100`
- **Attachment Item Row**:
  - Container: `flex items-center justify-between p-2.5 bg-gray-50 rounded-lg border border-gray-200 mb-2 text-xs`
  - File Icon & Name: `flex items-center gap-2 font-medium text-gray-800 hover:text-blue-600`
  - Meta Size & Uploader: `text-gray-400 text-xs`
  - Action Group: `flex items-center gap-3`
  - Download Link: `text-blue-600 hover:text-blue-800 font-semibold flex items-center gap-1`
  - Delete Button: `text-red-500 hover:text-red-700 font-medium`
- **Upload Form**:
  - Wrapper: `mt-3 p-3 bg-blue-50/50 rounded-lg border border-dashed border-blue-200`
  - File Input: `block w-full text-xs text-gray-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer`
  - Submit Button: `mt-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold py-1.5 px-3 rounded transition`

---

# 9. UI States
- **Default State**: Menampilkan daftar berkas lampiran yang ada dan form input upload.
- **Empty State**: Jika belum ada lampiran pada tugas tersebut, tampilkan teks ringkas: *"Belum ada berkas yang diunggah untuk tugas ini."*.
- **Submitting State**: Native browser file upload submission (indikator bar status browser aktif saat berkas di-upload).
- **Validation Error State**: Pesan error merah tampil tepat di bawah input file.
- **Success State**: Lampiran baru muncul di urutan teratas list setelah redirect dengan flash success hijau.

---

# 10. Error Container Specification
- **Field-level Error (Input File)**:
  - Lokasi: Tepat di bawah input berkas.
  - Blade Syntax:
    ```html
    @error('attachment')
        <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
    @enderror
    ```
- **Form-level Flash Error**:
  - Ditampilkan di atas konten tugas jika terjadi kesalahan sistem atau hak akses file.
- **Delete Confirmation**:
  - `onsubmit="return confirm('Apakah Anda yakin ingin menghapus berkas lampiran ini?')"`

---

# 11. Form Specification
### 1. Berkas Lampiran (Attachment)
- HTML: `type="file"`
- Name: `attachment`
- Form Attribute: `enctype="multipart/form-data"` WAJIB ada pada elemen `<form>`.
- Frontend: `required`, `accept=".pdf,.doc,.docx,.zip,.jpg,.jpeg,.png"`
- Backend: `required|file|mimes:pdf,doc,docx,zip,jpg,jpeg,png|max:10240`
- Database Fields Terkait:
  - `original_name`: `VARCHAR(255) NOT NULL`
  - `file_path`: `VARCHAR(255) NOT NULL`
  - `file_size`: `BIGINT UNSIGNED NOT NULL` (dalam satuan bytes)
  - `mime_type`: `VARCHAR(100) NOT NULL`

---

# 12. Frontend Validation
- Atribut `required` pada elemen `<input type="file">`.
- Atribut `accept=".pdf,.doc,.docx,.zip,.jpg,.jpeg,.png"` untuk membatasi pemilih berkas di sistem operasi.
- *Peringatan*: Frontend validation tidak diperlakukan sebagai batas keamanan; backend validation adalah penentu utama.

---

# 13. Backend Validation
Validation Rules pada `TaskAttachmentController@store`:
```php
$request->validate([
    'attachment' => [
        'required',
        'file',
        'mimes:pdf,doc,docx,zip,jpg,jpeg,png',
        'max:10240', // Maksimal 10 MB (10240 KB)
    ],
]);
```

---

# 14. Input Sanitization & XSS Prevention
- Nama berkas asli (`original_name`) selalu di-render menggunakan `{{ $attachment->original_name }}`.
- Nama berkas asli **TIDAK PERNAH** digunakan langsung sebagai nama berkas di server fisik (*filesystem*).
- Server menghasilkan hash acak otomatis menggunakan `$file->hashName()` atau `putFile()` untuk mencegah eksploitasi ekstensi ganda (*double extension attack*, misal `exploit.php.jpg`) atau *directory traversal*.

---

# 15. SQL Injection Prevention
- Data lampiran disimpan dan dicari menggunakan Eloquent Query Builder:
  `$task->attachments()->create(...)`
  `$attachment->delete()`
- Tidak ada raw concatenation query.

---

# 16. Database Design
Tabel `task_attachments`:
```text
task_attachments
--------------------------------------------------------
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
task_id             BIGINT UNSIGNED NOT NULL
user_id             BIGINT UNSIGNED NOT NULL (Pengunggah)
original_name       VARCHAR(255) NOT NULL
file_path           VARCHAR(255) NOT NULL
file_size           BIGINT UNSIGNED NOT NULL
mime_type           VARCHAR(100) NOT NULL
created_at          TIMESTAMP NULL
updated_at          TIMESTAMP NULL

FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
```

---

# 17. Database Constraints
- `task_id`: `NOT NULL`, FK `tasks.id`.
- `user_id`: `NOT NULL`, FK `users.id`.
- `original_name`: `NOT NULL`.
- `file_path`: `NOT NULL`.
- `file_size`: `NOT NULL`.

---

# 18. Foreign Key Behavior
- `task_id` -> `tasks.id`: `ON DELETE CASCADE`. Jika tugas dihapus, seluruh record lampirannya di database ikut terhapus otomatis.
- `user_id` -> `users.id`: `ON DELETE CASCADE`.

---

# 19. Duplicate Handling
- Dua pengguna dapat mengunggah file dengan nama asli yang sama (misal `tugas.pdf`). Hal ini aman karena nama file fisik di server dibuat acak dan unik (`hashName()`), serta setiap baris memiliki ID unik tersendiri di database.

---

# 20. HTTP Contract
### 1. Unggah Lampiran Baru
- **Method & URI**: `POST /workspaces/{workspace}/tasks/{task}/attachments`
- **Middleware**: `auth`
- **Header**: `Content-Type: multipart/form-data`
- **Request Payload**:
  - `_token`: string (CSRF)
  - `attachment`: file binary (PDF, DOCX, ZIP, JPG, PNG)
- **Success**: `302 Redirect` ke `/workspaces/{workspace}` dengan flash success *"Berkas berhasil diunggah."*.
- **Failure**: `302 Redirect Back` dengan session `$errors`.

### 2. Unduh Lampiran
- **Method & URI**: `GET /workspaces/{workspace}/tasks/{task}/attachments/{attachment}/download`
- **Middleware**: `auth`
- **Response**: Binary Stream (`Storage::download()`) dengan header MIME type yang sesuai dan header `Content-Disposition: attachment; filename="[original_name]"`.

### 3. Hapus Lampiran
- **Method & URI**: `DELETE /workspaces/{workspace}/tasks/{task}/attachments/{attachment}`
- **Middleware**: `auth`
- **Request Payload**:
  - `_token`: string (CSRF)
  - `_method`: `DELETE`
- **Success**: `302 Redirect` ke `/workspaces/{workspace}` dengan flash success *"Berkas berhasil dihapus."*.

---

# 21. Controller Responsibilities
`app/Http/Controllers/TaskAttachmentController.php`:
- `store(Request $request, Workspace $workspace, Task $task)`:
  1. Otorisasi keanggotaan workspace pengguna.
  2. Validasi file upload (`mimes`, `max:10240`).
  3. Simpan file ke storage: `$path = $request->file('attachment')->store('task-attachments', 'public')`.
  4. Simpan metadata ke tabel `task_attachments`.
  5. Redirect dengan flash message.
- `download(Workspace $workspace, Task $task, TaskAttachment $attachment)`:
  1. Otorisasi keanggotaan workspace.
  2. Verifikasi fisik: `Storage::disk('public')->exists($attachment->file_path)`.
  3. Return `Storage::disk('public')->download($attachment->file_path, $attachment->original_name)`.
- `destroy(Workspace $workspace, Task $task, TaskAttachment $attachment)`:
  1. Otorisasi: Hanya pengunggah file (`$attachment->user_id === auth()->id()`) atau pemilik workspace yang boleh menghapus.
  2. Hapus file fisik: `Storage::disk('public')->delete($attachment->file_path)`.
  3. Hapus record DB: `$attachment->delete()`.
  4. Redirect dengan flash message.

---

# 22. Model Responsibilities
`app/Models/TaskAttachment.php`:
- `$fillable = ['task_id', 'user_id', 'original_name', 'file_path', 'file_size', 'mime_type']`.
- Relasi:
  ```php
  public function task()
  {
      return $this->belongsTo(Task::class);
  }

  public function uploader()
  {
      return $this->belongsTo(User::class, 'user_id');
  }
  ```
- Helper format ukuran file:
  ```php
  public function getFormattedSizeAttribute(): string
  {
      return round($this->file_size / 1024, 1) . ' KB';
  }
  ```

---

# 23. Authorization
- Pengguna hanya boleh mengunggah atau mengunduh jika memiliki akses ke workspace dari tugas tersebut (baik sebagai pemilik atau anggota).
- Penghapusan lampiran hanya diperbolehkan bagi:
  1. Pengunggah berkas tersebut (`$attachment->user_id === auth()->id()`), ATAU
  2. Pemilik workspace (`$workspace->user_id === auth()->id()`).

---

# 24. CSRF
- Request upload berkas (`POST`) dan request hapus berkas (`DELETE`) wajib menyertakan direktif `@csrf`.

---

# 25. Mass Assignment Protection
- Nilai `task_id` dan `user_id` tidak diambil dari input form, melainkan dipasang langsung dari server:
  `$attachmentData['user_id'] = auth()->id();`
  `$task->attachments()->create($attachmentData);`

---

# 26. Error Handling
- Berkas tidak valid / terlalu besar: Kembali ke form dengan error message ramah pengguna tanpa membocorkan path direktori server.
- Berkas fisik tidak ada di disk: Mengembalikan HTTP 404 *"Berkas tidak ditemukan."*.
- Kegagalan penyimpanan: Jika insert database gagal setelah file tersimpan, hapus file fisik yang baru dibuat untuk mencegah sampah disk.

---

# 27. Transactions
- Gunakan `DB::transaction` saat mengunggah atau menghapus berkas:
  ```php
  DB::transaction(function () use ($path, $file, $task) {
      $task->attachments()->create([...]);
  });
  ```
  Jika database gagal, hapus file fisik yang sempat tersimpan.

---

# 28. Race Conditions
- Penamaan file menggunakan hash acak (`store()` Laravel) mencegah tabrakan nama file antar pengguna yang upload pada milidetik yang sama.

---

# 29. Delete Behavior
- **Dual Deletion**:
  1. File fisik dihapus dari disk `Storage::disk('public')->delete(...)`.
  2. Baris data dihapus dari database.
- **Konfirmasi**: Dialog konfirmasi sebelum submit delete request.

---

# 30. Empty State
- Teks ringkas pada kartu tugas: *"Belum ada berkas lampiran."*.

---

# 31. Pagination
- Lampiran per tugas biasanya berjumlah sedikit (1-5 berkas), sehingga ditampilkan seluruhnya tanpa paginasi.

---

# 32. Search / Filter
- Tidak diperlukan untuk lampiran tugas.

---

# 33. Accessibility Minimum
- Input file memiliki label yang jelas: "Pilih Berkas Lampiran".
- Tombol unduh dan hapus memiliki nama aksi yang eksplisit.

---

# 34. Responsive Behavior
- Pada tampilan mobile, baris lampiran membungkus teks nama file dan menempatkan tombol unduh/hapus di baris bawahnya (*flex-wrap*).

---

# 35. Edge Cases
- Pengguna mengunggah file tanpa ekstensi: Ditolak oleh validator `mimes`.
- Karakter aneh / spasi pada nama file asli: Tetap aman disimpan di database dan di-escape saat diunduh via parameter `Content-Disposition`.
- File ukuran 0 bytes: Ditolak oleh validator `file`.

---

# 36. Security Checklist
- [x] Validasi MIME type dan ekstensi secara ketat di backend.
- [x] Batas ukuran maksimal file ditegakkan (10MB).
- [x] File fisik disimpan dengan hash acak di direktori non-executable.
- [x] Tidak menggunakan nama berkas dari pengguna sebagai nama file di disk server.
- [x] Verifikasi otorisasi keanggotaan sebelum melayani unduhan berkas.
- [x] Token CSRF aktif pada request upload dan delete berkas.
- [x] Pembersihan file fisik saat record lampiran dihapus.

---

# 37. Testing Strategy
## Happy Path
- Pilih file `dokumen.pdf` (1 MB), klik "Unggah Berkas" -> Berkas muncul di daftar lampiran dengan ukuran tertera.
- Klik tombol "Unduh" -> Browser mengunduh file dengan nama `dokumen.pdf`.
- Klik tombol "Hapus", konfirmasi OK -> Berkas terhapus dari daftar dan file fisik terhapus dari disk.

## Validation Tests
- Upload file `.exe` atau `.php` -> Validasi gagal dengan error format tidak didukung.
- Upload file >10MB -> Validasi gagal dengan error batas ukuran terlewati.

## Security Tests
- Akses URL download attachment dari akun yang bukan anggota workspace -> Mendapatkan HTTP 403 Forbidden.

---

# 38. Acceptance Criteria
- [ ] Tersedia migrasi tabel `task_attachments` dengan foreign key ke `tasks` dan `users`.
- [ ] Pengguna dapat mengunggah berkas format PDF, DOC, DOCX, ZIP, JPG, PNG maksimal 10MB ke suatu tugas.
- [ ] Pengguna anggota workspace dapat mengunduh berkas lampiran.
- [ ] Berkas disimpan dengan nama acak aman di disk storage.
- [ ] Pengunggah berkas atau pemilik workspace dapat menghapus berkas lampiran.
- [ ] Menghapus lampiran menghapus file fisik di storage.

---

# 39. Definition of Done
- Migrasi `task_attachments` selesai dieksekusi.
- `php artisan storage:link` aktif.
- Model `TaskAttachment` dan relasi ke `Task` dan `User` terpasang.
- `TaskAttachmentController` menangani store, download, dan destroy dengan aman.
- UI form upload dan list lampiran terintegrasi pada view tugas di Blade.
- Seluruh acceptance criteria dan pengujian keamanan lulus.

---

# 40. Implementation Order Inside Feature
1. Buat migrasi `create_task_attachments_table`.
2. Jalankan migrasi dan pastikan symlink storage terhubung (`php artisan storage:link`).
3. Buat model `TaskAttachment.php` beserta relasinya ke `Task` dan `User`.
4. Daftarkan routes upload, download, dan delete lampiran di `routes/web.php`.
5. Buat `TaskAttachmentController.php` dengan method `store`, `download`, dan `destroy`.
6. Integrasikan komponen Blade lampiran dan form upload pada view tugas di `resources/views/workspaces/show.blade.php`.
7. Uji coba pengunggahan, pengunduhan berkas asli, dan penghapusan berkas.

---

# 41. Estimated 60-Minute Breakdown
- **00–10 menit**: Migrasi `task_attachments`, model setup, dan storage configuration.
- **10–25 menit**: Controller logic untuk `store` (validasi MIME/size + penyimpanan file) dan `download`.
- **25–40 menit**: Controller logic untuk `destroy` (file deletion) dan otorisasi akses.
- **40–50 menit**: Tampilan UI Blade lampiran (list berkas, ukuran, tombol download & delete, form upload).
- **50–60 menit**: Pengujian upload aneka format file, pengujian validasi ukuran, dan verifikasi download.

---

# 42. Explicit Non-Requirements
- Tidak ada integrasi cloud storage (AWS S3, Google Cloud Storage); menggunakan filesystem storage lokal bawaan Laravel.
- Tidak ada fitur scan virus antivirus pihak ketiga.
- Tidak ada pemrosesan kompresi gambar atau konversi PDF di server.
