# PRD: Penghapusan Pengguna oleh Admin Secara Atomic dengan Pembersihan File Lampiran

## 1. Title
Penghapusan Pengguna oleh Admin Secara Atomic dengan Pembersihan File Lampiran

## 2. Feature Name
Hapus User Atomic & File Cleanup

## 3. Description
Perbaikan dan penyesuaian fungsionalitas penghapusan pengguna (user) oleh Administrator. Proses ini diubah menjadi *atomic* menggunakan transaksi database, serta menambahkan mekanisme pembersihan (penghapusan) berkas-berkas fisik atau lampiran (*file attachments*) yang terkait dengan pengguna yang dihapus, baik pada *workspace* miliknya maupun lampiran yang diunggah olehnya di *workspace* lain.

## 4. Objective
- Memastikan bahwa penghapusan data pengguna dan seluruh dependensinya (CASCADE) dalam database bersifat *atomic*.
- Mencegah adanya berkas lampiran yang menjadi "sampah" (*orphaned files*) pada sistem penyimpanan fisik (*storage*) setelah pengguna dihapus.

## 5. Scope
- Memodifikasi fungsi `destroy` pada `Admin\UserController`.
- Mengumpulkan seluruh path file lampiran dari `task_attachments` sebelum pengguna dihapus.
- Membungkus perintah hapus pengguna (`$user->delete()`) di dalam blok `DB::transaction()`.
- Melakukan penghapusan file fisik dari `Storage::disk('public')` apabila transaksi berhasil dikomit.

## 6. Out of Scope
- Perubahan tampilan antarmuka (UI) untuk konfirmasi penghapusan (tetap menggunakan UI yang ada).
- Perubahan pada skema database atau *migration* (semua constraint CASCADE sudah diatur dari awal).
- Penghapusan file selain lampiran tugas (*task attachments*), karena saat ini hanya entitas tersebut yang memiliki *file attachment* fisik terkait user.

## 7. Target Audience / Actors
- **Administrator (admin):** Satu-satunya aktor yang memiliki hak akses (*authorization*) untuk melakukan aksi ini.

## 8. User Stories
- Sebagai **Admin**, saya ingin saat menghapus akun pengguna, seluruh file lampirannya yang ada di server juga terhapus, sehingga ruang penyimpanan (storage) server tetap efisien dan tidak ada *orphaned files*.

## 9. Use Cases
- Admin memilih opsi hapus pada baris data pengguna di halaman Manajemen Pengguna.
- Sistem memvalidasi apakah pengguna yang dihapus bukan Admin yang sedang *login*.
- Sistem mengumpulkan daftar file yang akan dihapus.
- Sistem menghapus data pengguna (dan relasinya via CASCADE) secara atomic.
- Sistem menghapus file fisik di penyimpanan.
- Sistem mengembalikan Admin ke halaman Manajemen Pengguna dengan pesan sukses (atau *error* jika gagal).

## 10. System Requirements
- PHP 8.4, Laravel 13, Database MySQL 8.0.
- Docker environment (app container, db container).

## 11. Functional Requirements
- Endpoint `DELETE /admin/users/{user}` wajib mematuhi aturan *atomic deletion*.
- Daftar file fisik yang akan dihapus meliputi:
  1. Berkas dari lampiran pada tugas-tugas di dalam *workspace* yang DIMILIKI (owned) oleh pengguna tersebut (karena workspace-nya akan terhapus via CASCADE).
  2. Berkas dari lampiran yang DIUNGGAH oleh pengguna tersebut di *workspace* apa pun.
- Penggabungan (*merge*) dan penghapusan duplikasi (*deduplicate*) dari daftar file di atas wajib dilakukan.
- Eksekusi hapus di database di-wrap di `DB::transaction`.
- Proteksi pencegahan penghapusan akun mandiri (*self-deletion*) harus dipertahankan.

## 12. Non-Functional Requirements
- Waktu respons (*response time*) penghapusan diharapkan maksimal 2-3 detik walaupun file yang dihapus banyak.
- *Graceful error handling*: Jika terjadi kesalahan (database timeout, dsb), tampilkan *flash message error*.

## 13. Business Rules
- Pengguna dengan hak Admin tidak boleh menghapus dirinya sendiri (*self-deletion check*).
- File fisik hanya dihapus setelah *commit* transaksi database berhasil dilakukan.
- Jika penghapusan file fisik gagal parsial, hal ini dapat diterima (*acceptable trade-off*) selama data di database terjamin *atomic*.

## 14. Workflows
1. Menerima request `DELETE`.
2. Validasi ID user target !== ID auth user.
3. Query `ownedWorkspaceFilePaths`.
4. Query `uploadedFilePaths`.
5. Merge & Deduplicate menjadi `$filePaths`.
6. Blok `try`: `DB::transaction` -> `$user->delete()`.
7. Blok `catch`: return redirect dengan pesan *error*.
8. Jika `try` berhasil: `Storage::disk('public')->delete($filePaths)`.
9. Redirect dengan pesan *success*.

## 15. User Interface (UI) Design / Wireframes
- Tidak ada perubahan UI. Tetap menggunakan tombol Hapus dan alert konfirmasi *native browser* bawaan di halaman `admin.users.index`.

## 16. Database Schema Changes
- **Tidak ada**. Mekanisme CASCADE pada relasi tabel `users`, `workspaces`, `workspace_members`, `tasks`, dan `task_attachments` sudah beroperasi dengan benar.

## 17. API/HTTP Endpoints
- **Method:** `DELETE`
- **Path:** `/admin/users/{user}`
- **Route Name:** `admin.users.destroy`
- **Middleware:** `auth`, `isAdmin`

