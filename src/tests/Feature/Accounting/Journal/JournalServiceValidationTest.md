# Dokumentasi Testing: JournalServiceValidationTest

## Deskripsi
Fokus pada validasi input di level Service sebelum data masuk ke Repository.

## Daftar Method Test & Penjelasan Detail

### 1. `test_pos_validation_kas_paid_amount_cannot_be_negative` [✅ **PASS**]
- **Deskripsi**: Mencegah input pembayaran kas bernilai negatif pada modul Kasir.
- **Verifikasi**: Assert melempar pesan error "Nominal pembayaran tidak boleh negatif".

### 2. `test_pos_validation_piutang_receivable_amount_cannot_be_negative` [✅ **PASS**]
- **Deskripsi**: Menangani kasus "Overpaid" yang menyebabkan piutang negatif (tidak diizinkan di modul ini).
- **Verifikasi**: Assert exception saat `paid > total`.

### 3. `test_pos_validation_pendapatan_net_amount_cannot_be_zero` [✅ **PASS**]
- **Deskripsi**: Memastikan transaksi penjualan bernilai minimal Rp 1.
- **Verifikasi**: Assert exception jika total net = 0.

### 4. `test_pos_validation_hpp_cost_amount_cannot_be_zero` [✅ **PASS**]
- **Deskripsi**: Validasi integritas stok. Barang keluar harus memiliki nilai perolehan (COGS).
- **Verifikasi**: Assert exception jika harga beli produk = 0.

### 5. `test_encounter_validation_jasa_service_amount` [✅ **PASS**]
- **Deskripsi**: Validasi pendapatan jasa medis di modul Pemeriksaan Dokter.
- **Verifikasi**: Menolak nilai jasa negatif.

### 6. `test_purchase_validation_kas_paid_amount` [✅ **PASS**]
- **Deskripsi**: Validasi pengeluaran kas saat pembelian supplier.

### 7. `test_payable_validation_hutang_usaha_amount` [✅ **PASS**]
- **Deskripsi**: Validasi pelunasan hutang dagang. Memastikan jumlah bayar > 0.

### 8. `test_expense_validation_kas_gaji_amount` [✅ **PASS**]
- **Deskripsi**: Validasi modul Biaya Gaji. Menguji konsistensi antara modul biaya dan jurnal pengeluaran kas.

---
*Dihasilkan secara otomatis oleh Antigravity.*
