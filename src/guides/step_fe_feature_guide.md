# Panduan Pengembangan Fitur Frontend (FE) - Klinik 2026

Panduan ini mendefinisikan standar arsitektur dan penulisan kode untuk setiap fitur baru di bagian Frontend, memastikan konsistensi, ketahanan (*reliability*), dan kemudahan pengujian.

---

## 🏗️ Struktur Arsitektur (Layered Architecture)

Setiap fitur harus terbagi menjadi 4 lapisan utama:
1.  **View (Presentation):** Komponen Vue (.vue) utama dan folder **Partials** untuk membagi UI (Table, Form, Filters).
2.  **ViewModel (Presentation):** State management menggunakan **Pinia Store**.
3.  **UseCase (Domain):** Logika bisnis transaksional yang granular.
4.  **Repository (Data):** Interaksi API (Axios/apiService). **Dilarang menggunakan Router Inertia untuk pengambilan data tabel/CRUD.**

---

## 🚦 Penanganan Error: Pola `Either`

Semua operasi asinkron yang berisiko gagal (panggilan API/Network) **WAJIB** menggunakan blok `try-catch` dan mengembalikan tipe `Either<Failure, T>`.
-   **Left (Failure):** Menampung objek kegagalan (ServerFailure, ValidationFailure, dll).
-   **Right (Success):** Menampung data hasil jika berhasil.

---

## 🛠️ Panduan Per Lapisan

### 1. Repository Implementation (`.repo_impl.ts`)
Berinteraksi dengan API menggunakan `apiService`. **Setiap method wajib dibungkus `try-catch`.**

**Syntax Wajib:**
```typescript
async paginate(filters: UserFilters): Promise<Either<Failure, any>> {
    try {
        const response = await apiService.get('/settings/users', { params: filters });
        return right(response.data);
    } catch (error: any) {
        return left(new ServerFailure(error.message));
    }
}

async save(data: FormData): Promise<Either<Failure, any>> {
    try {
        const response = await apiService.post('/path', data);
        return right(response.data);
    } catch (error: any) {
        if (error.response?.status === 422) {
            return left(new ValidationFailure(error.response.data.message, error.response.data.errors));
        }
        return left(new ServerFailure(error.message));
    }
}
```

### 2. Domain DI (`.di.ts`)
Mengelola instansiasi dan dependensi fitur secara terpusat.
- Lokasi: `domain/di/[feature].di.ts`.
- Gunakan factory function `create[Feature]Dependencies` untuk kemudahan testing.
- Export singleton instance untuk digunakan di VM.

### 3. UseCase (`.usecase.ts`)
Membungkus pemanggilan repository. **WAJIB** memiliki `try-catch` sebagai *safety net* tambahan.

```typescript
export class SaveDataUseCase {
    constructor(private repository: MyRepository) {}

    async execute(data: any): Promise<Either<Failure, any>> {
        try {
            return await this.repository.save(data);
        } catch (error: any) {
            return left(new ServerFailure(error.message));
        }
    }
}
```

### 4. ViewModel (Pinia Store - `.vm.ts`)
Mengelola state UI, Filter, dan Form.
- **Wajib menggunakan Pinia** (`defineStore`).
- Gunakan factory function `create[Feature]VMStore` yang menerima dependensi (DI).
- **Inertia `useForm`**: Diizinkan di dalam VM hanya untuk mengelola state form dan error validasi (bukan untuk `router.post`).

**Syntax ViewModel (Pola `.fold()`):**
```typescript
async function submit() {
    isProcessing.value = true;
    const result = await saveUseCase.execute(form.data());

    result.fold(
        (failure) => {
            if (failure instanceof ValidationFailure) {
                form.setError(failure.errors); // Petakan error 422 ke form Inertia
            } else {
                Swal.fire('Gagal!', failure.message, 'error');
            }
        },
        (data) => {
            fetchData(); // Refresh data tabel via API (Bukan reload halaman)
            closeForm();
            Swal.fire('Berhasil!', 'Data telah disimpan.', 'success');
        }
    );
    isProcessing.value = false;
}
```

**Elemen Wajib di VM:**
- **UI State**: `items`, `meta`, `summary`, `isLoading`.
- **Filter State**: `search`, `sortBy`, `perPage`, dll.
- **Link Mapping**: Method `mapApiPaginationLinksToWeb` untuk menyesuaikan link paginasi API ke context web Inertia.
- **Initial Fetch**: Gunakan `initFilters` untuk sinkronisasi state awal dari props Inertia.

