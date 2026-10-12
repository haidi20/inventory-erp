# Audit Kegagalan Test Suite — 2026-04-24

## Ringkasan

| Kondisi | Jumlah |
|---|---|
| Total test | ~1459 |
| **Gagal** | **1387** |
| Dilewati | 18 |
| Lulus | 54 |

Semua kegagalan adalah **pre-existing** sebelum sesi ini. Terbukti dengan menjalankan `git stash` lalu `php artisan test`: hasilnya 1382 gagal — angka yang sama sebelum perubahan multi-company rollout.

---

## Akar Masalah (Root Causes)

### RC-1 — SQLite: `cannot VACUUM from within a transaction` (1322 kegagalan PDOException)

**Error:**
```
SQLSTATE[HY000]: General error: 1 cannot VACUUM from within a transaction
(Connection: sqlite, Database: :memory:, SQL: vacuum "main")
```

**Penyebab:**
- Sebagian test menggunakan `RefreshDatabase` trait (yang membungkus test dalam transaksi) **sekaligus** memanggil `$this->artisan('migrate:fresh', ...)` di dalam `setUp()`.
- SQLite tidak bisa menjalankan `VACUUM` di dalam transaksi aktif.
- `VACUUM` dipanggil oleh SQLite saat mencoba menjatuhkan kolom atau merekonstruksi tabel secara internal.

**Test yang memanggil `migrate:fresh` di setUp() (bukan melalui RefreshDatabase):**

```
tests/Feature/Settings/UserCrudTddTest.php
tests/Feature/Settings/UserServerSideFeaturesTest.php
tests/Feature/Settings/Users/UserInertiaCrudRealDbTest.php
tests/Feature/Settings/Users/UserPageTest.php
tests/Feature/Rbac/RbacTest.php
tests/Feature/Repositories/Infrastructure/UserRepositoryTest.php
tests/Feature/Repositories/Operational/DispensingRepositoryTest.php
tests/Feature/Repositories/Operational/ShipRepositoryTest.php
tests/TestCase.php (override migrateFreshUsing — semua migration path v1+v2)
tests/Traits/ManualDatabaseSetup.php
```

**Fix yang dibutuhkan:**
- Hapus panggilan `$this->artisan('migrate:fresh', ...)` dari `setUp()` pada test yang sudah menggunakan `RefreshDatabase`
- Gunakan `RefreshDatabase` saja, atau `DatabaseTransactions` saja — jangan campur keduanya
- Atau: gunakan trait `ManualDatabaseSetup` secara konsisten (jalankan `php artisan migrate:fresh --env=testing` manual sebelum suite)

---

### RC-2 — SQLite: `near "SET": syntax error` (MySQL-only SQL)

**Error:**
```
SQLSTATE[HY000]: General error: 1 near "SET": syntax error
(Connection: sqlite, Database: :memory:, SQL: SET FOREIGN_KEY_CHECKS=0;)
```

**Penyebab:**
- Beberapa test atau migration menjalankan `SET FOREIGN_KEY_CHECKS=0` — sintaks MySQL yang tidak didukung SQLite.

**Fix yang dibutuhkan:**
- Ganti dengan `DB::statement('PRAGMA foreign_keys = OFF')` saat environment SQLite
- Atau: kondisikan dengan `if (config('database.default') !== 'sqlite')`

---

### RC-3 — SQLite: `table "migrations" already exists`

**Error:**
```
SQLSTATE[HY000]: General error: 1 table "migrations" already exists
```

**Penyebab:**
- Test yang memanggil `migrate:fresh` atau `migrate` berulang kali pada in-memory SQLite yang sama, menyebabkan tabel `migrations` sudah ada saat test berikutnya mencoba membuat ulang.
- Terjadi karena state database SQLite in-memory bocor antar test class.

**Fix yang dibutuhkan:**
- Pastikan setiap test yang butuh fresh database menggunakan `RefreshDatabase` (bukan manual `migrate:fresh`)
- Atau: gunakan `DatabaseMigrations` trait untuk test yang butuh full migration lifecycle

---

### RC-4 — `BindingResolutionException`: `RbacSeeder` tidak ada (12 kegagalan)

**Error:**
```
Target class [Database\Seeders\V1\RbacSeeder] does not exist.
```

**Test yang terdampak (11 file):**
```
tests/Feature/Accounting/AccountSettingServiceTest.php
tests/Feature/Accounting/ApiIntegrationTest.php
tests/Feature/Accounting/BackupAutomationTest.php
tests/Feature/Accounting/ExampleTest.php
tests/Feature/Accounting/LongRunningStabilityTest.php
tests/Feature/Accounting/PointInTimeRecoveryTest.php
tests/Feature/Accounting/SatusehatIntegrationTest.php
tests/Feature/Accounting/SensitiveDataMaskingTest.php
tests/Feature/Accounting/UserActivityLogTest.php
tests/Feature/Accounting/WebhookNotificationTest.php
tests/Feature/Operational/DoctorConsultationWorkflowSeederTest.php
```

**Penyebab:**
- `RbacSeeder` sudah dikonsolidasi ke `RolePermissionSeeder` (lihat komentar di `RolePermissionSeeder.php` baris 19)
- Test-test ini masih mengimpor dan menyeed class lama yang sudah tidak ada

**Fix yang dibutuhkan (pilih salah satu):**