## 18. Security
- Wajib melalui *middleware* otentikasi.
- Wajib memiliki peranan admin (`isAdmin`).
- Pencegahan penghapusan sesi aktif diri sendiri oleh admin.

## 19. Performance
- Query pengumpulan data file `pluck('file_path')` cukup cepat dan ringan tanpa *load* relasi penuh.

## 20. Accessibility
- (N/A) UI tidak berubah.

## 21. Error Handling
- Semua operasi penghapusan data database dilakukan dalam `try...catch` blok.
- `\Throwable $e` dicatch, lalu pengguna di-redirect kembali dengan `with('error', 'Gagal menghapus pengguna. Silakan coba lagi.')`.

## 22. Edge Cases
- Menghapus pengguna yang tidak memiliki file sama sekali: `$filePaths` akan bernilai array kosong, method `Storage::delete` dihindari dengan mengecek `!empty($filePaths)`.
- Kesamaan file (meskipun sangat jarang menggunakan *hashname*): fungsi `unique()` mencegah *error* saat menghapus.

## 23. Data Migration
- (N/A)

## 24. Localization / Internalization
- Pesan-pesan flash *error* atau *success* menggunakan bahasa Indonesia sesuai yang sudah ada sebelumnya.

## 25. Testing Strategy
- Unit/Feature testing membuat user, membuat beberapa task, mengunggah beberapa attachment (di workspace miliknya, dan workspace orang lain).
- Hit ke endpoint DELETE, pastikan respons `302` redirect, data di database hilang semua via CASCADE, dan file fisik tidak ada di *storage*.

## 26. Acceptance Criteria
- [ ] Admin tidak bisa menghapus akun sendiri.
- [ ] Proses penghapusan menggunakan transaksi database (`DB::transaction`).
- [ ] File lampiran di workspace yang dimiliki user dihapus secara fisik dari disk.
- [ ] File lampiran yang diunggah oleh user di workspace lain dihapus secara fisik dari disk.
- [ ] Ketika transaksi database gagal, data dan file tidak ada yang dihapus.

## 27. Analytics / Tracking
- (N/A)

## 28. Deployment Strategy
- Rollout biasa, tidak ada instruksi tambahan.

## 29. Rollback Plan
- *Revert* kode controller kembali ke versi semula jika terjadi kendala pada implementasi ini.

## 30. Documentation
- Perlu mendokumentasikan rantai cascade besar pada sistem (users -> workspaces -> tasks -> task_attachments) dan implikasinya terhadap file storage.

## 31. Dependencies
- Prasyarat: PRD terkait Manajemen User, Autentikasi, Kolaborasi Workspace, dan Manajemen Tugas & Lampiran telah diselesaikan (Dependencies PRD order 1 - 4).

## 32. Risks & Mitigations
- **Risiko:** Kegagalan saat memanggil `Storage::delete` membuat file tetap bersisa (*orphaned*).
- **Mitigasi:** File orpan bukan critical risk; selama database konsisten, aplikasi tetap berjalan lancar. Proses *clean-up script* dapat dibuat terpisah nantinya jika perlu.

## 33. Assumptions
- Semua file disimpan di disk `public` (local).
- Tidak ada penyimpanan Cloud eksternal seperti S3 di implementasi saat ini.

## 34. Constraints
- Wajib menggunakan environment yang sudah ditentukan: Docker `app` container, PHP 8.4.

## 35. Timeline / Milestones
- Estimasi waktu pengerjaan (development & testing): ~45 menit. Dapat dikerjakan paralel dengan implementasi fitur berurutan ke-5 lainnya.

## 36. Cost / Budget
- (N/A)

## 37. Approvals
- (TBD)

## 38. Glossary
- **Atomic:** Semua bagian proses berhasil atau seluruhnya dibatalkan, tidak ada yang setengah-setengah.
- **CASCADE:** Mekanisme SQL dimana jika suatu *record* induk dihapus, *record* turunannya yang memiliki referensi *Foreign Key* ikut terhapus.
- **Orphaned files:** File yatim piatu; berada di media penyimpanan tapi tidak memiliki referensi lagi di *database*.

## 39. Appendix
*Contoh Kode Kontroler yang Diharapkan:*
```php
public function destroy(User $user)
{
    if ($user->id === auth()->id()) {
        return redirect()->route('admin.users.index')
            ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
    }

    // Collect file paths
    $ownedWorkspaceFilePaths = \App\Models\TaskAttachment::whereHas('task.workspace', function ($q) use ($user) {
        $q->where('user_id', $user->id);
    })->pluck('file_path');

    $uploadedFilePaths = \App\Models\TaskAttachment::where('user_id', $user->id)->pluck('file_path');

    $filePaths = $ownedWorkspaceFilePaths->merge($uploadedFilePaths)->unique()->filter()->toArray();

    try {
        DB::transaction(function () use ($user) {
            $user->delete();
        });
    } catch (\Throwable $e) {
        return redirect()->route('admin.users.index')
            ->with('error', 'Gagal menghapus pengguna. Silakan coba lagi.');
    }

    if (!empty($filePaths)) {
        Storage::disk('public')->delete($filePaths);
    }

    return redirect()->route('admin.users.index')
        ->with('success', 'Pengguna berhasil dihapus.');
}
```

## 40. Revision History
- 1.0 - Inisialisasi PRD

## 41. Notes
- File fisik dihapus hanya SETELAH database sukses terhapus; kita tidak bisa menghapus file sebelumnya sebab apabila terjadi *rollback*, file yang sudah terhapus di disk tak bisa dipulihkan dengan transaksi SQL.

## 42. Sign-off
- **Project Manager:** [Nama]
- **Lead Developer:** [Nama]
- **Date:** 18 September 2026
