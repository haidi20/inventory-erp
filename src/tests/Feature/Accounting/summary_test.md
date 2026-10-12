# Ringkasan Pengujian Modul Accounting

Dokumen ini merangkum inventaris suite dan hasil run terbaru untuk seluruh folder `src/tests/Feature/Accounting`.

## Audit Date

- 2026-03-30

## Hasil Run Terbaru

- Command: `php artisan test tests/Feature/Accounting`
- Status: pass dengan skip terkontrol
- Hasil: `346 passed`, `2 skipped`, `2752 assertions`
- Durasi: `492.89s`
- Catatan: 2 skenario yang di-skip berasal dari `GeneralLedgerSimulationTest.php` untuk beban ekstrem yang memang tidak dijalankan pada standard run
- Catatan teknis: masih ada warning PHPUnit untuk metadata `@test` pada doc-comment; ini bukan failure, tetapi perlu dimigrasikan ke attributes sebelum PHPUnit 12

## Inventaris Suite

| Area | Jumlah file `.php` | Keterangan |
|------|--------------------|------------|
| Root `Accounting` | 43 | Setup, laporan, integrasi, backup, recovery, analytics |
| `Journal` | 36 | Core journal, workflow, tax, currency, resilience, concurrency |
| `GeneralLedger` | 11 | Balance, query, performance, year end, consolidation |
| `TrialBalance` | 8 | Integration, approval, forensic, budget, currency, recovery |
| Total | 98 | Seluruh suite executable di bawah folder Accounting |

## Sorotan Cakupan

- Setup dan accounting cycle: account setting, validation, opening balance, closing period, adjusting journal, adjusted trial balance, closing cycle, dan post-closing trial balance
- Journal engine: CRUD, approval workflow, recurring, scheduled posting, tax, fiscal correction, inter-branch, multi-currency, audit trail, attachment, stress, concurrency, locking, recovery, dan disaster recovery
- General ledger: running balance, upsert sync, sub-ledger, integration, consolidation, query/filter, year-end, performance, dan simulation
- Trial balance: integration, simulation, access control, approval, audit forensic, budget, currency, dan recovery
- Financial reporting: laba rugi, neraca, arus kas, perubahan ekuitas, comparative report, financial ratio, segment report, bank reconciliation, budget variance, dan export multi-format
- Additional control coverage: API integration, user activity log, sensitive data masking, SATUSEHAT integration, backup automation, restore from backup, point-in-time recovery, database migration, data retention, dan data integrity check

## Dokumen Ringkasan Per Area

- [summary_journal_test.md](./Journal/summary_journal_test.md)
- [summary_general_ledger_test.md](./GeneralLedger/summary_general_ledger_test.md)
- [summary_trial_balance_test.md](./TrialBalance/summary_trial_balance_test.md)
- [prompt.md](./prompt.md)

## Catatan

- Dokumen ini memakai hasil run aktual terbaru, bukan asumsi lama seperti `282 test scenarios` atau klaim `100% success` tanpa konteks skip.
- Jika setelah ini ada penambahan suite atau perubahan perilaku test, angka inventaris dan hasil run perlu diperbarui lagi.
