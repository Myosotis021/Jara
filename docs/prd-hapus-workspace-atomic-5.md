# PRD: Penghapusan Workspace Secara Atomic dengan Pembersihan File Lampiran

## 1. Title
PRD - Penghapusan Workspace Secara Atomic dengan Pembersihan File Lampiran

## 2. Feature Name
Penghapusan Workspace Secara Atomic dengan Pembersihan File Lampiran

## 3. Description
Fitur ini memodifikasi proses penghapusan (delete) workspace yang sudah ada agar berjalan secara atomic menggunakan database transaction dan memastikan bahwa file lampiran fisik (physical files) yang terkait dengan tugas di dalam workspace tersebut ikut terhapus dari sistem penyimpanan, mencegah terjadinya orphaned files.

## 4. Objective
- Mencegah penumpukan file yatim (orphaned files) di penyimpanan lokal setelah workspace dihapus.
- Memastikan integritas data saat penghapusan workspace: semua entitas terkait terhapus dalam satu transaksi atomic atau digagalkan semua bila terjadi error.

## 5. Target Audience / Actor
- Pemilik Workspace (Owner): Pengguna yang memiliki hak untuk menghapus workspace beserta seluruh isinya.

## 6. Value Proposition
Menjaga efisiensi kapasitas disk server dan menghindari data corrupt/tidak sinkron akibat kegagalan saat proses penghapusan data secara berjenjang.

## 7. Status
Draft / Ready for Implementation

## 8. Priority
Tinggi (Mencegah storage leak)

## 9. Target Release / Order
Order 5

## 10. Version
1.0.0

## 11. Author
Tim Produk Jara

## 12. Date
2026-09-18

## 13. Terminology / Glossary
- **Atomic/Transaction**: Serangkaian operasi database yang berhasil sepenuhnya atau digagalkan/rollback sepenuhnya.
- **Orphaned files**: File fisik yang tersisa di storage namun referensinya di database sudah terhapus.
- **CASCADE**: Fitur foreign key database untuk menghapus data terkait secara otomatis.

## 14. Scope
- Mengubah fungsi `destroy` pada `WorkspaceController`.
- Mengumpulkan file paths dari seluruh lampiran tugas di dalam workspace.
- Menambahkan `DB::transaction` saat menghapus `$workspace`.
- Menggunakan `Storage::disk('public')->delete()` untuk file paths yang terkumpul setelah transaksi sukses.
- Memodifikasi teks konfirmasi pada tombol hapus di UI (Blade view).

## 15. Out of Scope
- Penghapusan pengguna (User deletion).
- Pembuatan tabel database baru.
- Penambahan route baru.

## 16. Dependencies
- Fitur Autentikasi (prd-autentikasi-1.md)
- Fitur Kelola User (prd-kelola-user-2.md)
- Fitur Kelola Workspace (prd-kelola-workspace-2.md)
- Fitur Kolaborasi Workspace (prd-kolaborasi-workspace-3.md)
- Fitur Kelola Tugas (prd-kelola-tugas-3.md)
- Fitur Upload Tugas (prd-upload-tugas-4.md)

## 17. Assumptions
- Storage disk public sudah dikonfigurasi dengan benar di server lokal.
- Operasi delete database `CASCADE` sudah dikonfigurasi dengan benar pada schema tabel `tasks`, `task_attachments`, dan `workspace_members`.

## 18. Constraints
- Laravel 13, PHP 8.4, Docker environment.
- File storage lokal pada `storage/app/public/task-attachments/`.

## 19. Related Documents (PRDs)
- prd-autentikasi-1.md
- prd-kelola-user-2.md
- prd-kelola-workspace-2.md
- prd-kolaborasi-workspace-3.md
- prd-kelola-tugas-3.md
- prd-upload-tugas-4.md

## 20. User Flow (Main)
1. Pemilik workspace membuka halaman daftar workspace (GET /workspaces).
2. Pemilik menekan tombol Hapus pada card workspace miliknya.
3. Dialog konfirmasi native browser muncul dengan pesan baru: "Hapus workspace ini? Seluruh tugas dan file lampiran di dalamnya akan ikut terhapus permanen."
4. Pemilik mengonfirmasi.
5. Browser mengirim request `DELETE /workspaces/{workspace}`.
6. Backend memverifikasi bahwa user yang mengakses adalah pemilik (owner).
7. Backend mengumpulkan semua `file_path` dari `task_attachments` yang terkait dengan `tasks` di workspace ini.
8. Backend membuka `DB::transaction()`.
9. Di dalam transaksi, backend mengeksekusi `$workspace->delete()` (CASCADE otomatis menghapus data child).
10. Transaksi di-commit.
11. Backend menghapus file fisik di storage menggunakan kumpulan `file_path`.
12. Backend mengarahkan kembali (redirect) ke halaman `/workspaces` dengan pesan sukses.

## 21. User Flow (Alternative/Error)
- **Jika Transaksi Database Gagal**: `DB::transaction` akan otomatis melakukan rollback. Data workspace dan file fisiknya tidak ada yang dihapus. Backend meredirect dengan flash error "Gagal menghapus workspace. Silakan coba lagi."
- **Jika Penghapusan File Fisik Gagal**: Terjadi setelah transaksi database commit. Dianggap trade-off yang dapat diterima. Workspace tetap terhapus.

