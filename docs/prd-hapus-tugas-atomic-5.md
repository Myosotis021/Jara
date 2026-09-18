# Product Requirements Document (PRD)

## 1. Nama Fitur
Penghapusan Tugas Secara Atomic dengan Pembersihan File Lampiran

## 2. Deskripsi Singkat
Modifikasi fungsi penghapusan tugas individu (Task) agar berjalan secara atomic (menggunakan `DB::transaction`) sekaligus membersihkan file fisik lampiran (attachment) yang terkait di dalam disk server (`storage/app/public/task-attachments/`) ketika tugas dihapus.

## 3. Tujuan
- Memastikan integritas data ketika menghapus sebuah tugas.
- Menghemat kapasitas penyimpanan server dengan menghapus file lampiran (orphaned files) yang sudah tidak digunakan di dalam *disk*.

## 4. Masalah yang Diselesaikan
Saat ini, proses penghapusan tugas menggunakan `$task->delete()` tanpa transaksi basis data (`DB::transaction`). Perilaku standar basis data (CASCADE) menghapus data `task_attachments`, tetapi file fisik dari lampiran tersebut di dalam `storage/app/public/task-attachments/` tidak ikut terhapus, sehingga menjadi *orphaned files* (file yatim) yang memenuhi penyimpanan *server*. Selain itu, karena tidak menggunakan transaksi, jika terjadi kesalahan (misalnya server terputus), bisa terjadi kegagalan penghapusan.

## 5. Target Pengguna
- Pengguna yang memiliki akses ke *workspace* (baik Pemilik maupun Anggota *workspace*).

## 6. Batasan / Ruang Lingkup
- Hanya mengubah logika backend (controller) pada proses penghapusan tugas.
- Tidak ada perubahan pada Antarmuka Pengguna (UI). Tombol hapus dan modal konfirmasi yang sudah ada tetap dipertahankan.
- Tidak ada migrasi basis data tambahan atau rute (routes) baru.

## 7. Prasyarat
- *Workspace* dan fitur Tugas beserta lampirannya sudah terimplementasi dan berfungsi.
- Konfigurasi `Storage::disk('public')` sudah diatur dengan benar dan beroperasi.

## 8. Ketergantungan (Dependencies)
- prd-autentikasi-1.md (Order 1)
- prd-kelola-user-2.md (Order 2)
- prd-kelola-workspace-2.md (Order 2)
- prd-kolaborasi-workspace-3.md (Order 3)
- prd-kelola-tugas-3.md (Order 3)
- prd-upload-tugas-4.md (Order 4)

## 9. Metrik Kesuksesan (Success Metrics)
- Data tugas (Task) dan semua catatannya di tabel `task_attachments` (melalui CASCADE) berhasil dihapus dari basis data.
- File-file fisik lampiran yang berelasi dengan tugas tersebut otomatis terhapus dari *storage* lokal.
- Kapasitas penyimpanan *storage* menjadi lebih lega karena tidak ada *orphaned files*.

## 10. Asumsi
- Pengguna sudah memahami bahwa tugas dan lampiran yang telah dihapus tidak dapat dipulihkan.
- `file_path` yang ada pada catatan tabel `task_attachments` sudah valid merujuk pada *storage public*.

## 11. Alur Pengguna (User Flow)
1. Pengguna membuka halaman detail *workspace*.
2. Pengguna mengklik tombol "Hapus" pada salah satu tugas.
3. Muncul konfirmasi (browser default dialog atau modal UI yang sudah ada) untuk menghapus.
4. Pengguna menyetujui.
5. Sistem (backend) memproses penghapusan secara atomic dan file fisik, kemudian me-*redirect* kembali ke halaman *workspace* dengan pesan sukses.

## 12. Kasus Penggunaan (Use Cases)
- **Menghapus tugas tanpa lampiran:** Sistem menghapus tugas secara transaksional tanpa perlu menghapus file apa pun.
- **Menghapus tugas dengan beberapa lampiran:** Sistem mengambil semua nama/path lampiran terkait, menghapus record tugas (dan lampirannya di basis data), lalu menghapus semua file yang dicatat dari disk lokal.

