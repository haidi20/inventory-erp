# Skenario 31: Multi-Currency Journal

## Deskripsi
Menguji kemampuan sistem dalam menangani transaksi mata uang asing (Valas) dan konversinya ke mata uang fungsional (IDR).

## Sub-Skenario
1. **Penjualan USD**: Mencatat debit Kas USD dan kredit Pendapatan dengan kurs 15.000.
2. **Validasi Kurs**: Memasukkan nominal valas dan memastikan nominal IDR terhitung benar.

## File Terkait
- **Test**: [JournalMultiCurrencyTest.php](file:///d:/projects/klinik-starter/src/tests/Feature/Accounting/Journal/JournalMultiCurrencyTest.php)
- **Service**: [JournalService.php](file:///d:/projects/klinik-starter/src/app/Services/JournalService.php)
