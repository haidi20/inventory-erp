# Panduan Multi-Tenant (Branch) Scoping

## Konsep

Sistem ini mendukung arsitektur multi-tenant berbasis cabang (`branch_id`). Setiap record transaksi akuntansi wajib terikat ke `branch_id` untuk memastikan isolasi data antar klinik/cabang.

## Cara Kerja

### 1. Scoping Trait: HasBranchScope

Trait `App\Traits\HasBranchScope` digunakan untuk level cabang secara otomatis:
- **Query**: Menerapkan `BranchScope` yang memfilter data berdasarkan `branch_id` dari user yang sedang login.
- **Creating**: Mengisi `branch_id` secara otomatis jika kosong.

```php
use App\Traits\HasBranchScope;

class TransactionModel extends Model
{
    use HasBranchScope;

    protected $fillable = ['branch_id', /* fields lain */];
}
```

### 2. Migration Standard

Setiap tabel baru yang bersifat transaksional wajib menyertakan Foreign Key `branch_id` dan composite unique constraint jika diperlukan:

```php
// Branch level correlation
$table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();

// Contoh unique constraint dengan tenant prefix
$table->unique(['branch_id', 'reference_no']);
```

### 3. Transaction Code

Format: `{SHORT_NAME}/{SOURCE}/{YYYYMM}/{INDEX}`

| Segment | Contoh | Keterangan |
|---------|--------|------------|
| SHORT_NAME | `KDM` | Kode singkatan klinik/cabang |
| SOURCE | `SAL` | Kode dari `JournalReferenceType` enum |
| YYYYMM | `202603` | Bulan transaksi |
| INDEX | `0001` | Auto-increment per bulan/branch/source |

Contoh: `KDM/SAL/202603/0001`

Source code mapping di `TransactionCodeService::resolveSourceCode()`:
- `SALE` → `SAL`
- `POS_SALE` → `POS`
- `PURCHASE` → `PUR`
- `ENCOUNTER` → `ENC`
- `REGISTRATION` → `REG`
- `STOCK_ADJUSTMENT` → `ADJ`
- Default → `GEN`

### 4. Seeder

Saat menjalankan `DemoSeeder`, data awal akan diikat ke branch demo yang tersedia.

```bash
php artisan migrate:fresh --seed
```