## 13. Kasus Tepi (Edge Cases)
- **Tugas sudah terhapus di sesi atau tab lain:** Pengguna mencoba menghapus tugas yang record-nya tidak lagi ada di DB. Laravel route model binding akan mengembalikan 404 (Not Found).
- **Proses transaksi basis data gagal:** Proses penghapusan digagalkan, *file* tidak dihapus dari sistem *storage*, sistem menampilkan pesan *error flash message*.
- **Transaksi DB berhasil tapi Storage gagal menghapus beberapa file:** Basis data tetap ter-update (penghapusan berhasil), sisa file yang gagal dihapus menjadi *orphaned file*. Ini adalah pengorbanan (*trade-off*) yang bisa diterima daripada mengembalikan transaksi DB setelah data terlanjur dimanipulasi atau *commit*.

## 14. Penanganan Kesalahan (Error Handling)
- Kesalahan saat transaksi DB (misal karena konflik database): Menggunakan blok `try-catch`, record gagal dihapus, *flash message error* ditampilkan: `"Gagal menghapus tugas. Silakan coba lagi."`.
- Kesalahan pada saat menghapus file di `Storage`: Proses file deletion dijalankan dalam kondisi sunyi (*fail silent*) sehingga tidak merusak alur penyelesaian *request* jika DB sudah berhasil commit.

## 15. Kebutuhan Fungsional
1. Saat penghapusan terjadi di rute `workspaces.tasks.destroy`:
   - Sistem WAJIB mengumpulkan (collect) properti `file_path` dari semua *attachment* yang berelasi dengan `$task` ke dalam sebuah *array*.
2. Operasi `delete()` pada objek `$task` WAJIB dibungkus di dalam `DB::transaction()`.
3. Setelah eksekusi transaksi selesai (commit berhasil), sistem baru boleh melakukan `Storage::disk('public')->delete($filePaths)` jika daftar file path tidak kosong.

## 16. Kebutuhan Non-Fungsional
- Eksekusi kode harus efisien tanpa adanya redundansi kueri. Gunakan `->pluck('file_path')->filter()->toArray()` pada *collection/relation* untuk mendapatkan *array of file path* yang siap dikonsumsi *Storage adapter*.

## 17. Kebutuhan Performa
- Modifikasi tidak menyebabkan penambahan beban *query* yang signifikan pada penghapusan satu *task*.

## 18. Kebutuhan Keamanan
- Penanganan otorisasi (yakni *middleware auth*, pemeriksaan ID *workspace* dan validasi `$workspace->hasAccess()`) yang sudah ada di `TaskController@destroy` tetap wajib dipertahankan untuk menjamin keamanan akses penghapusan.

## 19. Kebutuhan Skalabilitas
- Tidak ada dampak signifikan pada arsitektur sistem. Pembersihan file yatim (*orphaned files*) ini justru mendukung skalabilitas *disk storage*.

## 20. Antarmuka Pengguna (UI) & Pengalaman Pengguna (UX)
- Tidak ada perubahan.
- *Flash messages* harus merespons hasil aksi: Kesuksesan penghapusan atau pesan kegagalan (dari catch DB transaction error).

## 21. Kebutuhan Aksesibilitas
- Tidak relevan. (Sama seperti eksisting)

## 22. Kebutuhan Internasionalisasi (i18n)
- Tidak ada perubahan, bahasa utama yang dipakai di pesan kesalahan tetap menggunakan Bahasa Indonesia.

## 23. Desain Basis Data
- Tidak relevan. Tidak ada struktur baru atau tambahan migrasi basis data. Fungsionalitas bergantung pada efek relasi CASCADE yang sudah ada.

## 24. Desain API
- Tidak relevan (Proyek ini *monolith* dengan metode *session-based auth*). Titik akhir (Endpoint) tetap sama, yakni `DELETE /workspaces/{workspace}/tasks/{task}`.

## 25. Integrasi Pihak Ketiga
- Tidak relevan.

