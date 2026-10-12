# Analisa Porting Template Blade ke Vue 3 (TS + Pinia)

Dokumen ini menganalisis kelayakan dan langkah-langkah untuk memindahkan template dari `app.blade.php` ke proyek Vue 3 modern tanpa ketergantungan pada jQuery maupun Laravel Blade.

## 1. Analisis Kelayakan: **Sangat Bisa**
Template yang ada di `app.blade.php` menggunakan **Vanilla CSS** yang sangat bersih dan terstruktur. Ini memudahkan proses migrasi karena:
- **CSS bersifat modular**: Variabel CSS (`:root`) sudah didefinisikan dengan baik.
- **Logic Sederhana**: Navigasi dan sidebar toggle hanya menggunakan manipulasi class yang bisa digantikan dengan state reaktif Vue.
- **Responsive**: Media queries sudah ada di CSS, tinggal dipindahkan ke file `.vue` atau global CSS.

---

## 2. Pemetaan Komponen (Blade vs Vue 3)

| Fitur Blade | Solusi Vue 3 + TS | Keterangan |
| :--- | :--- | :--- |
| `@yield('content')` | `<router-view />` | Slot utama untuk halaman. |
| `@include('sidebar')` | `<Sidebar />` | Komponen Sidebar mandiri. |
| `@auth` | `v-if="authStore.isLoggedIn"` | Logic akses menggunakan Pinia. |
| `$(document).on(...)` | `ref()` & `v-on:click` | Pengganti jQuery untuk interaksi. |
| `@stack('styles')` | Scoped `<style>` | CSS spesifik per halaman/komponen. |

---

## 3. Arsitektur State dengan Pinia

Untuk mengelola keadaan sidebar (buka/tutup) dan sesi user secara global:

```ts
// stores/uiStore.ts
import { defineStore } from 'pinia'

export const useUiStore = defineStore('ui', {
  state: () => ({
    isSidebarCollapsed: localStorage.getItem('sidebar_collapsed') === 'true',
    isMobileMenuOpen: false
  }),
  actions: {
    toggleSidebar() {
      this.isSidebarCollapsed = !this.isSidebarCollapsed
      localStorage.setItem('sidebar_collapsed', String(this.isSidebarCollapsed))
    },
    toggleMobileMenu() {
      this.isMobileMenuOpen = !this.isMobileMenuOpen
    }
  }
})
```

---

## 4. Implementasi Tanpa jQuery

### Sidebar Toggle Logic
Di Vue, kita tidak lagi menggunakan `document.getElementById('sidebarToggle').addEventListener(...)`. Kita cukup melakukan binding class pada `<body>` atau kontainer utama layout.

```vue
<!-- AppLayout.vue -->
<template>
  <div :class="{ 'sidebar-collapsed': ui.isSidebarCollapsed, 'sidebar-mobile-open': ui.isMobileMenuOpen }">
    <Sidebar />
    <Header @toggle-sidebar="ui.toggleSidebar" @toggle-mobile="ui.toggleMobileMenu" />
    <main class="app-main">
      <router-view />
    </main>
    <div class="sidebar-overlay" @click="ui.toggleMobileMenu"></div>
  </div>
</template>

<script setup lang="ts">
import { useUiStore } from '@/stores/uiStore'
const ui = useUiStore()
</script>
```

---

## 5. Langkah-langkah Migrasi

1.  **Extract CSS Variables**: Pindahkan blok `:root` dari Blade ke `src/assets/main.css`.
2.  **Layouting**: Buat folder `src/layouts/MainLayout.vue`. Pindahkan struktur HTML Sidebar, Header, dan Main ke sana.
3.  **Componentizing**: Pecah Sidebar dan Header menjadi file terpisah di `src/components/layout/`.
4.  **Icons**: Tetap gunakan Font Awesome (CDN) atau ganti ke library modern seperti `lucide-vue-next`.
5.  **TypeScript Interfaces**: Buat interface untuk Menu Item agar sidebar bisa dirender secara dinamis lewat `v-for`.

```ts
interface MenuItem {
  title: string;
  icon: string;
  path?: string;
  children?: MenuItem[];
  badge?: string | number;
}
```

