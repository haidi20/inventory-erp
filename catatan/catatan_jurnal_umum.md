# 📒 Arsitektur Sistem Akuntansi Klinik 2026

Dokumen ini berisi panduan logika bisnis, struktur database, dan alur integrasi transaksi ke jurnal umum untuk pengembangan sistem informasi klinik profesional.

---

## 1. Konsep Dasar: Saldo Normal (Default)

Saldo Normal adalah sifat dasar sebuah akun yang menjadi patokan rumus tetap di dalam sistem.

| Kelompok Akun | Saldo Normal (Default) | Rumus Saldo Akhir |
| :--- | :--- | :--- |
| **Aset (Harta)** | Debit | Total Debit - Total Kredit |
| **Kewajiban (Utang)** | Credit | Total Kredit - Total Debit |
| **Modal (Ekuitas)** | Credit | Total Kredit - Total Debit |
| **Pendapatan** | Credit | Total Kredit - Total Debit |
| **Beban** | Debit | Total Debit - Total Kredit |

> [!NOTE]
> **Catatan Developer**: Jangan simpan saldo akhir di tabel akun. Gunakan `type_saldo` sebagai konstanta untuk menghitung saldo secara otomatis melalui query atau service.

---

## 2. Struktur Database (Migrations)

### A. Tabel `journals` (Header)
Menyimpan informasi utama sebuah kejadian transaksi.
- `id` (BigInt, PK)
- `reference_no` (String): Contoh: `SLS-202603-001`
- `reference_model` (String): Nama Model asal (Contoh: `Sale`, `Purchase`)
- `reference_type` (String): Contoh: `pos_sales`, `purchase`, `bpjs_claim`
- `reference_id` (BigInt): ID dari tabel asal transaksi (untuk audit trail)
- `journal_date` (Date)
- `description` (Text)

### B. Tabel `journal_lines` (Mutasi)
Menyimpan detail pergerakan angka (double-entry).
- `id` (BigInt, PK)
- `journal_id` (FK to `journals`)
- `account_id` (FK to `accounts`)
- `debit` (Decimal 15,2)
- `credit` (Decimal 15,2)
- `is_processed` (Boolean): Flag untuk service aggregator (0: Belum, 1: Sudah)

### C. Tabel `account_balances` (Hasil Injection)
Tabel agregat untuk menyimpan saldo yang sudah dihitung agar laporan (Neraca/Laba Rugi) cepat dibuka.
- `account_id` (FK to `accounts`)
- `period` (String): Contoh: `2026-03`
- `opening_balance` (Decimal)
- `total_debit` (Decimal)
- `total_credit` (Decimal)
- `closing_balance` (Decimal)

---

## 3. Alur Integrasi Modul ke Jurnal

Setiap transaksi di menu operasional harus menginjeksi data ke Jurnal Umum secara otomatis.

**Skenario: Penjualan Obat Tunai (`pos_sales`)**
Jika Pasien bayar Rp100.000 (HPP Rp70.000):

| Akun | Posisi | Nilai | Logika |
| :--- | :--- | :--- | :--- |
| **1101 - Kas Besar** | Debit | 100.000 | Aset (Kas) Bertambah |
| **4102 - Pendapatan Obat** | Kredit | 100.000 | Pendapatan Bertambah |
| **5101 - HPP Obat** | Debit | 70.000 | Beban Bertambah |
| **1301 - Persediaan Obat** | Kredit | 70.000 | Aset (Stok) Berkurang |

---

## 4. Optimasi: Service Running Per Jam

Sistem tidak menghitung saldo setiap kali user klik simpan transaksi. Sebagai gantinya, gunakan background service (Cron Job):
1.  **Scan**: Ambil data `journal_details` di mana `is_processed = 0`.
2.  **Calculate**: Jumlahkan mutasi per akun untuk periode tersebut.
3.  **Inject**: Update tabel `account_balances` (tambah total debit/kredit dan hitung saldo akhir baru).
4.  **Mark**: Ubah `is_processed = 1`.

---

## 5. Fitur Traceability (Drill-down)

Gunakan kombinasi `source_type` dan `source_id` pada laporan Buku Besar.

**Kasus**: Akuntan melihat ada biaya listrik mencurigakan di Jurnal Umum.
- **Sistem**: Menampilkan link yang mengarah ke `/transactions/expenses/show/{source_id}`.
- **Hasil**: Transparansi data terjaga dari Jurnal sampai ke struk fisik.