## 26. Arsitektur Sistem
- Tidak relevan. Masih menggunakan arsitektur bawaan MVC di Laravel.

## 27. Strategi Migrasi Data
- Tidak relevan karena tidak merubah skema atau perlu migrasi data apa pun.

## 28. Strategi Pengujian (Testing Strategy)
- Unit / Feature Test di PHPUnit untuk `TaskController@destroy`:
  - Membuat *Task* dan *TaskAttachment*.
  - Men-generate file bohongan (*dummy*) lewat *Storage fake*.
  - Melakukan *request DELETE*.
  - Memverifikasi bahwa data *Task* di basis data hilang.
  - Memverifikasi dengan fungsi `Storage::disk('public')->assertMissing($file)` file terhapus secara fisik.

## 29. Rencana Peluncuran (Deployment Plan)
- Di-*deploy* mengikuti standar integrasi versi Git. *Pull/merge* langsung dari *branch* pengembangan.

## 30. Rencana Rollback
- Mengembalikan kode (*revert commit*) di Git (*TaskController@destroy*) jika kode ternyata *buggy*.

## 31. Monitoring & Alerting
- Tidak relevan secara khusus, dipantau melalui Laravel Log bawaan (*error logging* untuk exception di dalam block `catch`).

## 32. Dokumentasi
- Dokumen *Controller* yang dapat ditambahkan komentar kode jika diperlukan. Ini PRD terkait.

## 33. Pelatihan Pengguna
- Tidak relevan, fitur transparan untuk pengguna.

## 34. Dukungan Pelanggan (Customer Support)
- Dukungan tidak ada kecuali pengguna melaporkan error saat menghapus (mengacu pada *flash message error*).

## 35. Kepatuhan (Compliance) & Hukum
- Mengikuti pedoman privasi dengan menghapus *file* sensitif milik *user* secara langsung begitu *task* terkait dihapus.

## 36. Analisis Risiko (Risk Analysis)
- Risiko Rendah: *file_path* yang direkam keliru. Sistem hanya akan mencoba menghapus *file* di lokasi `public` disk, tidak akan menghapus file vital OS.

## 37. Rencana Komunikasi (Communication Plan)
- Tidak ada yang spesifik, kecuali menginformasikan ke tim QA untuk fokus menguji pembersihan file terkait task.

## 38. Jadwal & Estimasi (Timeline)
- Estimasi pengembangan: ~30 menit karena hanya memerlukan modifikasi pada satu *controller method* (`destroy` di `TaskController`).

## 39. Kebutuhan Sumber Daya (Resource Requirements)
- 1 Backend Developer (Laravel).

## 40. Anggaran (Budget)
- Tidak relevan (sudah bagian dari pengembangan rutin proyek).

## 41. Persetujuan (Sign-off)
- Menunggu *review* dan *approve* dari *Project Manager* & *Lead Engineer*.

## 42. Catatan Tambahan
Implementasi kodenya dapat merujuk pada standar berikut:

```php
public function destroy(Workspace $workspace, Task $task): RedirectResponse
{
    if ($task->workspace_id !== $workspace->id) {
        abort(404);
    }
    if (!$workspace->hasAccess(auth()->user())) {
        abort(403, 'Anda tidak memiliki hak akses ke workspace ini.');
    }

    // 1. Kumpulkan file path
    $filePaths = $task->attachments->pluck('file_path')->filter()->toArray();

    // 2. Transaksi atomic penghapusan
    try {
        DB::transaction(function () use ($task) {
            $task->delete();
        });
    } catch (\Throwable $e) {
        return redirect()->route('workspaces.show', $workspace)
            ->with('error', 'Gagal menghapus tugas. Silakan coba lagi.');
    }

    // 3. Bersihkan file fisik jika DB commit berhasil
    if (!empty($filePaths)) {
        Storage::disk('public')->delete($filePaths);
    }

    return redirect()->route('workspaces.show', $workspace)
        ->with('success', 'Tugas berhasil dihapus.');
}
```