---

## 6. Efek & Komponen Khusus (Alternatif Modern Tanpa jQuery)

Anda mungkin khawatir tentang fitur interaktif yang biasanya menggunakan jQuery. Berikut adalah cara menanganinya di Vue 3:

### A. Animasi Sidebar (Open/Close)
Dalam Blade, Anda mungkin menggunakan `classList.toggle()`. Di Vue, ini lebih elegan:
- **Logic**: Gunakan state `isCollapsed` di Pinia.
- **Visual**: Gunakan `transition` di CSS pada properti `width` dan `margin-left`. Efeknya akan jauh lebih mulus (*smooth*) karena Vue mengelola rendering frame dengan lebih efisien.

### B. Modal (Pengganti Bootstrap jQuery)
- **Problem**: Biasanya Modal dipanggil lewat `$('#modal').modal('show')`.
- **Solusi Vue**: Buat komponen `BaseModal.vue` dengan `v-if` atau `Teleport`. 
- **Library**: Jika ingin cepat, gunakan **PrimeVue Dialog** atau **Headless UI Dialog**. Ini murni TypeScript dan sangat ringan.

### C. Datatable (Pengganti jQuery DataTables)
- **Problem**: jQuery DataTables memanipulasi DOM secara langsung (tidak disarankan di Vue).
- **Solusi Vue**: 
  - **Custom**: Buat table dengan `v-for` + filter/sort logic di Pinia/Composition API (Sangat cepat dan kontrol penuh).
  - **Library**: Gunakan **PrimeVue DataTable** atau **Vue-3-Datatable**. Ini mendukung server-side pagination, sorting, dan filtering secara *out of the box*.

### D. Select2 (Pengganti jQuery Select2)
- **Problem**: Select2 butuh jQuery untuk inisialisasi dropdown.
- **Solusi Vue**: 
  - **Vue-Select**: Library standar untuk dropdown pencarian di Vue.
  - **PrimeVue Select/MultiSelect**: Sangat bertenaga, mendukung virtual scrolling untuk ribuan data, dan murni TypeScript.

---

## 7. Keuntungan Utama (Performance & DX)
Dengan beralih ke Vue 3 + TS + Pinia:
1.  **Reactivity**: Data di tabel otomatis *update* tanpa perlu `table.ajax.reload()`.
2.  **Type Safety**: TypeScript akan mencegah error saat mengirim data ke modal atau API.
3.  **Modular**: Modal dan Tabel menjadi komponen yang bisa dipakai ulang (*reusable*) di banyak halaman.
4.  **No Boilerplate**: Tidak perlu lagi inisialisasi script di bawah setiap halaman Blade (`$(function() { ... })`).

---

## 8. Pertanyaan Umum (FAQ)

### A. Apakah visual dan animasinya akan terganggu tanpa jQuery?
**Sama sekali tidak.** 
Visual (tampilan) sepenuhnya diatur oleh **Vanilla CSS** yang sudah ada di dalam file tersebut. jQuery hanya digunakan untuk penggerak logika (seperti menambah/menghapus class saat tombol diklik). 
- Di Vue 3, Anda akan mendapatkan animasi yang **lebih halus** karena transisinya didorong oleh *state engine* Vue yang teroptimasi, bukan manipulasi DOM manual yang berat.

### B. Apa nama template ini?
Template ini bukan template pasaran seperti AdminLTE atau Metronic. Ini adalah **Custom-Built Premium Dashboard** (sering disebut *Personalized UI*) yang dirancang khusus untuk proyek **Klinik 2026**. 
- Menggunakan basis **Bootstrap 5** tapi dengan desain modern yang unik (warna Indigo vibrant + Glassmorphism).
- Keuntungannya: Ringan dan tidak memiliki "sampah" kode berlebihan seperti template besar lainnya.

### C. Apakah wajib menggunakan jQuery?
**TIDAK.** 
Sama sekali tidak wajib. Dalam file `app.blade.php` ini, jQuery hanya digunakan untuk baris kecil logika seperti menyimpan status sidebar di `localStorage`. Semua fungsi tersebut dapat digantikan oleh 1-2 baris kode di **Pinia** atau **Vue Composition API**.

