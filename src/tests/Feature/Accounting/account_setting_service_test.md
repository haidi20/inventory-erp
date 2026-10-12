# Dokumentasi Pengujian AccountSettingServiceTest

File ini mendokumentasikan pengujian fungsional untuk `AccountSettingRepository` dan `AccountSettingService`.
Pengujian fokus pada **5 pilar pengaturan akuntansi** klinik.

---

## Struktur Kelas

- **`AccountSettingServiceTest`** — Kelas test tunggal tanpa abstract.
- Menggunakan `RefreshDatabase` + seed `DemoSeeder` dan `RbacSeeder`.

---

## Layer Repository (4 test)

### 1. `test_repository_can_fetch_all_accounts`
- Memanggil `allAccounts()` dan memastikan data terurut berdasarkan `code`.
- Memverifikasi akun Kas (`1101`) ada di dalam koleksi.

### 2. `test_repository_can_find_account_by_code`
- Memanggil `findAccountByCode('1101')` → harus menemukan akun.
- Memanggil `findAccountByCode('INVALID-XYZ')` → harus return `null`.

### 3. `test_repository_can_fetch_mappings`
- Memanggil `getMappings()` dan memverifikasi *eager loading* relasi `account` terpenuhi.

### 4. `test_repository_can_update_mapping`
- Membuat mapping baru via `updateMapping($key, $accountId)`.
- Update mapping ke akun lain dan pastikan ID mapping sama (tidak duplikat).

---

## Layer Service — Daftar Akun & Mapping (4 test)

### 5. `test_service_can_get_chart_of_accounts`
- Memanggil `getChartOfAccounts()` dan memverifikasi koleksi tidak kosong.

### 6. `test_service_can_get_account_mappings`
- Memanggil `getAccountMappings()` dan memverifikasi key `penjualan.kas` ada.

### 7. `test_service_can_update_mapping_by_code`
- Mengubah mapping `penjualan.kas` ke akun `1102` via service.
- Verifikasi di DB bahwa relasi akun sudah berubah.

### 8. `test_service_returns_null_when_updating_mapping_with_invalid_code`
- Memanggil `updateMappingByCode()` dengan kode akun tidak valid (`9999-NOT-EXIST`).
- Harus return `null` dan mapping asli tidak berubah.

---

## Layer Service — Fitur Tambahan (3 test)

### 9. `test_service_can_manage_tax_settings`
- **Baca:** Memastikan data pajak PPN dari seeder tersedia.
- **Tulis:** Membuat pajak baru `PPH 21` (2.5%) terhubung ke akun `2101`.

### 10. `test_service_can_manage_opening_balances`
- **Baca:** Memastikan saldo awal dari seeder tidak kosong.
- **Tulis:** Set saldo awal Kas Kecil (`1102`) sebesar Rp 5.000.000.

### 11. `test_service_can_manage_closing_periods`
- **Baca:** Memastikan periode bulan berjalan `open` dari seeder tersedia.
- **Tulis:** Menutup (`closePeriod`) periode bulan berjalan, verifikasi status `closed`.

---

## Ringkasan Cakupan

| Pilar Pengaturan | Repository | Service |
|---|---|---|
| Daftar Akun (COA) | ✅ `allAccounts`, `findByCode` | ✅ `getChartOfAccounts` |
| Pemetaan Akun | ✅ `getMappings`, `updateMapping` | ✅ `getAccountMappings`, `updateMappingByCode` |
| Master Pajak | ✅ (via service test) | ✅ `getTaxSettings`, `updateTaxSetting` |
| Saldo Awal | ✅ (via service test) | ✅ `getOpeningBalances`, `updateOpeningBalance` |
| Periode Akuntansi | ✅ (via service test) | ✅ `getClosingPeriods`, `closePeriod` |

**Total: 11 test cases, 41 assertions.**

## Cara Menjalankan
```bash
php artisan test tests/Feature/AccountSettingServiceTest.php
```
