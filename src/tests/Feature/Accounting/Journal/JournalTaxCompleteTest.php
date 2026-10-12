<?php

namespace Tests\Feature\Accounting\Journal;

use App\Modules\Finance\Journal\Models\AccountMapping;
use App\Modules\Finance\Journal\Repositories\AccountSettingRepository;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use Illuminate\Support\Carbon;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Entities\JournalLineEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Enums\JournalWorkflowStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Modules\Core\Authorization\Models\Role;
use App\Modules\Operational\Master\Models\TaxSetting;
use Tests\TestCase;
use App\Modules\Core\Identity\Models\User;

class JournalTaxCompleteTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $journalService;
    protected AccountSettingRepository $accountSettingRepo;
    protected Branch $branch;
    protected User $user;
    protected array $accounts = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->journalService = app(JournalService::class);
        $this->accountSettingRepo = app(AccountSettingRepository::class);

        // Setup User & Branch
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->branch = Branch::factory()->create(['name' => 'KDM', 'short_name' => 'KDM', 'is_active' => true]);
        $this->user->update(['branch_uuid' => $this->branch->uuid]);

        // Setup Role
        $role = Role::create(['name' => 'Tax Officer', 'is_active' => true]);
        $this->user->roles()->attach($role->uuid);

        // Setup Periods
        ClosingPeriod::create([
            'branch_uuid' => $this->branch->uuid,
            'period' => '2026-03',
            'name' => 'Maret 2026',
            'start_date' => '2026-03-01',
            'end_date' => '2026-03-31',
            'status' => 'open'
        ]);

        // Setup Accounts
        $this->accounts['cash'] = Account::create(['code' => '101', 'name' => 'Kas', 'type' => 'asset', 'type_saldo' => 'debit']);
        $this->accounts['revenue'] = Account::create(['code' => '401', 'name' => 'Pendapatan', 'type' => 'revenue', 'type_saldo' => 'credit']);
        $this->accounts['expense'] = Account::create(['code' => '501', 'name' => 'Biaya', 'type' => 'expense', 'type_saldo' => 'debit']);
        
        // Tax Accounts
        $this->accounts['ppn_output'] = Account::create(['code' => '2106', 'name' => 'PPN Keluaran', 'type' => 'liability', 'type_saldo' => 'credit']);
        $this->accounts['ppn_input'] = Account::create(['code' => '1108', 'name' => 'PPN Masukan', 'type' => 'asset', 'type_saldo' => 'debit']);
        $this->accounts['pph23_payable'] = Account::create(['code' => '2107', 'name' => 'Hutang PPh 23', 'type' => 'liability', 'type_saldo' => 'credit']);
        $this->accounts['pph42_payable'] = Account::create(['code' => '2108', 'name' => 'Hutang PPh 4(2)', 'type' => 'liability', 'type_saldo' => 'credit']);

        // Setup Tax Settings
        TaxSetting::create(['name' => 'PPN', 'percentage' => 11.00, 'account_uuid' => $this->accounts['ppn_output']->uuid, 'is_active' => true]);
        TaxSetting::create(['name' => 'PPN Masukan', 'percentage' => 11.00, 'account_uuid' => $this->accounts['ppn_input']->uuid, 'is_active' => true]);
        TaxSetting::create(['name' => 'PPh 23', 'percentage' => 2.00, 'account_uuid' => $this->accounts['pph23_payable']->uuid, 'is_active' => true]);
        TaxSetting::create(['name' => 'PPh 4(2)', 'percentage' => 10.00, 'account_uuid' => $this->accounts['pph42_payable']->uuid, 'is_active' => true]);

        // Setup Account Mappings (Required by JournalService validation)
        AccountMapping::create(['key' => 'penjualan.default', 'account_uuid' => $this->accounts['revenue']->uuid]);
        AccountMapping::create(['key' => 'pembelian.default', 'account_uuid' => $this->accounts['expense']->uuid]);
        AccountMapping::create(['key' => 'biaya.default', 'account_uuid' => $this->accounts['expense']->uuid]);
        AccountMapping::create(['key' => 'pendaftaran.default', 'account_uuid' => $this->accounts['revenue']->uuid]);
    }

    /**
     * Skenario 1: PPN Masukan (Purchase VAT)
     */
    public function test_purchase_vat_input_calculation()
    {
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'PURC-TAX-001',
            'journal_date' => '2026-03-05',
            'reference_type' => JournalReferenceType::PURCHASE,
            'lines' => [
                ['account_uuid' => $this->accounts['expense']->uuid, 'debit' => 1000000, 'credit' => 0, 'taxable' => true, 'tax_type' => 'ppn_input', 'tax_rate' => 11.00],
                ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 0, 'credit' => 1110000],
            ]
        ]);

        $journal = $this->journalService->createJournal($dto);
        $journal->load('lines');

        $this->assertCount(3, $journal->lines);
        $taxLine = $journal->lines->firstWhere('account_uuid', $this->accounts['ppn_input']->uuid);
        $this->assertNotNull($taxLine);
        $this->assertEquals(110000, (float)$taxLine->debit);
    }

    /**
     * Skenario 2: PPh 23 (Income Tax - Vendor with NPWP)
     */
    public function test_pph_23_calculation()
    {
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'PPH23-001',
            'journal_date' => '2026-03-10',
            'reference_type' => JournalReferenceType::EXPENSE,
            'lines' => [
                ['account_uuid' => $this->accounts['expense']->uuid, 'debit' => 1000000, 'credit' => 0, 'taxable' => true, 'tax_type' => 'pph_23', 'tax_rate' => 2.00, 'contact_uuid' => 999], // 2% with NPWP
                ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 0, 'credit' => 980000],
            ]
        ]);

        $journal = $this->journalService->createJournal($dto);
        $journal->load('lines');

        $this->assertCount(3, $journal->lines);
        $taxLine = $journal->lines->firstWhere('account_uuid', $this->accounts['pph23_payable']->uuid);
        $this->assertNotNull($taxLine);
        $this->assertEquals(20000, (float)$taxLine->credit);
    }

    /**
     * Skenario 3: PPh 4 Ayat 2 (Final Tax on Rent)
     */
    public function test_pph_4_2_final_tax_calculation()
    {
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'PPH42-001',
            'journal_date' => '2026-03-15',
            'reference_type' => JournalReferenceType::EXPENSE,
            'lines' => [
                ['account_uuid' => $this->accounts['expense']->uuid, 'debit' => 10000000, 'credit' => 0, 'taxable' => true, 'tax_type' => 'pph_4(2)', 'tax_rate' => 10.00, 'contact_uuid' => 999],
                ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 0, 'credit' => 9000000],
            ]
        ]);

        $journal = $this->journalService->createJournal($dto);
        $journal->load('lines');

        $this->assertCount(3, $journal->lines);
        $taxLine = $journal->lines->firstWhere('account_uuid', $this->accounts['pph42_payable']->uuid);
        $this->assertNotNull($taxLine);
        $this->assertEquals(1000000, (float)$taxLine->credit);
    }

    /**
     * Skenario 4: Tax Exemption (SKB handling)
     */
    public function test_tax_exemption_skb()
    {
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'SKB-001',
            'journal_date' => '2026-03-20',
            'reference_type' => JournalReferenceType::SALE,
            'lines' => [
                ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 1000000, 'credit' => 0],
                ['account_uuid' => $this->accounts['revenue']->uuid, 'debit' => 0, 'credit' => 1000000, 'taxable' => true, 'tax_rate' => 0.00], // exempt
            ]
        ]);

        $journal = $this->journalService->createJournal($dto);
        $journal->load('lines');

        // Should NOT create tax line because rate is 0
        $this->assertCount(2, $journal->lines);
    }

    /**
     * Skenario 5: Koreksi Fiskal tagging
     */
    public function test_fiscal_correction_tagging()
    {
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'FISCAL-001',
            'journal_date' => '2026-03-22',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->accounts['expense']->uuid, 'debit' => 500000, 'credit' => 0, 'is_fiscal' => true, 'fiscal_type' => 'positive'],
                ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 0, 'credit' => 500000],
            ]
        ]);

        $journal = $this->journalService->createJournal($dto);
        $journal->load('lines');

        $line = $journal->lines->firstWhere('account_uuid', $this->accounts['expense']->uuid);
        $this->assertTrue((bool)$line->is_fiscal);
        $this->assertEquals('positive', $line->fiscal_type);
    }

    /**
     * Skenario 6: VAT Nilai Lain (Different rate/calculation)
     */
    public function test_vat_nilai_lain_calculation()
    {
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'TAX-NL-001',
            'journal_date' => '2026-03-25',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->accounts['revenue']->uuid, 'debit' => 0, 'credit' => 1000000, 'taxable' => true, 'tax_rate' => 1.1], // PPN 1.1% (Nilai Lain)
                ['account_uuid' => $this->accounts['cash']->uuid, 'debit' => 1011000, 'credit' => 0],
            ]
        ]);

        $journal = $this->journalService->createJournal($dto);
        $journal->load('lines');

        $taxLine = $journal->lines->firstWhere('account_uuid', $this->accounts['ppn_output']->uuid);
        $this->assertEquals(11000, (float)$taxLine->credit);
    }
}
