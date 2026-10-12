# Dokumentasi Testing: JournalTaxCompleteTest

## Deskripsi
Memverifikasi perhitungan pajak otomatis (PPN, PPh 23, PPh 4 ayat 2) dan pelabelan koreksi fiskal sesuai aturan perpajakan Indonesia.

## Daftar Method Test & Penjelasan Detail

### 1. `test_purchase_vat_input_calculation` [✅ **PASS**]
- **Deskripsi**: Perhitungan Otomatis PPN Masukan (Purchase VAT 11%).
- **Logika**: Input DPP Rp 1.000.000 -> Sistem wajib menambah baris PPN Rp 110.000 secara otomatis.
- **Verifikasi**: Assert jumlah baris jurnal menjadi 3 dan nilai PPN akurat.

### 2. `test_pph_23_calculation` [✅ **PASS**]
- **Deskripsi**: Pemotongan PPh Pasal 23 atas penyerahan jasa.
- **Verifikasi**: Assert hutang pajak (2%) dicatat di sisi Kredit saat pengakuan biaya.

### 3. `test_pph_4_2_final_tax_calculation` [✅ **PASS**]
- **Deskripsi**: Perhitungan Pajak Final (PPh 4 ayat 2) sebesar 10% untuk sewa.
- **Verifikasi**: Assert pemotongan pajak tepat 10% dari nilai Bruto transaksi.

### 4. `test_tax_exemption_skb` [✅ **PASS**]
- **Deskripsi**: Penanganan Surat Keterangan Bebas (SKB) Pajak.
- **Logika**: Parameter `tax_rate` diset 0.00.
- **Verifikasi**: Assert tidak ada baris pajak yang terbentuk (status Exempt).

### 5. `test_fiscal_correction_tagging` [✅ **PASS**]
- **Deskripsi**: Tagging Koreksi Fiskal Positif/Negatif untuk rekonsiliasi SPT Badan.
- **Verifikasi**: Assert atribut `is_fiscal` dan `fiscal_type` tersimpan benar di database.

### 6. `test_vat_nilai_lain_calculation` [✅ **PASS**]
- **Deskripsi**: PPN Besaran Tertentu (1.1%) untuk transaksi khusus.
- **Verifikasi**: Assert akurasi perhitungan desimal untuk tarif pajak non-standar.

---
*Dihasilkan secara otomatis oleh Antigravity.*
