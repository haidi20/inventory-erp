<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        try {
            // Tabel akun akuntansi
            if (!Schema::hasTable('accounts')) {
                Schema::create('accounts', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Penjelasan kolom uuid.');
                    $table->bigInteger('id')->nullable()->comment('Penjelasan kolom id.');
                    $table->string('code', 50)->comment('Penjelasan kolom code.');
                    $table->string('name', 255)->comment('Penjelasan kolom name.');
                    $table->string('type', 50)->comment('Penjelasan kolom type.');
                    $table->char('parent_uuid', 36)->nullable()->comment('Penjelasan kolom parent_uuid.');
                    $table->boolean('is_header')->nullable()->default(false)->comment('Penjelasan kolom is_header.');
                    $table->boolean('is_report_balance_sheet')->nullable()->default(false)->comment('Penjelasan kolom is_report_balance_sheet.');
                    $table->char('normal_status', 1)->nullable()->comment('Penjelasan kolom normal_status.');
                    $table->integer('account_type_uuid')->nullable()->comment('Penjelasan kolom account_type_uuid.');
                    $table->boolean('is_primary')->nullable()->default(false)->comment('Penjelasan kolom is_primary.');
                    $table->boolean('is_cash_bank')->nullable()->default(false)->comment('Penjelasan kolom is_cash_bank.');
                    $table->integer('currency_uuid')->nullable()->comment('Penjelasan kolom currency_uuid.');
                    $table->decimal('exchange_rate', 18, 4)->nullable()->default(1)->comment('Penjelasan kolom exchange_rate.');
                    $table->text('note')->nullable()->comment('Penjelasan kolom note.');
                    $table->boolean('is_active')->nullable()->default(true)->comment('Penjelasan kolom is_active.');
                    $table->integer('created_by_legacy')->nullable()->comment('Penjelasan kolom created_by_legacy.');
                    $table->integer('updated_by_legacy')->nullable()->comment('Penjelasan kolom updated_by_legacy.');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->char('branch_uuid', 36)->comment('Penjelasan kolom branch_uuid.');
                    $table->char('created_by', 36)->nullable()->comment('Penjelasan kolom created_by.');
                    $table->char('updated_by', 36)->nullable()->comment('Penjelasan kolom updated_by.');
                    $table->char('deleted_by', 36)->nullable()->comment('Penjelasan kolom deleted_by.');
                    $table->bigInteger('created_at')->nullable()->comment('Penjelasan kolom created_at.');
                    $table->bigInteger('updated_at')->nullable()->comment('Penjelasan kolom updated_at.');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Penjelasan kolom deleted_at.');
                    
                    $table->unique(['branch_uuid', 'code', 'deleted_at'], 'idx_accounts_code_unique_soft');
                    $table->index('branch_uuid', 'idx_accounts_branch_uuid');
                    $table->index('company_uuid', 'idx_accounts_company_uuid');
                    $table->index('deleted_at', 'idx_accounts_deleted_at');
                });
            }
            // Tabel chart of accounts
            if (!Schema::hasTable('coas')) {
                Schema::create('coas', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Penjelasan kolom uuid.');
                    $table->bigInteger('id')->nullable()->comment('Penjelasan kolom id.');
                    $table->string('code', 50)->comment('Penjelasan kolom code.');
                    $table->string('name', 255)->comment('Penjelasan kolom name.');
                    $table->char('account_uuid', 36)->comment('Penjelasan kolom account_uuid.');
                    $table->integer('cost_center_uuid')->nullable()->comment('Penjelasan kolom cost_center_uuid.');
                    $table->integer('sub_account_uuid')->nullable()->comment('Penjelasan kolom sub_account_uuid.');
                    $table->bigInteger('start_date')->nullable()->comment('Penjelasan kolom start_date.');
                    $table->bigInteger('end_date')->nullable()->comment('Penjelasan kolom end_date.');
                    $table->boolean('is_active')->nullable()->default(true)->comment('Penjelasan kolom is_active.');
                    $table->integer('created_by_legacy')->nullable()->comment('Penjelasan kolom created_by_legacy.');
                    $table->integer('updated_by_legacy')->nullable()->comment('Penjelasan kolom updated_by_legacy.');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->char('branch_uuid', 36)->comment('Penjelasan kolom branch_uuid.');
                    $table->char('created_by', 36)->nullable()->comment('Penjelasan kolom created_by.');
                    $table->char('updated_by', 36)->nullable()->comment('Penjelasan kolom updated_by.');
                    $table->char('deleted_by', 36)->nullable()->comment('Penjelasan kolom deleted_by.');
                    $table->bigInteger('created_at')->nullable()->comment('Penjelasan kolom created_at.');
                    $table->bigInteger('updated_at')->nullable()->comment('Penjelasan kolom updated_at.');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Penjelasan kolom deleted_at.');
                    $table->index('branch_uuid', 'idx_coas_branch_uuid');
                    $table->index('company_uuid', 'idx_coas_company_uuid');
                    $table->index('deleted_at', 'idx_coas_deleted_at');
                });
            }
            // Tabel aset tetap
            if (!Schema::hasTable('assets')) {
                Schema::create('assets', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Penjelasan kolom uuid.');
                    $table->bigInteger('id')->nullable()->comment('Penjelasan kolom id.');
                    $table->char('branch_uuid', 36)->comment('Penjelasan kolom branch_uuid.');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->string('code', 50)->comment('Penjelasan kolom code.');
                    $table->string('name', 255)->comment('Penjelasan kolom name.');
                    $table->decimal('quantity', 17, 5)->nullable()->default(1)->comment('Penjelasan kolom quantity.');
                    $table->integer('type_uuid')->nullable()->comment('Penjelasan kolom type_uuid.');
                    $table->decimal('asset_value', 20, 5)->nullable()->default(0)->comment('Penjelasan kolom asset_value.');
                    $table->bigInteger('buy_date')->nullable()->comment('Penjelasan kolom buy_date.');
                    $table->bigInteger('use_date')->nullable()->comment('Penjelasan kolom use_date.');
                    $table->integer('depreciation_method_uuid')->nullable()->comment('Penjelasan kolom depreciation_method_uuid.');
                    $table->decimal('life_year', 7, 2)->nullable()->comment('Penjelasan kolom life_year.');
                    $table->decimal('rate', 17, 5)->nullable()->default(0)->comment('Penjelasan kolom rate.');
                    $table->integer('department_uuid')->nullable()->comment('Penjelasan kolom department_uuid.');
                    $table->integer('period_name')->nullable()->comment('Penjelasan kolom period_name.');
                    $table->char('coa_uuid', 36)->nullable()->comment('Penjelasan kolom coa_uuid.');
                    $table->char('coa_kredit_uuid', 36)->nullable()->comment('Penjelasan kolom coa_kredit_uuid.');
                    $table->char('coa_debet_uuid', 36)->nullable()->comment('Penjelasan kolom coa_debet_uuid.');
                    $table->boolean('is_intangible')->nullable()->default(false)->comment('Penjelasan kolom is_intangible.');
                    $table->boolean('is_active')->nullable()->default(true)->comment('Penjelasan kolom is_active.');
                    $table->integer('susut_year')->nullable()->comment('Penjelasan kolom susut_year.');
                    $table->decimal('yearly_depreciation', 20, 5)->nullable()->default(0)->comment('Penjelasan kolom yearly_depreciation.');
                    $table->text('note')->nullable()->comment('Penjelasan kolom note.');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Penjelasan kolom deleted_at.');
                    $table->char('created_by', 36)->nullable()->comment('Penjelasan kolom created_by.');
                    $table->char('updated_by', 36)->nullable()->comment('Penjelasan kolom updated_by.');
                    $table->char('deleted_by', 36)->nullable()->comment('Penjelasan kolom deleted_by.');
                    $table->bigInteger('created_at')->nullable()->comment('Penjelasan kolom created_at.');
                    $table->bigInteger('updated_at')->nullable()->comment('Penjelasan kolom updated_at.');
                    $table->index('deleted_at', 'idx_assets_deleted_at');
                });
            }
            // Tabel saldo akun
            if (!Schema::hasTable('account_balances')) {
                Schema::create('account_balances', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Penjelasan kolom uuid.');
                    $table->bigInteger('id')->nullable()->comment('Penjelasan kolom id.');
                    $table->char('branch_uuid', 36)->comment('Penjelasan kolom branch_uuid.');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->char('account_uuid', 36)->comment('Penjelasan kolom account_uuid.');
                    $table->char('coa_uuid', 36)->nullable()->comment('Penjelasan kolom coa_uuid.');
                    $table->string('period_month', 7)->comment('Penjelasan kolom period_month.');
                    $table->integer('period_name')->nullable()->comment('Penjelasan kolom period_name.');
                    $table->bigInteger('balance_date')->nullable()->comment('Penjelasan kolom balance_date.');
                    $table->decimal('opening_debit', 20, 8)->nullable()->default(0)->comment('Penjelasan kolom opening_debit.');
                    $table->decimal('opening_credit', 20, 8)->nullable()->default(0)->comment('Penjelasan kolom opening_credit.');
                    $table->decimal('opening_balance', 20, 8)->nullable()->default(0)->comment('Penjelasan kolom opening_balance.');
                    $table->decimal('total_debit', 20, 8)->nullable()->default(0)->comment('Penjelasan kolom total_debit.');
                    $table->decimal('total_credit', 20, 8)->nullable()->default(0)->comment('Penjelasan kolom total_credit.');
                    $table->decimal('closing_debit', 20, 8)->nullable()->default(0)->comment('Penjelasan kolom closing_debit.');
                    $table->decimal('closing_credit', 20, 8)->nullable()->default(0)->comment('Penjelasan kolom closing_credit.');
                    $table->decimal('closing_balance', 20, 8)->nullable()->default(0)->comment('Penjelasan kolom closing_balance.');
                    $table->decimal('legacy_a_saldo', 20, 5)->nullable()->default(0)->comment('Penjelasan kolom legacy_a_saldo.');
                    $table->decimal('legacy_a_debet', 20, 5)->nullable()->default(0)->comment('Penjelasan kolom legacy_a_debet.');
                    $table->decimal('legacy_a_credit', 20, 5)->nullable()->default(0)->comment('Penjelasan kolom legacy_a_credit.');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Penjelasan kolom deleted_at.');
                    $table->char('created_by', 36)->nullable()->comment('Penjelasan kolom created_by.');
                    $table->char('updated_by', 36)->nullable()->comment('Penjelasan kolom updated_by.');
                    $table->char('deleted_by', 36)->nullable()->comment('Penjelasan kolom deleted_by.');
                    $table->bigInteger('created_at')->nullable()->comment('Penjelasan kolom created_at.');
                    $table->bigInteger('updated_at')->nullable()->comment('Penjelasan kolom updated_at.');
                    $table->index(['branch_uuid', 'account_uuid', 'period_month'], 'idx_branch_account_period');
                    $table->index('deleted_at', 'idx_account_balances_deleted_at');
                });
            }
            // Tabel setup pemetaan COA
            if (!Schema::hasTable('coa_setups')) {
                Schema::create('coa_setups', function (Blueprint $table): void {
                    $table->string('program_account', 50)->primary()->comment('Penjelasan kolom program_account.');
                    $table->char('coa_uuid', 36)->nullable()->comment('Penjelasan kolom coa_uuid.');
                    $table->char('account_uuid', 36)->nullable()->comment('Penjelasan kolom account_uuid.');
                    $table->text('note')->nullable()->comment('Penjelasan kolom note.');
                    $table->integer('created_by_legacy')->nullable()->comment('Penjelasan kolom created_by_legacy.');
                    $table->integer('updated_by_legacy')->nullable()->comment('Penjelasan kolom updated_by_legacy.');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->char('branch_uuid', 36)->comment('Penjelasan kolom branch_uuid.');
                    $table->char('created_by', 36)->nullable()->comment('Penjelasan kolom created_by.');
                    $table->char('updated_by', 36)->nullable()->comment('Penjelasan kolom updated_by.');
                    $table->char('deleted_by', 36)->nullable()->comment('Penjelasan kolom deleted_by.');
                    $table->bigInteger('created_at')->nullable()->comment('Penjelasan kolom created_at.');
                    $table->bigInteger('updated_at')->nullable()->comment('Penjelasan kolom updated_at.');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Penjelasan kolom deleted_at.');
                    $table->index('branch_uuid', 'idx_coa_setups_branch_uuid');
                    $table->index('company_uuid', 'idx_coa_setups_company_uuid');
                    $table->index('deleted_at', 'idx_coa_setups_deleted_at');
                });
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('coa_setups');
            Schema::dropIfExists('account_balances');
            Schema::dropIfExists('assets');
        } catch (\Throwable $e) {
            throw $e;
        }
    }
};


