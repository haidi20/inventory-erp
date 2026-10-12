# Scenario 24: Locking Mechanism untuk Transaction Code

## Deskripsi
Memastikan mekanisme pendeteksian duplikasi nomor transaksi (`transaction_code`) berfungsi dengan benar untuk mencegah duplikasi data akibat double submit atau concurrency.

## Objective
- Mencegah data "hantu" atau entri ganda dengan nomor referensi yang sama.
- Validasi integritas kode transaksi unik per cabang/periode.

## File Testing
- `JournalTransactionLockingTest.php`

## Skenario Uji
1. **test_prevent_duplicate_reference_number**: Membuat jurnal dengan `reference_no` yang sudah ada. Harus melempar exception "Duplicate reference number".
2. **test_optimistic_locking_via_version**: Melakukan update jurnal dengan nomor `version` yang lebih rendah dari yang ada di database. Harus melempar exception "Concurrency Lock Error".