---

## 9. Checklist Migrasi File (Blade -> Vue 3)

Karena ini adalah migrasi dari **Server-Side Render (Blade)** ke **Client-Side Render (Vue)**, kita tidak menyalin folder secara mentah, melainkan mengekstrak isinya. Berikut adalah checklist-nya:

### A. CSS (Main Style)
- **Sumber**: Tag `<style>` di dalam `D:\projects\klinik-starter\src\resources\views\layouts\app.blade.php`.
- **Tujuan FE**: Simpan sebagai `src/assets/css/main.css` di folder Vue 3 anda.
- **Catatan**: Hapus tag `<style>`-nya, ambil hanya isi CSS-nya saja.

### B. Logo & Assets Statis
- **Sumber**: Folder `D:\projects\klinik-starter\src\public` (jika ada file gambar `.png`, `.jpg`, atau `.svg`).
- **Tujuan FE**: Masukkan ke folder `public/` atau `src/assets/images/` di proyek FE.

### C. Struktur HTML (Layout utama)
- **Sumber**: Tag `<body>` dan struktur `aside`, `header`, `main` di `app.blade.php`.
- **Tujuan FE**: Pindahkan ke `src/layouts/AppLayout.vue`.

### D. Struktur Sidebar (Dinamis)
- **Sumber**: `D:\projects\klinik-starter\src\resources\views\layouts\partials\sidebar_navigation.blade.php`.
- **Tujuan FE**: Pindahkan logika `@foreach` menjadi `v-for` di dalam komponen `Sidebar.vue` anda.

### E. CDN & Fonts
- **Sumber**: Tag `<link>` dan `<script>` di `<head>` pada `app.blade.php`.
- **Tujuan FE**: Pindahkan ke file `index.html` (di root proyek Vue 3 anda).
  - Google Fonts (`Plus Jakarta Sans`)
  - Font Awesome
  - Chart.js (Lebih baik install via npm: `npm install chart.js`)

---

## 10. Contoh Struktur Folder FE yang Disarankan

```text
fe-klinik/
├── public/              # Logo, Favicon
├── src/
│   ├── assets/
│   │   └── css/
│   │       └── main.css  # Hasil ekstrak dari app.blade.php
│   ├── components/
│   │   ├── layout/       # Header.vue, Sidebar.vue
│   │   └── ui/           # Modal.vue, DataTable.vue
│   ├── layouts/
│   │   └── AppLayout.vue # Layout utama
│   ├── stores/
│   │   └── ui.ts         # Pinia store untuk sidebar & modal
│   └── views/            # Halaman Dashboard, Pasien, dll.
├── index.html            # Masukkan CDN Font Awesome di sini
└── package.json
```

---

---

## 11. Struktur Detail File & Folder (`src/resources/views`)

Berikut adalah struktur lengkap dan mendalam dari seluruh file `.blade.php` yang ada di dalam folder `views`. Gunakan daftar ini sebagai checklist saat memindahkan halaman ke Vue 3.