## 22. User Flow (Edge Cases)
- **Workspace tanpa tugas**: Proses mengumpulkan path kosong. Transaksi berjalan lancar. Tidak ada file dihapus.
- **Workspace dengan tugas namun tanpa lampiran**: Proses mengumpulkan path menghasilkan list kosong. Transaksi sukses, storage delete tidak dipanggil/kosong.
- **File fisik sudah tidak ada di disk**: Method `Storage::delete()` menangani hal ini tanpa error fatal (menjaga agar request tidak gagal).
- **Double-click tombol Hapus**: Request pertama memproses penghapusan; request kedua mengembalikan error 404 (Not Found).

## 23. User Interface / UX Requirements
- **Tombol Hapus**: Tidak ada perubahan desain tombol.
- **Konfirmasi**: Ubah atribut `onsubmit` atau `onclick` untuk menampilkan native confirm prompt menjadi: `confirm('Hapus workspace ini? Seluruh tugas dan file lampiran di dalamnya akan ikut terhapus permanen.')`

## 24. API / Endpoint Specification (HTTP Contract)
- **Endpoint**: `DELETE /workspaces/{workspace}`
- **Route Name**: `workspaces.destroy`
- **Middleware**: `auth`
- **Request Body**: (Tidak ada, dikirim via HTTP DELETE / Form spoofing).
- **Response**: Redirect ke `route('workspaces.index')` dengan `success` atau `error` flash session.

## 25. Data Model / Database Changes
- **Tidak ada skema baru.**
- Mengandalkan `ON DELETE CASCADE` yang sudah ada pada tabel:
  - `workspace_members`
  - `tasks`
  - `task_attachments`

## 26. File Storage
- Lokasi file: `storage/app/public/task-attachments/`
- Mekanisme penghapusan menggunakan fasad `Storage::disk('public')->delete($filePaths)`.

## 27. Security Requirements
- Memastikan `user_id` yang terhubung di workspace adalah `auth()->id()`.
- Middleware `auth` diwajibkan untuk endpoint ini.

## 28. Performance Requirements
- Pengambilan path file menggunakan Eager Loading `->with('attachments')` untuk menghindari N+1 queries.
- Estimasi response time harus di bawah 1 detik untuk workspace wajar.

## 29. Scalability Requirements
- Menggunakan `flatMap()` dan array operations bawaan collection Laravel efisien untuk ukuran tugas di bawah 10.000 (cukup untuk sistem To-Do list).

## 30. Reliability / Availability Requirements
- `DB::transaction` sangat krusial untuk memastikan sistem tidak berada di status data sebagian-terhapus.

## 31. Error Handling / Logging
- Try-catch block di controller.
- Jika database rollback, berikan pesan 'error' ke view.

## 32. Auditing / Tracking
- (Opsional) Tidak ada skema log audit sistematic yang dipersyaratkan saat ini.

## 33. Technical Strategy / Architecture
1. Ambil ID workspace.
2. Query path: `tasks()->with('attachments')->get()->flatMap(...)->toArray()`.
3. Mulai Transaksi.
4. Delete workspace (memanfaatkan CASCADE FK di RDBMS).
5. Catch Throwable.
6. Commit.
7. Delete physical storage if array not empty.

## 34. Code Implementation / Controller Logic
```php
public function destroy(Workspace $workspace): RedirectResponse
{
    if ($workspace->user_id !== auth()->id()) {
        abort(403, 'Hanya pemilik yang dapat mengubah atau menghapus workspace ini.');
    }

    // Kumpulkan path file dari lampiran tugas workspace ini
    $filePaths = $workspace->tasks()
        ->with('attachments')
        ->get()
        ->flatMap(fn ($task) => $task->attachments->pluck('file_path'))
        ->filter()
        ->toArray();

    try {
        DB::transaction(function () use ($workspace) {
            $workspace->delete();
        });
    } catch (\Throwable $e) {
        return redirect()->route('workspaces.index')
            ->with('error', 'Gagal menghapus workspace. Silakan coba lagi.');
    }

    if (!empty($filePaths)) {
        Storage::disk('public')->delete($filePaths);
    }

    return redirect()->route('workspaces.index')
        ->with('success', 'Workspace berhasil dihapus.');
}
```

## 35. Testing Strategy
- Uji hapus workspace berisi banyak task dan attachment. Pastikan DB terhapus, dan file lokal hilang.
- Uji trigger exception di dalam closure db transaction, pastikan tidak ada data dan file yang terhapus.
- Uji pengguna non-owner mencoba menghapus. (Harus abort 403).

## 36. Rollout Plan
- Deploy di environment dev lokal menggunakan Docker.
- Verifikasi tabel cascades berjalan di MySQL 8.0 db container.

## 37. Success Metrics
- 0% orphaned files tersisa setelah menghapus workspace.
- 100% atomic rollbacks jika ada kegagalan query SQL.

## 38. Risks and Mitigations
- **Risiko**: File gagal dihapus, tetapi transaksi sukses.
- **Mitigasi**: Ini dianggap edge case / trade-off yang wajar agar pengguna tidak diblokir. Tidak ada masalah integritas data DB yang terjadi.

## 39. Compliance / Legal
- Mematuhi aturan penghapusan data secara permanen saat pengguna ingin menghapus workspace milik mereka.

## 40. Open Questions
- Apakah perlu mengirim email notifikasi ke workspace_members jika dihapus? (Keputusan saat ini: Tidak perlu).

## 41. Approvals
- Disetujui oleh: Product Owner
- Tanggal: 2026-09-18

## 42. Appendix / References
- Dokumentasi Laravel Eloquent Collections
- Dokumentasi Laravel Storage Fasad
- PRD Jara sebelumnya (Docs)
