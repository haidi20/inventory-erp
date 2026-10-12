<?php

namespace Tests\Feature\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * IndexOptimizationTest
 *
 * TDD coverage for Task #10 (Optimalisasi Indexing Database) di
 * `guides/job_desc_guide.md`.
 *
 * Test ini memverifikasi:
 *   1. Kolom primary key (`uuid`) sudah ter-index sebagai PRIMARY.
 *   2. Seluruh kolom relasi `*_uuid` memiliki index dengan kolom
 *      tersebut sebagai leading column (sehingga equality lookup cepat).
 *   3. Kolom pencarian bisnis (`document_no`, `invoice_no`, `code`,
 *      `name`, `status`, `email`) ter-index pada tabel yang
 *      menggunakannya.
 *
 * Strategi: untuk setiap kombinasi (table, column) yang kritikal,
 * ambil daftar index real dari database via `Schema::getIndexes($table)`
 * lalu assert ada index yang columns-nya diawali oleh kolom target
 * (equality-friendly). Dengan begitu, composite index seperti
 * `[short_name, deleted_at]` masih dihitung sebagai index leading
 * `short_name`.
 *
 * Skema sudah harus ter-migrasi sebelum test dijalankan
 * (konsisten dengan `DatabaseSchemaParityTest` dan
 * `CreateBranchesTableTest`).
 */
class IndexOptimizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Jalankan seluruh migrasi v1 agar schema lengkap.
        // Optimasi index sudah dibackport ke migration create table.
    }

    /**
     * Daftar tabel yang memiliki kolom `uuid` sebagai primary key.
     * Kolom `uuid` wajib muncul sebagai PRIMARY di database.
     */
    private const UUID_PRIMARY_TABLES = [
        'branches', 'users', 'audit_logs', 'employees', 'patients',
        'suppliers', 'items', 'services', 'service_packages',
        'service_package_items', 'registrations', 'encounters',
        'encounter_items', 'sales', 'sale_items', 'purchases',
        'purchase_items', 'receivables', 'payables', 'stock_movements',
        'accounts', 'journals', 'journal_lines', 'account_balances',
        'recurring_journals', 'recurring_journal_lines', 'icd10_codes',
        'satusehat_settings', 'encounter_vitals', 'encounter_physicals',
        'encounter_diagnoses', 'clinic_profile', 'companies',
        'prescriptions', 'dispensings', 'dispensing_items',
        'purchase_returns', 'purchase_return_items', 'lab_orders',
        'lab_order_items', 'radiology_orders', 'general_ledgers',
        'bpjs_settings', 'lookup_types', 'lookup_sets', 'lookup_values',
        'locations', 'units', 'price_levels', 'item_prices',
        'item_units', 'item_accounts', 'item_stocks', 'item_details',
        'item_conversions', 'payment_terms', 'taxes', 'people',
        'person_contacts', 'item_suppliers', 'item_stok',
        'sales_orders', 'sales_order_details', 'delivery_orders',
        'delivery_order_details', 'sales_invoices',
        'sales_invoice_details', 'sales_invoice_agings', 'coas',
        'assets', 'boms', 'bom_details', 'bom_costs', 'stock_transfers',
        'stock_transfer_items', 'ingredients', 'stock_adjustments',
        'stock_adjustment_items', 'transactions', 'transaction_details',
        'purchase_orders',
    ];

    /**
     * Daftar (table => [columns]) yang HARUS memiliki index dengan
     * kolom tersebut sebagai leading column.
     *
     * Mencakup:
     *  - kolom relasi `*_uuid`
     *  - kolom pencarian/filter bisnis: `document_no`, `invoice_no`,
     *    `code`, `name`, `status`, `email`
     */
    private const REQUIRED_LEADING_INDEX_MAP = [
        'branches' => ['name', 'company_uuid'],
        'users' => ['branch_uuid'],
        'audit_logs' => ['branch_uuid', 'user_uuid', 'entity_uuid'],
        'employees' => ['branch_uuid', 'user_uuid'],
        'patients' => ['email'],
        'suppliers' => ['branch_uuid', 'email'],
        'items' => ['code', 'name'],
        'services' => ['code'],
        'service_packages' => ['code', 'name'],
        'service_package_items' => ['branch_uuid', 'service_package_uuid', 'item_uuid'],
        'registrations' => ['branch_uuid', 'patient_uuid', 'doctor_uuid'],
        'encounters' => ['branch_uuid', 'registration_uuid', 'patient_uuid', 'doctor_uuid'],
        'encounter_items' => ['branch_uuid', 'encounter_uuid', 'item_uuid'],
        'sales' => ['registration_uuid', 'patient_uuid', 'cashier_uuid'],
        'sale_items' => ['branch_uuid', 'sale_uuid', 'item_uuid'],
        'purchases' => ['invoice_no', 'supplier_uuid', 'received_by_uuid'],
        'purchase_items' => ['purchase_uuid', 'product_uuid'],
        'stock_movements' => ['branch_uuid', 'company_uuid', 'product_uuid', 'location_uuid', 'reference_uuid'],
        'accounts' => ['code', 'name', 'parent_uuid'],
        'closing_periods' => ['name', 'status'],
        'journals' => ['reference_uuid', 'closing_period_uuid'],
        'journal_lines' => ['branch_uuid', 'journal_uuid', 'account_uuid', 'contact_uuid'],
        'account_balances' => ['account_uuid'],
        'recurring_journals' => ['branch_uuid', 'status'],
        'recurring_journal_lines' => ['branch_uuid', 'account_uuid'],
        'encounter_vitals' => ['encounter_uuid'],
        'encounter_physicals' => ['encounter_uuid'],
        'encounter_diagnoses' => ['encounter_uuid', 'status'],
        'clinic_profile' => ['name', 'email'],
        'companies' => ['email'],
        'prescriptions' => ['encounter_uuid', 'patient_uuid', 'doctor_uuid', 'status'],
        'dispensings' => ['patient_uuid', 'dispensed_by_uuid'],
        'dispensing_items' => ['dispensing_uuid', 'product_uuid'],
        'purchase_returns' => ['purchase_uuid', 'supplier_uuid', 'branch_uuid', 'company_uuid', 'document_no', 'status'],
        'purchase_return_items' => ['purchase_return_uuid', 'item_uuid'],
        'lab_orders' => ['patient_uuid', 'doctor_uuid', 'status'],
        'lab_order_items' => ['lab_order_uuid'],
        'radiology_orders' => ['patient_uuid', 'doctor_uuid', 'status'],
        'general_ledgers' => ['branch_uuid', 'journal_uuid'],
        'lookup_types' => ['name'],
        'lookup_sets' => ['code', 'name'],
        'lookup_values' => ['code'],
        'locations' => ['code', 'name', 'company_uuid'],
        'units' => ['code', 'name', 'company_uuid'],
        'price_levels' => ['code', 'name', 'company_uuid'],
        'item_details' => ['branch_uuid', 'company_uuid', 'item_uuid', 'location_uuid', 'status'],
        'payment_terms' => ['name', 'branch_uuid', 'company_uuid'],
        'people' => ['name', 'branch_uuid', 'company_uuid', 'email'],
        'person_contacts' => ['person_uuid', 'name', 'email'],
        'sales_orders' => ['branch_uuid', 'company_uuid', 'person_uuid', 'location_uuid', 'status'],
        'sales_order_details' => ['sales_order_uuid', 'item_uuid'],
        'delivery_orders' => ['branch_uuid', 'company_uuid', 'sales_order_uuid', 'person_uuid', 'location_uuid'],
        'delivery_order_details' => ['delivery_order_uuid', 'item_uuid'],
        'sales_invoices' => ['branch_uuid', 'company_uuid', 'sales_order_uuid', 'person_uuid', 'invoice_no', 'location_uuid', 'status'],
        'sales_invoice_details' => ['sales_invoice_uuid', 'item_uuid'],
        'sales_invoice_agings' => ['sales_invoice_uuid'],
        'coas' => ['code', 'name', 'account_uuid'],
        'assets' => ['branch_uuid', 'company_uuid', 'code', 'name'],
        'boms' => ['branch_uuid', 'company_uuid', 'document_no', 'item_uuid'],
        'bom_details' => ['bom_uuid', 'item_uuid'],
        'bom_costs' => ['bom_uuid', 'item_uuid'],
        'stock_transfers' => ['source_location_uuid', 'destination_location_uuid', 'status'],
        'stock_transfer_items' => ['transfer_uuid', 'product_uuid'],
        'ingredients' => ['branch_uuid', 'company_uuid', 'name'],
        'stock_adjustments' => ['location_uuid', 'status'],
        'stock_adjustment_items' => ['adjustment_uuid', 'item_uuid'],
        'transactions' => ['shift_uuid', 'location_uuid', 'user_uuid', 'status'],
        'transaction_details' => ['transaction_uuid', 'product_uuid'],
        'purchase_orders' => ['status'],
    ];

    /**
     * Test bahwa kolom `uuid` ter-definisi sebagai primary key
     * pada seluruh tabel inti v1.
     */
    public function test_uuid_is_primary_key_on_core_tables(): void
    {
        foreach (self::UUID_PRIMARY_TABLES as $table) {
            if (!Schema::hasTable($table)) {
                $this->markTestSkipped("Table '{$table}' belum ada di schema testing.");
                continue;
            }
            if (!Schema::hasColumn($table, 'uuid')) {
                // Tabel tertentu (mis. password_reset_tokens) tidak
                // punya kolom uuid — skip untuk tabel seperti itu.
                continue;
            }

            $this->assertTrue(
                $this->hasPrimaryOn($table, 'uuid'),
                "Table '{$table}' tidak memiliki PRIMARY KEY pada kolom 'uuid'."
            );
        }
    }

    /**
     * Test bahwa kolom relasi `*_uuid` dan kolom pencarian bisnis
     * memiliki index dengan kolom tersebut sebagai leading column.
     *
     * Diverifikasi berdasarkan peta REQUIRED_LEADING_INDEX_MAP.
     */
    public function test_required_columns_have_leading_column_index(): void
    {
        $missing = [];

        foreach (self::REQUIRED_LEADING_INDEX_MAP as $table => $columns) {
            if (!Schema::hasTable($table)) {
                // Tabel belum ter-migrasi di environment test —
                // tandai agar terlihat tapi lanjut check tabel lain.
                $missing[] = "Table '{$table}' tidak ada di schema.";
                continue;
            }

            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) {
                    // Beberapa tabel didefinisikan ulang di beberapa
                    // migrasi dengan variasi kolom. Jika kolom tidak
                    // ada di schema final, tidak perlu diindex.
                    continue;
                }

                if (!$this->hasLeadingIndex($table, $column)) {
                    $missing[] = "Kolom '{$column}' pada tabel '{$table}' tidak memiliki index dengan kolom tersebut sebagai leading column.";
                }
            }
        }

        $this->assertSame(
            [],
            $missing,
            "Index optimization belum lengkap:\n - " . implode("\n - ", $missing)
        );
    }

    /**
     * Test khusus: tabel transaksi penting (sales_invoices,
     * purchase_returns, boms) harus menyertakan index pada kolom
     * nomor dokumen agar pencarian dokumen tidak melakukan full
     * table scan.
     */
    public function test_document_number_columns_are_indexed(): void
    {
        $cases = [
            ['purchases', 'invoice_no'],
            ['sales_invoices', 'invoice_no'],
            ['purchase_returns', 'document_no'],
            ['boms', 'document_no'],
        ];

        foreach ($cases as [$table, $column]) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
                continue;
            }
            $this->assertTrue(
                $this->hasLeadingIndex($table, $column),
                "Kolom dokumen '{$column}' pada tabel '{$table}' wajib ter-index."
            );
        }
    }

    /**
     * Test khusus: kolom filter `status` pada tabel transaksi besar
     * (sales_orders, sales_invoices, purchase_returns, lab_orders,
     * radiology_orders, prescriptions, transactions, stock_adjustments,
     * stock_transfers) harus ter-index.
     */
    public function test_status_columns_on_transaction_tables_are_indexed(): void
    {
        $tables = [
            'sales_orders', 'sales_invoices', 'purchase_returns',
            'purchase_orders', 'lab_orders', 'radiology_orders',
            'prescriptions', 'transactions', 'stock_adjustments',
            'stock_transfers', 'encounter_diagnoses',
            'recurring_journals', 'closing_periods', 'item_details',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'status')) {
                continue;
            }
            $this->assertTrue(
                $this->hasLeadingIndex($table, 'status'),
                "Kolom 'status' pada tabel '{$table}' wajib ter-index."
            );
        }
    }

    /**
     * Test composite `(branch_uuid, deleted_at)` — covering index
     * untuk pola query standar `WHERE branch_uuid = ? AND deleted_at
     * IS NULL`. Semua tabel transaksional tenant-aware harus
     * memiliki composite ini.
     */
    public function test_tenant_soft_delete_composite_index_exists(): void
    {
        $tables = [
            'sales_orders', 'sales_invoices', 'sales_invoice_details',
            'sales_invoice_agings', 'delivery_orders',
            'delivery_order_details', 'purchase_returns',
            'purchase_return_items', 'purchase_return_payments',
            'purchases', 'purchase_items',
            'journals', 'journal_lines', 'general_ledgers',
            'recurring_journals', 'recurring_journal_lines',
            'account_balances', 'encounters', 'encounter_items',
            'registrations', 'transactions', 'transaction_details',
            'shifts', 'stock_adjustments', 'stock_adjustment_items',
            'stock_transfers', 'stock_transfer_items',
            'stock_movements', 'audit_logs', 'payment_terms',
            'people', 'person_balances', 'person_saldo_details',
            'opening_balance_records', 'item_details', 'item_accounts',
            'item_stocks', 'item_balances', 'item_stok',
            'item_discount_rules', 'item_discounts', 'assets',
            'ingredients', 'boms', 'assemblies', 'assembly_items',
            'users', 'locations', 'payables', 'receivables',
            'sales', 'sale_items',
        ];

        $missing = [];
        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            if (!Schema::hasColumn($table, 'branch_uuid') || !Schema::hasColumn($table, 'deleted_at')) {
                continue;
            }
            if (!$this->hasCompositeIndex($table, ['branch_uuid', 'deleted_at'])) {
                $missing[] = "Tabel '{$table}' tidak memiliki composite index (branch_uuid, deleted_at).";
            }
        }

        $this->assertSame([], $missing,
            "Composite tenant+soft-delete belum lengkap:\n - " . implode("\n - ", $missing));
    }

    /**
     * Test composite `(company_uuid, deleted_at)` untuk tabel
     * multi-company.
     */
    public function test_company_soft_delete_composite_index_exists(): void
    {
        $tables = [
            'branches', 'sales_orders', 'sales_invoices',
            'sales_invoice_details', 'sales_invoice_agings',
            'delivery_orders', 'delivery_order_details',
            'purchase_returns', 'purchase_return_items',
            'purchase_return_payments', 'purchases', 'purchase_items',
            'account_balances', 'recurring_journal_lines',
            'transactions', 'transaction_details', 'shifts',
            'stock_adjustments', 'stock_adjustment_items',
            'stock_transfers', 'stock_transfer_items',
            'stock_movements', 'payment_terms', 'people',
            'person_balances', 'person_saldo_details',
            'opening_balance_records', 'item_details', 'item_accounts',
            'item_stocks', 'item_balances', 'item_stok',
            'item_discount_rules', 'item_discounts', 'assets',
            'ingredients', 'boms', 'assemblies', 'assembly_items',
            'locations',
        ];

        $missing = [];
        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            if (!Schema::hasColumn($table, 'company_uuid') || !Schema::hasColumn($table, 'deleted_at')) {
                continue;
            }
            if (!$this->hasCompositeIndex($table, ['company_uuid', 'deleted_at'])) {
                $missing[] = "Tabel '{$table}' tidak memiliki composite index (company_uuid, deleted_at).";
            }
        }

        $this->assertSame([], $missing,
            "Composite company+soft-delete belum lengkap:\n - " . implode("\n - ", $missing));
    }

    /**
     * Test composite `(branch_uuid, status)` — pola filter tenant +
     * status dokumen pada tabel transaksi.
     */
    public function test_tenant_status_composite_index_exists(): void
    {
        $tables = [
            'sales_orders', 'sales_invoices', 'purchase_returns',
            'purchase_orders', 'transactions', 'stock_adjustments',
            'stock_transfers', 'encounters', 'registrations',
            'journals', 'recurring_journals', 'payables',
            'receivables', 'item_details', 'assemblies',
        ];

        $missing = [];
        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            if (!Schema::hasColumn($table, 'branch_uuid') || !Schema::hasColumn($table, 'status')) {
                continue;
            }
            if (!$this->hasCompositeIndex($table, ['branch_uuid', 'status'])) {
                $missing[] = "Tabel '{$table}' tidak memiliki composite index (branch_uuid, status).";
            }
        }

        $this->assertSame([], $missing,
            "Composite tenant+status belum lengkap:\n - " . implode("\n - ", $missing));
    }

    /**
     * Test composite `(branch_uuid, invoice_no)` dan
     * `(branch_uuid, document_no)` untuk lookup dokumen unik per
     * cabang (standar industri: `invoice_no` per cabang bukan
     * global).
     */
    public function test_tenant_document_composite_index_exists(): void
    {
        $cases = [
            ['sales_invoices', ['branch_uuid', 'invoice_no']],
            ['purchases', ['branch_uuid', 'invoice_no']],
            ['purchase_returns', ['branch_uuid', 'document_no']],
            ['purchase_orders', ['branch_uuid', 'document_no']],
            ['boms', ['branch_uuid', 'document_no']],
        ];

        foreach ($cases as [$table, $combo]) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            $skip = false;
            foreach ($combo as $col) {
                if (!Schema::hasColumn($table, $col)) {
                    $skip = true;
                    break;
                }
            }
            if ($skip) {
                continue;
            }
            $this->assertTrue(
                $this->hasCompositeIndex($table, $combo),
                "Tabel '{$table}' wajib memiliki composite index (" . implode(', ', $combo) . ")."
            );
        }
    }

    /**
     * Test composite `(branch_uuid, invoice_date)` untuk query
     * laporan berbasis rentang tanggal per cabang.
     */
    public function test_tenant_date_range_composite_index_exists(): void
    {
        if (!Schema::hasTable('sales_invoices')) {
            $this->markTestSkipped('sales_invoices belum ada.');
            return;
        }
        if (!Schema::hasColumn('sales_invoices', 'invoice_date')
            || !Schema::hasColumn('sales_invoices', 'branch_uuid')) {
            $this->markTestSkipped('Kolom belum tersedia di sales_invoices.');
            return;
        }

        $this->assertTrue(
            $this->hasCompositeIndex('sales_invoices', ['branch_uuid', 'invoice_date']),
            "Tabel 'sales_invoices' wajib memiliki composite (branch_uuid, invoice_date) untuk query rentang tanggal."
        );
    }

    /**
     * Helper — periksa apakah ada index pada `$table` dengan `$column`
     * sebagai leading column (kolom pertama).
     */
    private function hasLeadingIndex(string $table, string $column): bool
    {
        foreach (Schema::getIndexes($table) as $index) {
            $columns = $index['columns'] ?? [];
            if (!is_array($columns) || $columns === []) {
                continue;
            }
            if ((string) $columns[0] === $column) {
                return true;
            }
        }

        return false;
    }

    /**
     * Helper — periksa apakah `$column` adalah primary key di `$table`.
     */
    private function hasPrimaryOn(string $table, string $column): bool
    {
        foreach (Schema::getIndexes($table) as $index) {
            $isPrimary = (bool) ($index['primary'] ?? false);
            $columns = $index['columns'] ?? [];
            if ($isPrimary && is_array($columns) && count($columns) === 1 && (string) $columns[0] === $column) {
                return true;
            }
        }

        return false;
    }

    /**
     * Helper — periksa apakah ada index dengan daftar kolom EXACT
     * (urutan sama) seperti `$expected`. Digunakan untuk validasi
     * composite index.
     */
    private function hasCompositeIndex(string $table, array $expected): bool
    {
        $target = implode(',', array_map('strval', $expected));
        foreach (Schema::getIndexes($table) as $index) {
            $columns = $index['columns'] ?? [];
            if (!is_array($columns)) {
                continue;
            }
            $actual = implode(',', array_map('strval', $columns));
            if ($actual === $target) {
                return true;
            }
        }

        return false;
    }
}
