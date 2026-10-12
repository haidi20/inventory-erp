<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseSchemaParityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_branches_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('branches'),
            "Table 'branches' does not exist."
        );

        $expectedColumns = ['company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'description', 'id', 'is_active', 'name', 'short_name', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('branches', $expectedColumns),
            "Table 'branches' is missing some expected columns."
        );
    }

    public function test_users_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('users'),
            "Table 'users' does not exist."
        );

        $expectedColumns = ['created_at', 'created_by', 'deleted_at', 'deleted_by', 'email', 'email_verified_at', 'is_active', 'name', 'password', 'remember_token', 'updated_at', 'updated_by', 'username', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('users', $expectedColumns),
            "Table 'users' is missing some expected columns."
        );

        $this->assertFalse(Schema::hasColumns('users', ['branch_uuid', 'company_uuid']));
        $this->assertTrue(Schema::hasColumns('user_companies', ['user_uuid', 'company_uuid', 'is_default']));
        $this->assertTrue(Schema::hasColumns('user_branches', ['user_uuid', 'company_uuid', 'branch_uuid', 'is_default']));
    }

    public function test_password_reset_tokens_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('password_reset_tokens'),
            "Table 'password_reset_tokens' does not exist."
        );

        $expectedColumns = ['created_at', 'email', 'token'];

        $this->assertTrue(
            Schema::hasColumns('password_reset_tokens', $expectedColumns),
            "Table 'password_reset_tokens' is missing some expected columns."
        );
    }

    public function test_sessions_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('sessions'),
            "Table 'sessions' does not exist."
        );

        $expectedColumns = ['id', 'ip_address', 'last_activity', 'payload', 'user_agent', 'user_id', 'user_uuid'];

        $this->assertTrue(
            Schema::hasColumns('sessions', $expectedColumns),
            "Table 'sessions' is missing some expected columns."
        );
    }

    public function test_cache_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('cache'),
            "Table 'cache' does not exist."
        );

        $expectedColumns = ['expiration', 'key', 'value'];

        $this->assertTrue(
            Schema::hasColumns('cache', $expectedColumns),
            "Table 'cache' is missing some expected columns."
        );
    }

    public function test_cache_locks_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('cache_locks'),
            "Table 'cache_locks' does not exist."
        );

        $expectedColumns = ['expiration', 'key', 'owner'];

        $this->assertTrue(
            Schema::hasColumns('cache_locks', $expectedColumns),
            "Table 'cache_locks' is missing some expected columns."
        );
    }

    public function test_jobs_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('jobs'),
            "Table 'jobs' does not exist."
        );

        $expectedColumns = ['attempts', 'available_at', 'created_at', 'payload', 'queue', 'reserved_at', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('jobs', $expectedColumns),
            "Table 'jobs' is missing some expected columns."
        );
    }

    public function test_job_batches_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('job_batches'),
            "Table 'job_batches' does not exist."
        );

        $expectedColumns = ['cancelled_at', 'created_at', 'failed_job_uuids', 'failed_jobs', 'finished_at', 'id', 'name', 'options', 'pending_jobs', 'total_jobs'];

        $this->assertTrue(
            Schema::hasColumns('job_batches', $expectedColumns),
            "Table 'job_batches' is missing some expected columns."
        );
    }

    public function test_failed_jobs_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('failed_jobs'),
            "Table 'failed_jobs' does not exist."
        );

        $expectedColumns = ['connection', 'exception', 'failed_at', 'payload', 'queue', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('failed_jobs', $expectedColumns),
            "Table 'failed_jobs' is missing some expected columns."
        );
    }

    public function test_audit_logs_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('audit_logs'),
            "Table 'audit_logs' does not exist."
        );

        $expectedColumns = ['after', 'before', 'branch_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'entity_type', 'entity_uuid', 'event', 'ip_address', 'updated_at', 'updated_by', 'user_agent', 'user_uuid', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('audit_logs', $expectedColumns),
            "Table 'audit_logs' is missing some expected columns."
        );
    }

    public function test_employees_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('employees'),
            "Table 'employees' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'fee_default', 'ihs_practitioner_id', 'is_active', 'name', 'nik', 'role', 'satusehat_last_sync', 'sip_number', 'specialization', 'str_number', 'updated_at', 'updated_by', 'user_uuid', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('employees', $expectedColumns),
            "Table 'employees' is missing some expected columns."
        );
    }

    public function test_patients_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('patients'),
            "Table 'patients' does not exist."
        );

        $expectedColumns = ['address', 'birth_date', 'bpjs_number', 'branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'email', 'gender', 'ihs_number', 'is_active', 'medical_record_no', 'name', 'nik', 'phone', 'satusehat_last_sync', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('patients', $expectedColumns),
            "Table 'patients' is missing some expected columns."
        );
    }

    public function test_suppliers_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('suppliers'),
            "Table 'suppliers' does not exist."
        );

        $expectedColumns = ['address', 'branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'email', 'is_active', 'name', 'phone', 'term_days', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('suppliers', $expectedColumns),
            "Table 'suppliers' is missing some expected columns."
        );
    }

    public function test_product_model_storage_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('items'),
            "Canonical product storage table 'items' does not exist."
        );

        $expectedColumns = [
            'uuid', 'id', 'branch_uuid', 'company_uuid', 'code', 'name', 'barcode',
            'product_uuid', 'base_unit_uuid', 'base_price_buy', 'base_price_sell',
            'min_stock', 'max_stock', 'max_order', 'storage_bin_code', 'image',
            'is_bom_enabled', 'is_active', 'deleted_at', 'created_by', 'updated_by',
            'deleted_by', 'created_at', 'updated_at',
        ];

        $this->assertTrue(
            Schema::hasColumns('items', $expectedColumns),
            "Canonical product storage table 'items' is missing some expected columns."
        );
    }

    public function test_items_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('items'),
            "Table 'items' does not exist."
        );

        $expectedColumns = [
            'uuid', 'id', 'branch_uuid', 'company_uuid', 'product_uuid',
            'code', 'name', 'description', 'note', 'barcode',
            'item_type', 'base_unit_uuid', 'default_location_uuid', 'storage_bin_code',
            'item_category_lookup_uuid', 'item_group_lookup_uuid', 'item_type_lookup_uuid',
            'brand_lookup_uuid', 'origin_lookup_uuid', 'grade_lookup_uuid',
            'price_buy', 'price_sell', 'unit', 'category', 'location', 'remarks',
            'kind', 'brand', 'origin', 'grade',
            'latest_buy_price', 'latest_sell_price', 'base_price_buy', 'base_price_sell',
            'standard_cost', 'min_stock', 'max_stock', 'min_order', 'max_order',
            'length_cm', 'width_cm', 'height_cm', 'is_bom_enabled',
            'coa_inventory_uuid', 'coa_hpp_uuid', 'coa_sales_uuid',
            'created_at', 'updated_at', 'deleted_at', 'created_by', 'updated_by', 'deleted_by',
        ];

        $this->assertTrue(
            Schema::hasColumns('items', $expectedColumns),
            "Table 'items' is missing some expected columns."
        );
    }

    public function test_item_custom_fields_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('item_custom_fields'),
            "Table 'item_custom_fields' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'field_key', 'field_value', 'item_uuid', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('item_custom_fields', $expectedColumns),
            "Table 'item_custom_fields' is missing some expected columns."
        );
    }

    public function test_item_discounts_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('item_discounts'),
            "Table 'item_discounts' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'discount_percen', 'id', 'item_uuid', 'qty', 'uom_code', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('item_discounts', $expectedColumns),
            "Table 'item_discounts' is missing some expected columns."
        );
    }

    public function test_services_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('services'),
            "Table 'services' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'code', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'doctor_fee', 'duration_minutes', 'is_active', 'name', 'price', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('services', $expectedColumns),
            "Table 'services' is missing some expected columns."
        );
    }

    public function test_service_packages_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('service_packages'),
            "Table 'service_packages' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'code', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'is_active', 'name', 'price', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('service_packages', $expectedColumns),
            "Table 'service_packages' is missing some expected columns."
        );
    }

    public function test_service_package_items_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('service_package_items'),
            "Table 'service_package_items' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'item_type', 'item_uuid', 'qty', 'service_package_uuid', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('service_package_items', $expectedColumns),
            "Table 'service_package_items' is missing some expected columns."
        );
    }

    public function test_files_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('files'),
            "Table 'files' does not exist."
        );

        $expectedColumns = ['accessed_at', 'branch_uuid', 'company_uuid', 'context_identifier', 'context_type', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'disk', 'feature', 'file_category', 'file_hash', 'file_path', 'file_size', 'id', 'is_latest', 'is_primary', 'mime_type', 'original_filename', 'parent_model', 'parent_uuid', 'processing_status', 'sort_order', 'updated_at', 'updated_by', 'uploaded_by_uuid', 'uploaded_ip', 'uuid', 'version'];

        $this->assertTrue(
            Schema::hasColumns('files', $expectedColumns),
            "Table 'files' is missing some expected columns."
        );
    }

    public function test_file_variants_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('file_variants'),
            "Table 'file_variants' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'file_path', 'file_size', 'file_uuid', 'height', 'id', 'updated_at', 'updated_by', 'uuid', 'variant_type', 'width'];

        $this->assertTrue(
            Schema::hasColumns('file_variants', $expectedColumns),
            "Table 'file_variants' is missing some expected columns."
        );
    }

    public function test_file_audits_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('file_audits'),
            "Table 'file_audits' does not exist."
        );

        $expectedColumns = ['action', 'actor_ip', 'actor_uuid', 'branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'file_uuid', 'id', 'note', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('file_audits', $expectedColumns),
            "Table 'file_audits' is missing some expected columns."
        );
    }

    public function test_registrations_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('registrations'),
            "Table 'registrations' does not exist."
        );

        $expectedColumns = ['bpjs_no', 'branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'doctor_uuid', 'notes', 'patient_uuid', 'payment_type', 'poli', 'queue_no', 'registered_at', 'status', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('registrations', $expectedColumns),
            "Table 'registrations' is missing some expected columns."
        );
    }

    public function test_encounters_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('encounters'),
            "Table 'encounters' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'complaint', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'diagnosis', 'doctor_uuid', 'notes', 'patient_uuid', 'registration_uuid', 'status', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('encounters', $expectedColumns),
            "Table 'encounters' is missing some expected columns."
        );
    }

    public function test_encounter_items_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('encounter_items'),
            "Table 'encounter_items' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'encounter_uuid', 'item_type', 'item_uuid', 'price', 'qty', 'subtotal', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('encounter_items', $expectedColumns),
            "Table 'encounter_items' is missing some expected columns."
        );
    }

    public function test_sales_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('sales'),
            "Table 'sales' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'cashier_uuid', 'change', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'paid', 'patient_uuid', 'payment_method', 'payment_status', 'registration_uuid', 'sale_at', 'sale_no', 'total_amount', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('sales', $expectedColumns),
            "Table 'sales' is missing some expected columns."
        );
    }

    public function test_sale_items_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('sale_items'),
            "Table 'sale_items' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'item_type', 'item_uuid', 'price', 'qty', 'sale_uuid', 'subtotal', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('sale_items', $expectedColumns),
            "Table 'sale_items' is missing some expected columns."
        );
    }

    public function test_purchases_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('purchases'),
            "Table 'purchases' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'invoice_no', 'paid', 'purchase_date', 'purchase_order_uuid', 'received_at', 'received_by_uuid', 'status', 'supplier_uuid', 'total', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('purchases', $expectedColumns),
            "Table 'purchases' is missing some expected columns."
        );
    }

    public function test_purchase_items_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('purchase_items'),
            "Table 'purchase_items' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'price', 'product_uuid', 'purchase_uuid', 'qty', 'subtotal', 'unit_uuid', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('purchase_items', $expectedColumns),
            "Table 'purchase_items' is missing some expected columns."
        );
    }

    public function test_product_purchases_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('product_purchases'),
            "Table 'product_purchases' does not exist."
        );

        $expectedColumns = ['created_at', 'created_by', 'deleted_at', 'deleted_by', 'discount', 'imei_number', 'net_unit_cost', 'product_batch_uuid', 'product_uuid', 'purchase_unit_uuid', 'purchase_uuid', 'qty', 'recieved', 'tax', 'tax_rate', 'total', 'updated_at', 'updated_by', 'uuid', 'variant_uuid'];

        $this->assertTrue(
            Schema::hasColumns('product_purchases', $expectedColumns),
            "Table 'product_purchases' is missing some expected columns."
        );
    }

    public function test_product_quotation_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('product_quotation'),
            "Table 'product_quotation' does not exist."
        );

        $expectedColumns = ['created_at', 'created_by', 'deleted_at', 'deleted_by', 'discount', 'net_unit_price', 'product_batch_uuid', 'product_uuid', 'qty', 'quotation_uuid', 'sale_unit_uuid', 'tax', 'tax_rate', 'total', 'updated_at', 'updated_by', 'uuid', 'variant_uuid'];

        $this->assertTrue(
            Schema::hasColumns('product_quotation', $expectedColumns),
            "Table 'product_quotation' is missing some expected columns."
        );
    }

    public function test_product_sales_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('product_sales'),
            "Table 'product_sales' does not exist."
        );

        $expectedColumns = ['created_at', 'created_by', 'deleted_at', 'deleted_by', 'discount', 'imei_number', 'net_unit_price', 'product_batch_uuid', 'product_uuid', 'qty', 'return_qty', 'sale_unit_uuid', 'sale_uuid', 'tax', 'tax_rate', 'total', 'updated_at', 'updated_by', 'uuid', 'variant_uuid'];

        $this->assertTrue(
            Schema::hasColumns('product_sales', $expectedColumns),
            "Table 'product_sales' is missing some expected columns."
        );
    }

    public function test_receivables_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('receivables'),
            "Table 'receivables' does not exist."
        );

        $expectedColumns = ['amount', 'branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'due_date', 'paid', 'reference_type', 'reference_uuid', 'status', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('receivables', $expectedColumns),
            "Table 'receivables' is missing some expected columns."
        );
    }

    public function test_payables_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('payables'),
            "Table 'payables' does not exist."
        );

        $expectedColumns = ['amount', 'branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'due_date', 'paid', 'reference_type', 'reference_uuid', 'status', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('payables', $expectedColumns),
            "Table 'payables' is missing some expected columns."
        );
    }

    public function test_stock_movements_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('stock_movements'),
            "Table 'stock_movements' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'location_uuid', 'notes', 'price', 'product_uuid', 'qty', 'reference_type', 'reference_uuid', 'type', 'updated_at', 'updated_by', 'user_uuid', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('stock_movements', $expectedColumns),
            "Table 'stock_movements' is missing some expected columns."
        );
    }

    public function test_accounts_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('accounts'),
            "Table 'accounts' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'code', 'company_uuid', 'created_at', 'created_by', 'currency_uuid', 'deleted_at', 'deleted_by', 'exchange_rate', 'financial_statement', 'is_active', 'is_cash_bank', 'is_giro', 'is_header', 'level', 'name', 'parent_uuid', 'type', 'type_saldo', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('accounts', $expectedColumns),
            "Table 'accounts' is missing some expected columns."
        );
    }

    public function test_account_mappings_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('account_mappings'),
            "Table 'account_mappings' does not exist."
        );

        $expectedColumns = ['account_uuid', 'branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'key', 'menu_name', 'note', 'type_saldo', 'updated_at', 'updated_by', 'uuid', 'value_source'];

        $this->assertTrue(
            Schema::hasColumns('account_mappings', $expectedColumns),
            "Table 'account_mappings' is missing some expected columns."
        );
    }

    public function test_tax_settings_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('tax_settings'),
            "Table 'tax_settings' does not exist."
        );

        $expectedColumns = ['account_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'is_active', 'name', 'percentage', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('tax_settings', $expectedColumns),
            "Table 'tax_settings' is missing some expected columns."
        );
    }

    public function test_closing_periods_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('closing_periods'),
            "Table 'closing_periods' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'closed_at', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'end_date', 'name', 'period', 'start_date', 'status', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('closing_periods', $expectedColumns),
            "Table 'closing_periods' is missing some expected columns."
        );
    }

    public function test_journals_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('journals'),
            "Table 'journals' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'closing_period_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'description', 'fiscal_type', 'is_adjusting', 'is_fiscal', 'is_processed', 'is_system', 'journal_date', 'reference_model', 'reference_no', 'reference_type', 'reference_uuid', 'reversal_date', 'reversal_journal_uuid', 'reversal_status', 'scheduled_at', 'status', 'transaction_code', 'type', 'updated_at', 'updated_by', 'uuid', 'version', 'workflow_status'];

        $this->assertTrue(
            Schema::hasColumns('journals', $expectedColumns),
            "Table 'journals' is missing some expected columns."
        );
    }

    public function test_journal_lines_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('journal_lines'),
            "Table 'journal_lines' does not exist."
        );

        $expectedColumns = ['account_uuid', 'branch_uuid', 'company_uuid', 'contact_uuid', 'created_at', 'created_by', 'credit', 'debit', 'deleted_at', 'deleted_by', 'description', 'exchange_rate', 'fiscal_type', 'foreign_amount', 'foreign_currency', 'is_fiscal', 'is_processed', 'is_reconciled', 'journal_uuid', 'tax_rate', 'tax_type', 'taxable', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('journal_lines', $expectedColumns),
            "Table 'journal_lines' is missing some expected columns."
        );
    }

    public function test_account_balances_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('account_balances'),
            "Table 'account_balances' does not exist."
        );

        $expectedColumns = ['account_uuid', 'branch_uuid', 'closing_balance', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'opening_balance', 'period', 'total_credit', 'total_debit', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('account_balances', $expectedColumns),
            "Table 'account_balances' is missing some expected columns."
        );
    }

    public function test_recurring_journals_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('recurring_journals'),
            "Table 'recurring_journals' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'end_date', 'frequency', 'journal_data', 'last_run_date', 'next_run_date', 'status', 'template_name', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('recurring_journals', $expectedColumns),
            "Table 'recurring_journals' is missing some expected columns."
        );
    }

    public function test_recurring_journal_lines_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('recurring_journal_lines'),
            "Table 'recurring_journal_lines' does not exist."
        );

        $expectedColumns = ['account_uuid', 'branch_uuid', 'company_uuid', 'created_at', 'credit', 'debit', 'deleted_at', 'recurring_journal_uuid', 'updated_at', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('recurring_journal_lines', $expectedColumns),
            "Table 'recurring_journal_lines' is missing some expected columns."
        );
    }

    public function test_journal_attachments_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('journal_attachments'),
            "Table 'journal_attachments' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'file_name', 'file_path', 'file_size', 'file_type', 'journal_uuid', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('journal_attachments', $expectedColumns),
            "Table 'journal_attachments' is missing some expected columns."
        );
    }

    public function test_icd10_codes_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('icd10_codes'),
            "Table 'icd10_codes' does not exist."
        );

        $expectedColumns = ['category', 'code', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'name_en', 'name_uuid', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('icd10_codes', $expectedColumns),
            "Table 'icd10_codes' is missing some expected columns."
        );
    }

    public function test_satusehat_settings_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('satusehat_settings'),
            "Table 'satusehat_settings' does not exist."
        );

        $expectedColumns = ['auth_url', 'base_url', 'client_secret', 'client_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'is_active', 'org_uuid', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('satusehat_settings', $expectedColumns),
            "Table 'satusehat_settings' is missing some expected columns."
        );
    }

    public function test_encounter_vitals_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('encounter_vitals'),
            "Table 'encounter_vitals' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'diastolic_bp', 'encounter_uuid', 'heart_rate', 'height_cm', 'respiratory_rate', 'satusehat_uuid', 'spo2', 'synced_at', 'systolic_bp', 'temperature_c', 'updated_at', 'updated_by', 'uuid', 'weight_kg'];

        $this->assertTrue(
            Schema::hasColumns('encounter_vitals', $expectedColumns),
            "Table 'encounter_vitals' is missing some expected columns."
        );
    }

    public function test_encounter_physicals_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('encounter_physicals'),
            "Table 'encounter_physicals' does not exist."
        );

        $expectedColumns = ['abdomen', 'branch_uuid', 'company_uuid', 'consciousness', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'encounter_uuid', 'extremities', 'general_condition', 'head_neck', 'neurological', 'satusehat_uuid', 'skin', 'synced_at', 'thorax_lung', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('encounter_physicals', $expectedColumns),
            "Table 'encounter_physicals' is missing some expected columns."
        );
    }

    public function test_encounter_diagnoses_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('encounter_diagnoses'),
            "Table 'encounter_diagnoses' does not exist."
        );

        $expectedColumns = ['assessment_notes', 'branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'encounter_uuid', 'icd10_code', 'icd10_name', 'satusehat_uuid', 'status', 'symptoms', 'synced_at', 'type', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('encounter_diagnoses', $expectedColumns),
            "Table 'encounter_diagnoses' is missing some expected columns."
        );
    }

    public function test_clinic_profile_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('clinic_profile'),
            "Table 'clinic_profile' does not exist."
        );

        $expectedColumns = ['address', 'city', 'clinic_type', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'district', 'email', 'name', 'phone', 'postal_code', 'province', 'registration_number', 'satusehat_org_uuid', 'synced_at', 'updated_at', 'updated_by', 'uuid', 'website'];

        $this->assertTrue(
            Schema::hasColumns('clinic_profile', $expectedColumns),
            "Table 'clinic_profile' is missing some expected columns."
        );
    }

    public function test_companies_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('companies'),
            "Table 'companies' does not exist."
        );

        $expectedColumns = ['address', 'bank_account', 'bank_holder', 'bank_name', 'city', 'clinic_type', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'district', 'email', 'id', 'is_active', 'name', 'phone', 'postal_code', 'province', 'registration_number', 'satusehat_org_uuid', 'synced_at', 'timezone', 'updated_at', 'updated_by', 'uuid', 'website'];

        $this->assertTrue(
            Schema::hasColumns('companies', $expectedColumns),
            "Table 'companies' is missing some expected columns."
        );
    }

    public function test_prescriptions_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('prescriptions'),
            "Table 'prescriptions' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'doctor_uuid', 'dosage', 'duration_days', 'encounter_uuid', 'frequency', 'instructions', 'medication_code', 'medication_name', 'patient_uuid', 'quantity', 'route', 'satusehat_uuid', 'status', 'synced_at', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('prescriptions', $expectedColumns),
            "Table 'prescriptions' is missing some expected columns."
        );
    }

    public function test_dispensings_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('dispensings'),
            "Table 'dispensings' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'dispensed_at', 'dispensed_by_uuid', 'dispensing_no', 'notes', 'patient_uuid', 'total', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('dispensings', $expectedColumns),
            "Table 'dispensings' is missing some expected columns."
        );
    }

    public function test_dispensing_items_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('dispensing_items'),
            "Table 'dispensing_items' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'dispensing_uuid', 'price_sell', 'product_uuid', 'qty', 'subtotal', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('dispensing_items', $expectedColumns),
            "Table 'dispensing_items' is missing some expected columns."
        );
    }

    public function test_purchase_returns_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('purchase_returns'),
            "Table 'purchase_returns' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'purchase_uuid', 'reason', 'return_no', 'returned_at', 'returned_by_uuid', 'supplier_uuid', 'total', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('purchase_returns', $expectedColumns),
            "Table 'purchase_returns' is missing some expected columns."
        );
    }

    public function test_purchase_return_items_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('purchase_return_items'),
            "Table 'purchase_return_items' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'price', 'product_uuid', 'qty', 'return_uuid', 'subtotal', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('purchase_return_items', $expectedColumns),
            "Table 'purchase_return_items' is missing some expected columns."
        );
    }

    public function test_lab_orders_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('lab_orders'),
            "Table 'lab_orders' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'clinical_notes', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'doctor_uuid', 'order_no', 'ordered_at', 'patient_uuid', 'resulted_at', 'resulted_by', 'status', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('lab_orders', $expectedColumns),
            "Table 'lab_orders' is missing some expected columns."
        );
    }

    public function test_lab_order_items_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('lab_order_items'),
            "Table 'lab_order_items' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'flag', 'lab_order_uuid', 'normal_range', 'result', 'test_code', 'test_name', 'unit', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('lab_order_items', $expectedColumns),
            "Table 'lab_order_items' is missing some expected columns."
        );
    }

    public function test_radiology_orders_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('radiology_orders'),
            "Table 'radiology_orders' does not exist."
        );

        $expectedColumns = ['body_part', 'branch_uuid', 'clinical_notes', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'doctor_uuid', 'findings', 'impression', 'modality', 'order_no', 'ordered_at', 'patient_uuid', 'radiologist_uuid', 'resulted_at', 'status', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('radiology_orders', $expectedColumns),
            "Table 'radiology_orders' is missing some expected columns."
        );
    }

    public function test_general_ledgers_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('general_ledgers'),
            "Table 'general_ledgers' does not exist."
        );

        $expectedColumns = ['account_uuid', 'balance', 'branch_uuid', 'company_uuid', 'created_at', 'created_by', 'credit', 'debit', 'deleted_at', 'deleted_by', 'description', 'is_reconciled', 'is_system', 'journal_date', 'journal_line_uuid', 'journal_uuid', 'reference_no', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('general_ledgers', $expectedColumns),
            "Table 'general_ledgers' is missing some expected columns."
        );
    }

    public function test_transaction_sequences_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('transaction_sequences'),
            "Table 'transaction_sequences' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'last_index', 'period', 'type_code', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('transaction_sequences', $expectedColumns),
            "Table 'transaction_sequences' is missing some expected columns."
        );
    }

    public function test_bpjs_settings_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('bpjs_settings'),
            "Table 'bpjs_settings' does not exist."
        );

        $expectedColumns = ['base_url', 'consumer_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'is_active', 'ppk_code', 'secret_key', 'updated_at', 'updated_by', 'user_key', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('bpjs_settings', $expectedColumns),
            "Table 'bpjs_settings' is missing some expected columns."
        );
    }

    public function test_lookup_types_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('lookup_types'),
            "Table 'lookup_types' does not exist."
        );

        $expectedColumns = ['code', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'description', 'is_active', 'name', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('lookup_types', $expectedColumns),
            "Table 'lookup_types' is missing some expected columns."
        );
    }

    public function test_lookup_sets_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('lookup_sets'),
            "Table 'lookup_sets' does not exist."
        );

        $expectedColumns = ['code', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'description', 'id', 'is_active', 'lookup_set_uuid', 'name', 'program_code', 'source', 'updated_at', 'updated_by', 'user_can_edit_flag', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('lookup_sets', $expectedColumns),
            "Table 'lookup_sets' is missing some expected columns."
        );
    }

    public function test_lookup_values_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('lookup_values'),
            "Table 'lookup_values' does not exist."
        );

        $expectedColumns = ['active_flag', 'code', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'description', 'diskon_pilih', 'display_name', 'end_date', 'id', 'is_active', 'is_default', 'lookup_set_uuid', 'lookup_value_uuid', 'max_stock', 'order_priority', 'persen1', 'persen2', 'price_up_beli', 'primary_flag', 'program_code', 'seq', 'start_date', 'updated_at', 'updated_by', 'uuid', 'value'];

        $this->assertTrue(
            Schema::hasColumns('lookup_values', $expectedColumns),
            "Table 'lookup_values' is missing some expected columns."
        );
    }

    public function test_lookup_value_details_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('lookup_value_details'),
            "Table 'lookup_value_details' does not exist."
        );

        $expectedColumns = ['company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'idx', 'lookup_value_uuid', 'updated_at', 'updated_by', 'uuid', 'value'];

        $this->assertTrue(
            Schema::hasColumns('lookup_value_details', $expectedColumns),
            "Table 'lookup_value_details' is missing some expected columns."
        );
    }

    public function test_temp_lovs_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('temp_lovs'),
            "Table 'temp_lovs' does not exist."
        );

        $expectedColumns = ['active_flag', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'description', 'diskon_pilih', 'display_name', 'lookup_set_uuid', 'lookup_value_uuid', 'persen1', 'persen2', 'price_up_beli', 'program_code', 'seq', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('temp_lovs', $expectedColumns),
            "Table 'temp_lovs' is missing some expected columns."
        );
    }

    public function test_temp_lov_details_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('temp_lov_details'),
            "Table 'temp_lov_details' does not exist."
        );

        $expectedColumns = ['created_at', 'created_by', 'deleted_at', 'deleted_by', 'idx', 'temp_lov_uuid', 'updated_at', 'updated_by', 'uuid', 'value'];

        $this->assertTrue(
            Schema::hasColumns('temp_lov_details', $expectedColumns),
            "Table 'temp_lov_details' is missing some expected columns."
        );
    }

    public function test_locations_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('locations'),
            "Table 'locations' does not exist."
        );

        $expectedColumns = ['address_uuid', 'branch_uuid', 'can_sell', 'code', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'description', 'is_active', 'is_primary', 'name', 'type', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('locations', $expectedColumns),
            "Table 'locations' is missing some expected columns."
        );
    }

    public function test_units_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('units'),
            "Table 'units' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'code', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'description', 'id', 'is_active', 'is_base_global', 'is_primary', 'name', 'symbol', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('units', $expectedColumns),
            "Table 'units' is missing some expected columns."
        );
    }

    public function test_price_levels_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('price_levels'),
            "Table 'price_levels' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'code', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'description', 'id', 'is_active', 'name', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('price_levels', $expectedColumns),
            "Table 'price_levels' is missing some expected columns."
        );
    }

    public function test_item_prices_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('item_prices'),
            "Table 'item_prices' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'ended_at', 'id', 'is_active', 'item_uuid', 'price', 'price_level_uuid', 'started_at', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('item_prices', $expectedColumns),
            "Table 'item_prices' is missing some expected columns."
        );
    }

    public function test_item_units_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('item_units'),
            "Table 'item_units' does not exist."
        );

        $expectedColumns = ['barcode', 'branch_uuid', 'company_uuid', 'conversion_ratio', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'id', 'is_base', 'is_purchase_unit', 'is_sales_unit', 'item_uuid', 'unit_uuid', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('item_units', $expectedColumns),
            "Table 'item_units' is missing some expected columns."
        );
    }

    public function test_item_accounts_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('item_accounts'),
            "Table 'item_accounts' does not exist."
        );

        $expectedColumns = ['account_role', 'account_uuid', 'branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'id', 'item_uuid', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('item_accounts', $expectedColumns),
            "Table 'item_accounts' is missing some expected columns."
        );
    }

    public function test_item_stocks_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('item_stocks'),
            "Table 'item_stocks' does not exist."
        );

        $expectedColumns = ['available_qty', 'avg_cost', 'branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'id', 'item_uuid', 'last_movement_at', 'location_uuid', 'on_hand_qty', 'reserved_qty', 'source_id', 'source_type', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('item_stocks', $expectedColumns),
            "Table 'item_stocks' is missing some expected columns."
        );
    }

    public function test_item_opening_balances_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('item_opening_balances'),
            "Table 'item_opening_balances' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'id', 'item_uuid', 'location_uuid', 'note', 'opening_qty', 'opening_unit_cost', 'period_month', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('item_opening_balances', $expectedColumns),
            "Table 'item_opening_balances' is missing some expected columns."
        );
    }

    public function test_item_details_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('item_details'),
            "Table 'item_details' does not exist."
        );

        $expectedColumns = ['batch_no', 'branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'expired_at', 'id', 'item_uuid', 'location_uuid', 'manufactured_at', 'note', 'serial_no', 'status', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('item_details', $expectedColumns),
            "Table 'item_details' is missing some expected columns."
        );
    }

    public function test_item_discount_rules_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('item_discount_rules'),
            "Table 'item_discount_rules' does not exist."
        );

        $expectedColumns = ['amount', 'branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'discount_type', 'end_at', 'id', 'is_active', 'item_uuid', 'max_qty', 'min_qty', 'note', 'price_level_uuid', 'start_at', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('item_discount_rules', $expectedColumns),
            "Table 'item_discount_rules' is missing some expected columns."
        );
    }

    public function test_item_conversions_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('item_conversions'),
            "Table 'item_conversions' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'conversion_ratio', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'from_unit_uuid', 'id', 'is_active', 'item_uuid', 'to_unit_uuid', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('item_conversions', $expectedColumns),
            "Table 'item_conversions' is missing some expected columns."
        );
    }

    public function test_user_warehouses_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('user_warehouses'),
            "Table 'user_warehouses' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'is_primary', 'location_uuid', 'updated_at', 'updated_by', 'user_uuid', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('user_warehouses', $expectedColumns),
            "Table 'user_warehouses' is missing some expected columns."
        );
    }

    public function test_person_balances_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('person_balances'),
            "Table 'person_balances' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'closing_balance', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'location_uuid', 'opening_balance', 'period_month', 'period_name', 'person_uuid', 'saldo_awal', 'saldo_hutang', 'total_invoice', 'total_payment', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('person_balances', $expectedColumns),
            "Table 'person_balances' is missing some expected columns."
        );
    }

    public function test_opening_balance_records_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('opening_balance_records'),
            "Table 'opening_balance_records' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'is_posted', 'note', 'period_month', 'transaction_date', 'transaction_no', 'type', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('opening_balance_records', $expectedColumns),
            "Table 'opening_balance_records' is missing some expected columns."
        );
    }

    public function test_item_balances_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('item_balances'),
            "Table 'item_balances' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'hpp', 'id', 'item_uuid', 'location_uuid', 'note', 'period_name', 'qty_awal', 'qty_keluar', 'qty_masuk', 'stock_date', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('item_balances', $expectedColumns),
            "Table 'item_balances' is missing some expected columns."
        );
    }

    public function test_person_saldo_details_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('person_saldo_details'),
            "Table 'person_saldo_details' does not exist."
        );

        $expectedColumns = ['balance', 'branch_uuid', 'company_uuid', 'created_at', 'created_by', 'currency_uuid', 'deleted_at', 'deleted_by', 'document_date', 'document_no', 'due_date', 'id', 'location_uuid', 'mata_uang_uuid', 'note', 'period_name', 'person_uuid', 'rate_acc', 'rate_tax', 'total_amount', 'total_paid', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('person_saldo_details', $expectedColumns),
            "Table 'person_saldo_details' is missing some expected columns."
        );
    }

    public function test_item_uoms_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('item_uoms'),
            "Table 'item_uoms' does not exist."
        );

        $expectedColumns = ['barcode', 'branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'id', 'is_base_uom', 'item_uuid', 'legacy_item_uuid', 'to_qty', 'uom_code', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('item_uoms', $expectedColumns),
            "Table 'item_uoms' is missing some expected columns."
        );
    }

    public function test_payment_terms_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('payment_terms'),
            "Table 'payment_terms' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'description', 'due_days', 'id', 'is_active', 'is_primary', 'legacy_id', 'name', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('payment_terms', $expectedColumns),
            "Table 'payment_terms' is missing some expected columns."
        );
    }

    public function test_taxes_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('taxes'),
            "Table 'taxes' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'id', 'is_active', 'is_pph', 'is_primary', 'legacy_purchase_account_uuid', 'legacy_sale_account_uuid', 'legacy_tax_type_uuid', 'percentage', 'tax_code', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('taxes', $expectedColumns),
            "Table 'taxes' is missing some expected columns."
        );
    }

    public function test_people_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('people'),
            "Table 'people' does not exist."
        );

        $expectedColumns = ['address', 'alternate_name', 'branch_uuid', 'code', 'company_type_uuid', 'company_uuid', 'created_at', 'created_by', 'created_by_legacy', 'created_date', 'currency_uuid', 'customer_type_uuid', 'deleted_at', 'deleted_by', 'description', 'discount_amount', 'discount_percentage', 'email', 'employee_uuid', 'is_active', 'is_approved', 'is_cash', 'is_customer', 'is_supplier', 'last_update_by', 'last_update_date', 'legacy_id', 'location_uuid', 'loyalty_points', 'name', 'note', 'npwp_no', 'opening_balance', 'payable_account_uuid', 'payable_limit', 'payment_term_uuid', 'phone', 'price_level_uuid', 'receivable_account_uuid', 'receivable_limit', 'region', 'salesman_uuid', 'tax_code', 'type', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('people', $expectedColumns),
            "Table 'people' is missing some expected columns."
        );
    }

    public function test_person_custom_fields_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('person_custom_fields'),
            "Table 'person_custom_fields' does not exist."
        );

        $expectedColumns = ['created_at', 'created_by', 'deleted_at', 'deleted_by', 'field_key', 'field_type', 'field_value', 'person_uuid', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('person_custom_fields', $expectedColumns),
            "Table 'person_custom_fields' is missing some expected columns."
        );
    }

    public function test_person_addresses_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('person_addresses'),
            "Table 'person_addresses' does not exist."
        );

        $expectedColumns = ['address1', 'address2', 'city', 'country', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'is_primary', 'person_uuid', 'phone', 'postal_code', 'province', 'type', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('person_addresses', $expectedColumns),
            "Table 'person_addresses' is missing some expected columns."
        );
    }

    public function test_person_contacts_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('person_contacts'),
            "Table 'person_contacts' does not exist."
        );

        $expectedColumns = ['created_at', 'created_by', 'deleted_at', 'deleted_by', 'email', 'name', 'person_uuid', 'phone', 'position', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('person_contacts', $expectedColumns),
            "Table 'person_contacts' is missing some expected columns."
        );
    }

    public function test_item_suppliers_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('item_suppliers'),
            "Table 'item_suppliers' does not exist."
        );

        $expectedColumns = ['created_at', 'created_by', 'deleted_at', 'deleted_by', 'is_primary', 'item_uuid', 'purchase_price', 'supplier_item_code', 'supplier_uuid', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('item_suppliers', $expectedColumns),
            "Table 'item_suppliers' is missing some expected columns."
        );
    }

    public function test_item_stok_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('item_stok'),
            "Table 'item_stok' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'default_flag', 'deleted_at', 'deleted_by', 'hpp', 'id', 'item_uuid', 'location_uuid', 'qty_awal', 'qty_keluar', 'qty_masuk', 'stock_awal', 'stock_date', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('item_stok', $expectedColumns),
            "Table 'item_stok' is missing some expected columns."
        );
    }

    public function test_sales_orders_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('sales_orders'),
            "Table 'sales_orders' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'description', 'id', 'legacy_id', 'location_uuid', 'payment_term_uuid', 'person_uuid', 'sales_order_date', 'sales_order_no', 'status', 'total_amount', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('sales_orders', $expectedColumns),
            "Table 'sales_orders' is missing some expected columns."
        );
    }

    public function test_sales_order_details_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('sales_order_details'),
            "Table 'sales_order_details' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'description', 'discount', 'item_uuid', 'price', 'qty', 'sales_order_uuid', 'total', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('sales_order_details', $expectedColumns),
            "Table 'sales_order_details' is missing some expected columns."
        );
    }

    public function test_delivery_orders_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('delivery_orders'),
            "Table 'delivery_orders' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'delivery_date', 'delivery_no', 'description', 'id', 'legacy_id', 'location_uuid', 'person_uuid', 'sales_order_uuid', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('delivery_orders', $expectedColumns),
            "Table 'delivery_orders' is missing some expected columns."
        );
    }

    public function test_delivery_order_details_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('delivery_order_details'),
            "Table 'delivery_order_details' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'delivery_order_uuid', 'description', 'item_uuid', 'qty', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('delivery_order_details', $expectedColumns),
            "Table 'delivery_order_details' is missing some expected columns."
        );
    }

    public function test_sales_invoices_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('sales_invoices'),
            "Table 'sales_invoices' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'delivery_order_uuid', 'id', 'invoice_date', 'invoice_no', 'legacy_id', 'location_uuid', 'payment_account_1_uuid', 'payment_account_2_uuid', 'payment_term_uuid', 'person_uuid', 'sales_order_uuid', 'status', 'total_amount', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('sales_invoices', $expectedColumns),
            "Table 'sales_invoices' is missing some expected columns."
        );
    }

    public function test_sales_invoice_details_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('sales_invoice_details'),
            "Table 'sales_invoice_details' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'discount', 'hpp', 'income_account_uuid', 'item_uuid', 'price', 'qty', 'sales_invoice_uuid', 'total', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('sales_invoice_details', $expectedColumns),
            "Table 'sales_invoice_details' is missing some expected columns."
        );
    }

    public function test_sales_invoice_agings_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('sales_invoice_agings'),
            "Table 'sales_invoice_agings' does not exist."
        );

        $expectedColumns = ['amount', 'branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'due_date', 'remaining', 'sales_invoice_uuid', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('sales_invoice_agings', $expectedColumns),
            "Table 'sales_invoice_agings' is missing some expected columns."
        );
    }

    public function test_coas_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('coas'),
            "Table 'coas' does not exist."
        );

        $expectedColumns = ['account_uuid', 'branch_uuid', 'code', 'company_uuid', 'cost_center_uuid', 'created_at', 'created_by', 'created_by_legacy', 'deleted_at', 'deleted_by', 'end_date', 'id', 'is_active', 'name', 'start_date', 'sub_account_uuid', 'updated_at', 'updated_by', 'updated_by_legacy', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('coas', $expectedColumns),
            "Table 'coas' is missing some expected columns."
        );
    }

    public function test_assets_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('assets'),
            "Table 'assets' does not exist."
        );

        $expectedColumns = ['asset_value', 'branch_uuid', 'buy_date', 'coa_debet_uuid', 'coa_kredit_uuid', 'coa_uuid', 'code', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'department_uuid', 'depreciation_method_uuid', 'id', 'is_active', 'is_intangible', 'life_year', 'name', 'note', 'period_name', 'quantity', 'rate', 'susut_year', 'type_uuid', 'updated_at', 'updated_by', 'use_date', 'uuid', 'yearly_depreciation'];

        $this->assertTrue(
            Schema::hasColumns('assets', $expectedColumns),
            "Table 'assets' is missing some expected columns."
        );
    }

    public function test_coa_setups_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('coa_setups'),
            "Table 'coa_setups' does not exist."
        );

        $expectedColumns = ['account_uuid', 'branch_uuid', 'coa_uuid', 'company_uuid', 'created_at', 'created_by', 'created_by_legacy', 'deleted_at', 'deleted_by', 'note', 'program_account', 'updated_at', 'updated_by', 'updated_by_legacy'];

        $this->assertTrue(
            Schema::hasColumns('coa_setups', $expectedColumns),
            "Table 'coa_setups' is missing some expected columns."
        );
    }

    public function test_boms_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('boms'),
            "Table 'boms' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'document_no', 'end_date', 'id', 'is_active', 'is_primary', 'item_uuid', 'note', 'quantity', 'reference_no', 'start_date', 'unit_uuid', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('boms', $expectedColumns),
            "Table 'boms' is missing some expected columns."
        );
    }

    public function test_bom_details_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('bom_details'),
            "Table 'bom_details' does not exist."
        );

        $expectedColumns = ['bom_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'item_uuid', 'note', 'quantity', 'ratio', 'unit_uuid', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('bom_details', $expectedColumns),
            "Table 'bom_details' is missing some expected columns."
        );
    }

    public function test_bom_costs_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('bom_costs'),
            "Table 'bom_costs' does not exist."
        );

        $expectedColumns = ['amount', 'bom_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'item_uuid', 'note', 'total', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('bom_costs', $expectedColumns),
            "Table 'bom_costs' is missing some expected columns."
        );
    }

    public function test_assemblies_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('assemblies'),
            "Table 'assemblies' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'cost', 'created_at', 'created_by', 'date', 'deleted_at', 'deleted_by', 'document_no', 'item_uuid', 'location_uuid', 'notes', 'quantity', 'status', 'unit_uuid', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('assemblies', $expectedColumns),
            "Table 'assemblies' is missing some expected columns."
        );
    }

    public function test_assembly_items_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('assembly_items'),
            "Table 'assembly_items' does not exist."
        );

        $expectedColumns = ['assembly_uuid', 'branch_uuid', 'company_uuid', 'cost', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'item_uuid', 'quantity', 'subtotal', 'unit_uuid', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('assembly_items', $expectedColumns),
            "Table 'assembly_items' is missing some expected columns."
        );
    }

    public function test_stock_transfers_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('stock_transfers'),
            "Table 'stock_transfers' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'date', 'deleted_at', 'deleted_by', 'destination_location_uuid', 'no_pag', 'notes', 'source_location_uuid', 'status', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('stock_transfers', $expectedColumns),
            "Table 'stock_transfers' is missing some expected columns."
        );
    }

    public function test_stock_transfer_items_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('stock_transfer_items'),
            "Table 'stock_transfer_items' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'note', 'product_uuid', 'qty', 'transfer_uuid', 'unit_uuid', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('stock_transfer_items', $expectedColumns),
            "Table 'stock_transfer_items' is missing some expected columns."
        );
    }

    public function test_ingredients_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('ingredients'),
            "Table 'ingredients' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'name', 'unit_uuid', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('ingredients', $expectedColumns),
            "Table 'ingredients' is missing some expected columns."
        );
    }

    public function test_stocks_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('stocks'),
            "Table 'stocks' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'difference_stock', 'first_stock', 'ingredient_uuid', 'last_stock', 'stock_close_input', 'stock_in', 'stock_used', 'updated_at', 'updated_by'];

        $this->assertTrue(
            Schema::hasColumns('stocks', $expectedColumns),
            "Table 'stocks' is missing some expected columns."
        );
    }

    public function test_stock_adjustments_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('stock_adjustments'),
            "Table 'stock_adjustments' does not exist."
        );

        $expectedColumns = ['account_uuid', 'adjustment_no', 'branch_uuid', 'company_uuid', 'created_at', 'created_by', 'date', 'deleted_at', 'deleted_by', 'is_value_adjustment', 'location_uuid', 'notes', 'reference_no', 'status', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('stock_adjustments', $expectedColumns),
            "Table 'stock_adjustments' is missing some expected columns."
        );
    }

    public function test_stock_adjustment_items_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('stock_adjustment_items'),
            "Table 'stock_adjustment_items' does not exist."
        );

        $expectedColumns = ['actual_qty', 'adjustment_qty', 'adjustment_uuid', 'book_qty', 'branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'item_uuid', 'notes', 'price', 'reason', 'unit_uuid', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('stock_adjustment_items', $expectedColumns),
            "Table 'stock_adjustment_items' is missing some expected columns."
        );
    }

    public function test_shifts_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('shifts'),
            "Table 'shifts' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'date', 'deleted_at', 'deleted_by', 'is_closed', 'location_uuid', 'opening_balance', 'shift_number', 'start_time', 'updated_at', 'updated_by', 'user_uuid'];

        $this->assertTrue(
            Schema::hasColumns('shifts', $expectedColumns),
            "Table 'shifts' is missing some expected columns."
        );
    }

    public function test_transactions_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('transactions'),
            "Table 'transactions' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'category_order', 'change_money', 'company_uuid', 'created_at', 'created_by', 'date', 'deleted_at', 'deleted_by', 'location_uuid', 'notes', 'order_type_uuid', 'payment_method', 'sequence_number', 'shift_uuid', 'status', 'total_amount', 'total_qty', 'updated_at', 'updated_by', 'user_uuid'];

        $this->assertTrue(
            Schema::hasColumns('transactions', $expectedColumns),
            "Table 'transactions' is missing some expected columns."
        );
    }

    public function test_transaction_details_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('transaction_details'),
            "Table 'transaction_details' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'product_name', 'product_price', 'product_uuid', 'qty', 'subtotal', 'transaction_uuid', 'updated_at', 'updated_by'];

        $this->assertTrue(
            Schema::hasColumns('transaction_details', $expectedColumns),
            "Table 'transaction_details' is missing some expected columns."
        );
    }

    public function test_purchase_orders_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('purchase_orders'),
            "Table 'purchase_orders' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'date', 'deleted_at', 'deleted_by', 'discount_amount', 'discount_percent', 'document_no', 'grand_total', 'location_uuid', 'notes', 'reference_no', 'status', 'supplier_uuid', 'tax_amount', 'tax_percent', 'total', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('purchase_orders', $expectedColumns),
            "Table 'purchase_orders' is missing some expected columns."
        );
    }

    public function test_purchase_order_items_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('purchase_order_items'),
            "Table 'purchase_order_items' does not exist."
        );

        $expectedColumns = ['branch_uuid', 'company_uuid', 'created_at', 'created_by', 'deleted_at', 'deleted_by', 'discount', 'notes', 'price', 'product_uuid', 'purchase_order_uuid', 'quantity', 'subtotal', 'unit_uuid', 'updated_at', 'updated_by', 'uuid'];

        $this->assertTrue(
            Schema::hasColumns('purchase_order_items', $expectedColumns),
            "Table 'purchase_order_items' is missing some expected columns."
        );
    }

    public function test_purchase_return_payments_table_exists_and_has_expected_columns()
    {
        $this->assertTrue(
            Schema::hasTable('purchase_return_payments'),
            "Table 'purchase_return_payments' does not exist."
        );

        $expectedColumns = ['account_uuid', 'amount', 'branch_uuid', 'cheque_no', 'company_uuid', 'created_at', 'created_by', 'date', 'deleted_at', 'deleted_by', 'discount', 'notes', 'purchase_return_uuid', 'reference_no', 'updated_at', 'updated_by', 'uuid', 'voucher_no'];

        $this->assertTrue(
            Schema::hasColumns('purchase_return_payments', $expectedColumns),
            "Table 'purchase_return_payments' is missing some expected columns."
        );
    }
}