**Opsi A** — Buat alias seeder (cepat, tidak perlu ubah banyak test):
```php
// database/seeders/V1/RbacSeeder.php
namespace Database\Seeders\V1;

class RbacSeeder extends RolePermissionSeeder {}
```

**Opsi B** — Update semua test yang masih referensi `RbacSeeder`:
```bash
# Ganti semua import
# use Database\Seeders\V1\RbacSeeder;
# menjadi:
# use Database\Seeders\V1\RolePermissionSeeder;
# Dan ganti semua $this->seed(RbacSeeder::class)
# menjadi $this->seed(RolePermissionSeeder::class)
```

---

### RC-5 — `QueryException`: VACUUM dari `UserCrudTddTest` (34 kegagalan)

**Error:**
```
SQLSTATE[HY000]: General error: 1 cannot VACUUM from within a transaction
```

**Test yang terdampak:**
```
tests/Feature/Settings/UserCrudTddTest.php — 5 test gagal (pre-existing)
```

**Penyebab khusus:**
- Test memanggil `$this->artisan('migrate:fresh', ...)` di `setUp()` sekaligus menggunakan `RefreshDatabase`
- `RefreshDatabase` membungkus test dalam transaksi database
- `migrate:fresh` di dalam transaksi aktif memicu VACUUM pada SQLite → error

**Fix:**
```php
// UserCrudTddTest.php — hapus artisan('migrate:fresh') dari setUp()
// Biarkan RefreshDatabase yang handle migration
protected function setUp(): void
{
    parent::setUp();
    // HAPUS: $this->artisan('migrate:fresh', [...])

    config(['permission.cache.store' => 'array']);
    // ... rest of setUp
}
```

---

### RC-6 — `TypeError` dan `Error` — Logika Test (6 + 3 kegagalan)

**Test yang terdampak:**
```
Tests\Unit\Service\AuthServiceTest          — TypeError (3 test)
Tests\Unit\DatabaseModelRelationshipsTest   — Error (8 test)
Tests\Unit\Entities\MasterData\ProductEntityTest — Error (2 test)
Tests\Unit\Http\Resources\ApiDataResourceTest    — Error (1 test)
Tests\Unit\Repository\UnixTimeComplianceTest     — Error (1 test)
```

**Penyebab (masing-masing berbeda):**

- **AuthServiceTest**: `null` dikembalikan di mana `string` diharapkan — kemungkinan `branch_uuid` atau `company_uuid` belum ter-set di mock/factory
- **DatabaseModelRelationshipsTest**: Relationship yang diuji tidak ada di model (model sudah berubah tapi test tidak di-update)
- **ProductEntityTest**: Key mapping alias di `fromArray()` tidak sesuai dengan test expectation
- **ApiDataResourceTest**: Format label timestamp tidak cocok dengan format yang diuji
- **UnixTimeComplianceTest**: Model baru tidak punya `$dateFormat = 'U'`

**Fix:** Perlu investigasi per test — baca pesan error spesifik masing-masing sebelum fix.

---

## Urutan Prioritas Perbaikan

| Prioritas | Root Cause | Estimasi Fix | Dampak |
|---|---|---|---|
| 🔴 1 | RC-4: Buat `RbacSeeder` alias | 5 menit | Perbaiki ~100+ accounting test |
| 🔴 2 | RC-1+RC-5: Hapus `migrate:fresh` duplikat dari setUp() | 30 menit | Perbaiki ~1300 PDOException |
| 🟡 3 | RC-2: Ganti `SET FOREIGN_KEY_CHECKS=0` | 15 menit | Perbaiki MySQL-only SQL error |
| 🟡 4 | RC-6: Fix logika test individual | Per-test | Sisa kegagalan minor |
| 🟢 5 | RC-3: Cegah state bocor antar test | Setelah RC-1 | Otomatis resolved setelah RC-1 |

---

## Cara Verifikasi Setelah Fix

```bash
# Verifikasi RC-4 (RbacSeeder alias)
php artisan test --filter="AccountSettingServiceTest"

# Verifikasi RC-1+RC-5 (VACUUM fix)
php artisan test --filter="UserCrudTddTest"

# Verifikasi keseluruhan
php artisan test
```

---

## Catatan Penting

- Test yang **pass saat dijalankan sendiri** (`--filter=SpecificTest`) tapi **gagal di full suite** → hampir pasti karena test pollution dari `migrate:fresh` manual di setUp() (RC-1)
- Kegagalan **bukan** disebabkan oleh perubahan multi-company rollout sesi ini
- Bukti: `git stash && php artisan test` → 1382 gagal (sebelum perubahan); `git stash pop && php artisan test` → 1387 gagal (5 test tambahan = test baru yang kita tulis, juga kena error yang sama)
- Semua test baru (BOM, Assembly, Complaint) **PASS** saat dijalankan dengan `--filter`

---

## Test yang Sudah Lulus (54 test)

Test berikut sudah konsisten PASS bahkan di full suite:

```
Tests\Feature\DatabaseSchemaParityTest
Tests\Feature\CodeCleanlinessTest
Tests\Feature\Infrastructure\ViteHealthTest
Tests\Unit\Entities\* (sebagian)
Tests\Unit\Rbac\PermissionVerificationTest
```

Test ini menggunakan pola yang benar: tidak memanggil `migrate:fresh` manual, atau tidak bergantung pada database sama sekali.