### 5. View & Partials (`.vue`)
- **Main View**: Menginisialisasi VM dan menangani watcher (seperti `api_token`).
- **Partials**: Pecah komponen besar menjadi bagian kecil di folder `Partials/`:
    - `[Feature]Table.vue`
    - `[Feature]Filters.vue`
    - `[Feature]Form.vue`
- Pastikan setiap elemen interaktif memiliki `data-testid` untuk pengujian E2E.

---

## 🔍 Standar Fitur Server-Side (Wajib)

Setiap halaman index/tabel harus mengimplementasikan:

1.  **Pencarian (Search):** Server-side via API dengan `debounce` (300ms).
2.  **Paginasi (Pagination):** Navigasi via API, update state `items` dan `meta`.
3.  **Sorting:** Klik header tabel memicu `toggleSort` di VM dan fetch ulang data.
4.  **Export & Import:** 
    - **Export:** Harus melalui API dengan response type `Blob`.
    - **Import:** Harus melalui API `Multipart/form-data` dengan penanganan error validasi (422) yang jelas di UI.

---

## ⌨️ Standar UI/UX: Keyboard-first & Informative (KUI)

Setiap form atau modul interaktif **WAJIB** memenuhi standar aksesibilitas berikut:

1.  **Keyboard Shortcuts (via @vueuse/core):**
    -   `Ctrl + S`: Simpan/Submit form.
    -   `Esc`: Tutup modal atau batalkan aksi.
    -   `F1`: Toggle panel bantuan (Help Section).
2.  **Informative UI (No Guessing):**
    -   **Info Icon (ⓘ):** Letakkan di header halaman/modal untuk membuka panduan fitur.
    -   **Help Section:** Panel yang merinci langkah penggunaan dan daftar shortcut keyboard.
    -   **Field Help (?):** Tambahkan pada label input yang memerlukan penjelasan format atau tujuan (misal: NIK, Role ID).
3.  **Keyboard Accessibility:**
    -   Pastikan semua elemen (input, button, grid items) dapat dijangkau dengan `Tab`.
    -   Gunakan `tabindex="0"` dan handler `Enter/Space` untuk elemen kustom agar tetap bisa dioperasikan tanpa mouse.

---

## 🧪 Standar Pengujian (Testing)

Setiap fitur baru harus menyertakan:
1.  **Unit Test (Vitest):** Menguji RepoImpl dan UseCase secara terisolasi.
2.  **Integration Test (Vitest + Real API):** Menguji alur dari Repo ke Database asli (Server aktif).
3.  **VM Test:** Menguji logika state di Pinia store dengan mock dependensi.
4.  **E2E Test (Playwright):** Menguji alur dari Layar/Screen (UI) hingga database.

⚠️ **Catatan:** File pengujian integrasi yang sudah **Passed** harus diberi komentar **"DIKUNCI" (LOCKED)** agar tidak dirubah tanpa instruksi.

---

## 🏢 Konsep Multi-Company (Sangat Penting)

Aplikasi ini dirancang untuk mendukung banyak perusahaan (*Multi-Company*) dan cabang (*Multi-Branch*).

1.  **Context Otomatis:**
    -   `apiService` secara otomatis menyertakan header `X-Klinik-Company-Uuid` pada setiap permintaan API.
    -   Data ini diambil dari state auth (`page.props.auth.user.company_uuid`) atau `app_context`.
2.  **Scoping di Frontend:**
    -   Saat melakukan simpan data (`save`), pastikan jika backend membutuhkan `branch_uuid` secara eksplisit, Anda mengambilnya dari user context: `usePage().props.auth.user.branch_uuid`.
    -   **PENTING:** Jangan pernah melakukan *hardcode* UUID perusahaan atau cabang.
3.  **Dukungan Sandbox:**
    -   Untuk keperluan testing, `apiService` mendukung header `X-Klinik-Environment` yang memisahkan data simulasi dengan data asli.

---

## 🚀 Prosedur Pasca Perubahan (WAJIB)

Setiap kali melakukan perubahan pada kode, terutama file **.vue** dan **.ts**, Anda **WAJIB** menjalankan prosedur berikut untuk memastikan tidak ada regresi:

1.  **Pengecekan Tipe (Typecheck):**
    ```bash
    npm run typecheck
    ```
    *Pastikan hasil menunjukkan "Found 0 errors".*

2.  **Membangun Aset (Build):**
    ```bash
    npm run build
    ```
    *Pastikan proses kompilasi Vite berhasil tanpa error.*
