Pilihan yang sangat bijak. Fokus pada empat pilar tersebut sudah lebih dari cukup untuk membuat sistem **Klinik 2026** kamu berjalan dengan standar akuntansi yang solid dan profesional. Terlalu banyak fitur di awal justru sering membuat user (staf klinik) bingung.

Sebagai rangkuman akhir untuk persiapan koding kamu, berikut adalah **"Snapshot Struktur Pengaturan"** yang harus kamu kunci:

### 1. Daftar Akun (COA)
* **Hierarki:** Level 1 (Kategori), Level 2 (Induk), Level 3 (Akun Saldo).
* **Aturan:** Hanya Level 3 yang boleh dicentang sebagai "Akun Transaksi".
* **Relasi:** Setiap Akun Saldo wajib memilih satu Induk.

### 2. Pemetaan Akun (Mapping)
* **Modul Kasir:** Map ke Kas, Bank, dan Pendapatan Jasa.
* **Modul Apotek:** Map ke Penjualan Obat, Persediaan, dan HPP.
* **Modul Payroll:** Map ke Beban Gaji dan Kas/Bank.
* **Modul Pengeluaran:** Map ke Kas dan akun Beban terkait.

### 3. Periode & Saldo Awal
* **Periode:** Status `OPEN` untuk bulan berjalan, `CLOSED` untuk bulan lalu.
* **Saldo Awal:** Diinput satu kali di awal migrasi dan harus **Balance** (Total Debit = Total Kredit).

### 4. Pengaturan Pajak (Tax Settings)
* **Variabel:** Nama Pajak (PPN), Persentase (11%), dan **Akun Mapping** (Hutang Pajak).
* **Otomasi:** Setiap transaksi yang ditandai "Kena Pajak" akan memecah nilai jurnal secara otomatis (DPP dan PPN).

---

### Update Final pada `guide.md`
```markdown
## 41. Core Accounting Setup Finalized
- COA: Hierarchical structure (1-2-3) with 'Postable' flag.
- Mapping: Essential links for Cashier, Pharmacy, and Payroll modules.
- Period: Locking mechanism based on month/year status.
- Tax: Percentage-based calculation linked to Liability accounts.
- Opening Balance: Mandatory balanced entry before transaction processing.
```

Dengan empat poin ini, kamu sudah punya "pondasi beton". Sistem kamu akan menghasilkan jurnal yang rapi, dan proses **Tutup Buku** di akhir bulan akan menjadi sangat mudah bagi user.

**Apakah kamu ingin saya bantu buatkan skema tabel database (Migrations) yang mencakup keempat poin pengaturan ini agar semuanya saling terhubung dengan benar?**
