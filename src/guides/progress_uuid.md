# Progress Implementasi UUID

Tugas-tugas yang belum dikerjakan untuk migrasi penuh ke UUID:

## 1. Refactor Routes
- [x] Mengubah parameter `{id}` menjadi `{uuid}` pada file `routes/web.php`.
- [x] Mengubah parameter `{id}` menjadi `{uuid}` pada file `routes/api.php`.
- [x] Memastikan Route Model Binding menggunakan kolom `uuid`.

## 2. Refactor Controllers
- [x] Mengubah nama argumen method di Controller dari `$id` menjadi `$uuid` (contoh: `public function show($uuid)`).
- [x] Memastikan query di controller menggunakan `uuid` jika tidak menggunakan Route Model Binding.

## 3. Refactor Views (Blade Templates)
- [x] Mengganti semua akses `$model->id` menjadi `$model->uuid` (kecuali untuk kolom legacy `id` jika memang diperlukan secara eksplisit).
- [x] Mengupdate pemanggilan route agar menggunakan `uuid`, contoh: `route('pasien.show', $pasien->uuid)`.
- [x] Memastikan atribut ID pada elemen HTML (seperti `id="modal-{{ $item->uuid }}"`) menggunakan UUID untuk menghindari konflik.

## 4. Refactor Form & Request
- [x] Mengubah nama input hidden atau parameter request dari `id` atau `*_id` menjadi `uuid` atau `*_uuid`.
- [x] Mengupdate validation rules di FormRequest (contoh: `'patient_uuid' => 'required|exists:patients,uuid'`).

## 5. Refactor JavaScript
- [x] Memeriksa kode JavaScript (inline di blade maupun file `.js`) yang masih mengambil properti `.id` dari objek JSON.
- [x] Mengupdate pemanggilan API / AJAX agar menyertakan UUID.

## 6. Audit Database & Migrations
- [x] Memastikan semua foreign key di migrasi menggunakan `foreignUuid` atau `uuid` yang mereferensikan kolom `uuid` di tabel tujuan.
- [x] Memastikan tabel-tabel third-party (seperti Spatie Permissions/Roles) sudah terintegrasi penuh dengan UUID (sebagian sudah, perlu audit menyeluruh).

## 7. Clean up Blade Templates
- [x] Menghapus query database (Product::where...) dari file Blade (retur.blade.php).
- [x] Memindahkan logika pemilihan data ke Repository (ProductRepository).
- [x] Melakukan mapping data di tingkat Repository/Controller agar Blade tetap bersih.