### A. Core, Layouts & Auth
*   **auth/**
    *   `forgot-password.blade.php`
    *   `login.blade.php`
    *   `reset-password.blade.php`
*   **layouts/**
    *   `app.blade.php` (Master Layout)
    *   **partials/**
        *   `sidebar_navigation.blade.php`
*   **partials/**
    *   **common/** (Pesan error, Modal sukses, dll.)
*   **vendor/**
    *   **pagination/** (Tailwind, Bootstrap 5, etc.)

---

### B. Master Data (Database Klinik)
*   **master/**
    *   `employees.blade.php`
    *   `packages.blade.php`
    *   `patients.blade.php`
    *   `services.blade.php`
    *   `suppliers.blade.php`
    *   **products/**
        *   `index.blade.php`
        *   `products_form.blade.php`
        *   `products_summary.blade.php`
        *   `products_table_card.blade.php`
    *   **partials/** (Form edit/tambah master data)
*   **customer_supplier/**
    *   `page.blade.php`
*   **daftar_harga/**
    *   `index.blade.php`

---

### C. Pelayanan Medis
*   **pendaftaran/**
    *   `index.blade.php`
    *   `pasien_list.blade.php`
    *   `pasien_show.blade.php`
*   **transaksi/**
    *   `pemeriksaan.blade.php` (Rekam Medis)
    *   `pemeriksaan_form.blade.php` (Input SOAP)
    *   `penjualan.blade.php` (Kasir / Billing)
    *   `penjualan_form.blade.php`
    *   `penjualan_detail.blade.php`
    *   `pembelian.blade.php` (Stok Masuk)
    *   `kas_bank.blade.php` (Input Keuangan)
    *   `piutang.blade.php`
    *   `hutang.blade.php`
    *   `tugas_dokter.blade.php` (Dashboard Dokter)
    *   `penunjang_permintaan.blade.php`
    *   `penunjang_hasil.blade.php`
*   **farmasi/**
    *   `dispensing.blade.php`
    *   `stok.blade.php`
    *   `penerimaan.blade.php`
    *   `kartu_stok.blade.php`
    *   `adjustment.blade.php`
    *   `retur.blade.php`
*   **lab/** & **radiologi/**
    *   `index.blade.php`
    *   `detail.blade.php`
*   **bpjs/**
    *   `index.blade.php` (VClaim)
    *   `sep.blade.php`
    *   `rujukan.blade.php`
    *   `pendaftaran.blade.php`
    *   `monitoring-klaim.blade.php`
    *   `verifikasi.blade.php`
*   **satusehat/**
    *   `overview.blade.php`
    *   `config.blade.php`
    *   `sync.blade.php`
    *   `encounter.blade.php`
    *   `fhir.blade.php`

---

### D. Keuangan, Inventori & Laporan
*   **accounting/**
    *   `coa.blade.php` (Chart of Accounts)
    *   `jurnal.blade.php`
    *   `bukubesar.blade.php`
    *   `neraca.blade.php`
    *   `laba_rugi.blade.php`
    *   `saldo_awal.blade.php`
    *   `transaksi.blade.php` (Input Jurnal Umum)
    *   `akun_induk.blade.php` & `sub_akun.blade.php`
*   **stok/**
    *   `page.blade.php` (Summary Stok)
*   **cetak_barcode/**
    *   `index.blade.php`
*   **analytics/**
    *   `overview.blade.php` (Dashboard Grafik)
    *   `margin_produk.blade.php`
    *   `profit_dokter.blade.php`
    *   `tindakan_terlaris.blade.php`
*   **laporan/**
    *   `arus_kas.blade.php`
    *   `pendapatan_dokter.blade.php`
    *   `pemeriksaan.blade.php`
    *   `penjualan_pos.blade.php`
*   **reports/**
    *   `journal_pdf.blade.php`
    *   `general_ledger_pdf.blade.php`
    *   `trial_balance_pdf.blade.php`
    *   **exports/** (File sementara hasil excel)

---

### E. Pengaturan & Sistem
*   **management/**
    *   `users.blade.php`
    *   `users_form.blade.php`
    *   `roles.blade.php`
    *   **partials/** (Role list, Permission list)
    *   **exports/** (Eksport data user)
*   **settings/**
    *   `index.blade.php` (Konfigurasi Umum)
    *   `clinic_info.blade.php`
    *   `database.blade.php` (Backup & Restore)
    *   `bpjs.blade.php` (Setting API Key)
    *   `satusehat.blade.php` (Setting Auth)
    *   `icd10.blade.php` (Import Master Penyakit)
*   **himbauan/**
    *   `index.blade.php` (Pesan Broadcast/Banner)

---

### F. Halaman Root
*   `dashboard.blade.php` (Welcome Screen / Menu Utama)
*   `welcome.blade.php` (Landing Page Depan)

---

## Kesimpulan Akhir
Memindahkan template ini ke **Vue 3 + TS + Pinia** adalah keputusan yang sangat tepat. Anda akan mendapatkan tampilan yang sama persis (atau lebih baik), tanpa beban library jQuery yang sudah mulai ketinggalan zaman untuk aplikasi web modern.
