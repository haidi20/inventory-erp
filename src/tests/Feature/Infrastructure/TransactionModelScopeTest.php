<?php

namespace Tests\Feature\Infrastructure;

use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Identity\Models\UserBranch;
use App\Modules\Core\Identity\Models\UserCompany;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Clinical\Ancillary\Models\LabOrder;
use App\Modules\Clinical\Ancillary\Models\RadiologyOrder;
use App\Modules\Clinical\Pharmacy\Models\Dispensing;
use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Finance\Journal\Models\JournalLine;
use App\Modules\Finance\Ledger\Models\GeneralLedger;
use App\Modules\Finance\Ledger\Models\SalesInvoice;
use App\Modules\Finance\Ledger\Models\Transaction;
use App\Modules\Operational\Inventory\Models\Stock;
use App\Modules\Operational\Inventory\Models\StockMovement;
use App\Modules\Operational\Master\Models\Location;
use App\Modules\Operational\Master\Models\Person;
use App\Modules\Operational\Master\Models\Supplier;
use App\Modules\Operational\Procurement\Models\Purchase;
use App\Modules\Operational\Procurement\Models\PurchaseOrder;
use App\Modules\Operational\Sales\Models\DeliveryOrder;
use App\Modules\Operational\Sales\Models\SalesOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * TDD test untuk Section 30.6 iterasi 1 (model transaksi prioritas tinggi).
 *
 * Konsep: trait HasCompanyScope + HasBranchScope harus terpasang pada setiap
 * model transaksi sehingga global scope CompanyScope/BranchScope otomatis
 * memfilter query berdasarkan konteks user yang login. Isolasi data antar
 * company/branch TIDAK boleh bergantung pada filter manual di Repository.
 */
class TransactionModelScopeTest extends TestCase
{
    use RefreshDatabase;

    private Company $companyA;
    private Company $companyB;
    private Branch $branchA;
    private Branch $branchB;
    private User $user;

    /** Fixture lintas tabel yang dipakai beberapa model (dibuat per company). */
    private array $fixtures = [];

    /** Counter nomor dokumen unik per run test. */
    private int $docSeq = 0;

    private function nextDocNo(string $prefix): string
    {
        return $prefix . '-' . (++$this->docSeq) . '-' . uniqid();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyA = Company::factory()->create(['deleted_at' => 0]);
        $this->companyB = Company::factory()->create(['deleted_at' => 0]);
        $this->branchA = Branch::factory()->create(['company_uuid' => $this->companyA->uuid]);
        $this->branchB = Branch::factory()->create(['company_uuid' => $this->companyB->uuid]);

        $this->user = User::factory()->create();

        UserCompany::query()->create([
            'user_uuid' => $this->user->uuid,
            'company_uuid' => $this->companyA->uuid,
            'is_default' => true,
        ]);
        UserBranch::query()->create([
            'user_uuid' => $this->user->uuid,
            'company_uuid' => $this->companyA->uuid,
            'branch_uuid' => $this->branchA->uuid,
            'is_default' => true,
        ]);
    }

    private function actingAsScopedUser(): void
    {
        $this->actingAs($this->user);
        Auth::shouldUse('web');
    }

    /**
     * Fixture relasi per company: account, supplier, person, location, item.
     * Dipakai untuk memenuhi FK tabel transaksi tanpa mengulang setup per model.
     */
    private function fixturesFor(string $companyUuid, string $branchUuid): array
    {
        if (isset($this->fixtures[$companyUuid])) {
            return $this->fixtures[$companyUuid];
        }

        $account = Account::query()->create([
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
            'code' => 'ACC-' . substr($companyUuid, 0, 8),
            'name' => 'Kas Test',
            'type' => 'asset',
        ]);

        $supplier = Supplier::query()->create([
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
            'name' => 'Supplier Test ' . substr($companyUuid, 0, 8),
        ]);

        $person = Person::query()->create([
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
            'code' => 'PER-' . substr($companyUuid, 0, 8),
            'name' => 'Customer Test',
        ]);

        $location = Location::query()->create([
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
            'code' => 'LOC-' . substr($companyUuid, 0, 8),
            'name' => 'Gudang Test',
        ]);

        $item = \App\Modules\Operational\Master\Models\Product::query()->create([
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
            'code' => 'ITM-' . substr($companyUuid, 0, 8),
            'name' => 'Item Test',
        ]);

        $patient = Patient::query()->create([
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
            'medical_record_no' => 'MR-' . substr($companyUuid, 0, 8) . '-' . (++$this->docSeq),
            'name' => 'Pasien Test',
        ]);

        return $this->fixtures[$companyUuid] = compact('account', 'supplier', 'person', 'location', 'item', 'patient');
    }

    /**
     * Daftar model transaksi iterasi 1. Setiap model harus otomatis
     * terfilter oleh konteks company user yang login.
     */
    public static function transactionModelProvider(): array
    {
        return [
            'Journal (Finance)' => [Journal::class],
            'JournalLine (Finance)' => [JournalLine::class],
            'Transaction POS (Finance\Ledger)' => [Transaction::class],
            'SalesInvoice (Finance\Ledger)' => [SalesInvoice::class],
            'GeneralLedger (Finance\Ledger)' => [GeneralLedger::class],
            'Stock (Inventory)' => [Stock::class],
            'StockMovement (Inventory)' => [StockMovement::class],
            'Purchase (Procurement)' => [Purchase::class],
            'PurchaseOrder (Procurement)' => [PurchaseOrder::class],
            'SalesOrder (Sales)' => [SalesOrder::class],
            'DeliveryOrder (Sales)' => [DeliveryOrder::class],
            'Dispensing (Clinical\Pharmacy)' => [Dispensing::class],
            'LabOrder (Clinical\Ancillary)' => [LabOrder::class],
            'RadiologyOrder (Clinical\Ancillary)' => [RadiologyOrder::class],
        ];
    }

    /**
     * @dataProvider transactionModelProvider
     */
    public function test_transaction_models_are_scoped_by_company_of_logged_in_user(string $modelClass): void
    {
        $this->actingAsScopedUser();

        $fxA = $this->fixturesFor($this->companyA->uuid, $this->branchA->uuid);
        $fxB = $this->fixturesFor($this->companyB->uuid, $this->branchB->uuid);

        $modelA = $modelClass::query()->create($this->attributesFor($modelClass, $fxA, $this->companyA->uuid, $this->branchA->uuid));
        $modelClass::query()->create($this->attributesFor($modelClass, $fxB, $this->companyB->uuid, $this->branchB->uuid));

        // Global scope harus otomatis: query tanpa where manual hanya
        // mengembalikan data company user yang login.
        $this->assertSame(1, $modelClass::query()->count(), "{$modelClass} harus terfilter ke company A saja.");
        $this->assertSame($modelA->uuid, $modelClass::query()->first()->uuid);
    }

    /**
     * @dataProvider transactionModelProvider
     */
    public function test_transaction_models_create_fill_company_branch_from_context(string $modelClass): void
    {
        $this->actingAsScopedUser();

        $fxA = $this->fixturesFor($this->companyA->uuid, $this->branchA->uuid);

        // company_uuid/branch_uuid TIDAK diisi eksplisit -- trait wajib
        // mengisinya dari konteks user login.
        $model = $modelClass::query()->create($this->attributesFor($modelClass, $fxA, null, null));

        $this->assertSame($this->companyA->uuid, $model->company_uuid, "{$modelClass} harus mengisi company_uuid dari konteks.");
        $this->assertSame($this->branchA->uuid, $model->branch_uuid, "{$modelClass} harus mengisi branch_uuid dari konteks.");
    }

    /**
     * Attribute minimum untuk create baris data per model.
     * company/branch null = diserahkan ke trait (auto-fill dari konteks).
     */
    private function attributesFor(string $modelClass, array $fx, ?string $companyUuid, ?string $branchUuid): array
    {
        $base = [
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
        ];

        return match ($modelClass) {
            Journal::class => array_merge($base, [
                'transaction_code' => $this->nextDocNo('TRX'),
                'reference_no' => $this->nextDocNo('REF'),
                'journal_date' => time(),
            ]),
            JournalLine::class => array_merge($base, [
                'journal_uuid' => Journal::query()->create([
                    'company_uuid' => $companyUuid,
                    'branch_uuid' => $branchUuid,
                    'transaction_code' => $this->nextDocNo('TRX-L'),
                    'reference_no' => $this->nextDocNo('REF-L'),
                    'journal_date' => time(),
                ])->uuid,
                'account_uuid' => $fx['account']->uuid,
                'debit' => 1000,
            ]),
            Transaction::class => array_merge($base, [
                'location_uuid' => $fx['location']->uuid,
                'user_uuid' => $this->user->uuid,
                'order_type_uuid' => $fx['location']->uuid,
                'date' => time(),
                'total_amount' => 10000,
                'total_qty' => 1,
            ]),
            SalesInvoice::class => array_merge($base, [
                'invoice_no' => $this->nextDocNo('INV'),
                'invoice_date' => time(),
                'person_uuid' => $fx['person']->uuid,
                'payment_term_uuid' => $fx['location']->uuid,
                'location_uuid' => $fx['location']->uuid,
            ]),
            GeneralLedger::class => array_merge($base, [
                'journal_date' => time(),
                'account_uuid' => $fx['account']->uuid,
                'reference_no' => $this->nextDocNo('GL'),
                'journal_uuid' => Journal::query()->create([
                    'company_uuid' => $companyUuid,
                    'branch_uuid' => $branchUuid,
                    'transaction_code' => $this->nextDocNo('TRX-GL'),
                    'reference_no' => $this->nextDocNo('REF-GL'),
                    'journal_date' => time(),
                ])->uuid,
                'journal_line_uuid' => JournalLine::query()->create([
                    'company_uuid' => $companyUuid,
                    'branch_uuid' => $branchUuid,
                    'journal_uuid' => Journal::query()->create([
                        'company_uuid' => $companyUuid,
                        'branch_uuid' => $branchUuid,
                        'transaction_code' => $this->nextDocNo('TRX-JL'),
                        'reference_no' => $this->nextDocNo('REF-JL'),
                        'journal_date' => time(),
                    ])->uuid,
                    'account_uuid' => $fx['account']->uuid,
                    'debit' => 500,
                ])->uuid,
            ]),
            Stock::class => array_merge($base, [
                'ingredient_uuid' => $fx['item']->uuid,
                'first_stock' => 10,
            ]),
            StockMovement::class => array_merge($base, [
                'product_uuid' => $fx['item']->uuid,
                'qty' => 5,
                'type' => 'in',
            ]),
            Purchase::class => array_merge($base, [
                'invoice_no' => $this->nextDocNo('PUR'),
                'supplier_uuid' => $fx['supplier']->uuid,
                'purchase_date' => time(),
            ]),
            PurchaseOrder::class => array_merge($base, [
                'document_no' => $this->nextDocNo('PO'),
                'date' => time(),
                'supplier_uuid' => $fx['supplier']->uuid,
                'location_uuid' => $fx['location']->uuid,
            ]),
            SalesOrder::class => array_merge($base, [
                'sales_order_no' => $this->nextDocNo('SO'),
                'sales_order_date' => time(),
                'person_uuid' => $fx['person']->uuid,
                'location_uuid' => $fx['location']->uuid,
                'payment_term_uuid' => $fx['location']->uuid,
            ]),
            DeliveryOrder::class => array_merge($base, [
                'delivery_no' => $this->nextDocNo('DO'),
                'delivery_date' => time(),
                'person_uuid' => $fx['person']->uuid,
                'location_uuid' => $fx['location']->uuid,
            ]),
            Dispensing::class => array_merge($base, [
                'dispensing_no' => $this->nextDocNo('DSP'),
                'dispensed_at' => time(),
            ]),
            LabOrder::class => array_merge($base, [
                'order_no' => $this->nextDocNo('LAB'),
                'ordered_at' => time(),
                'patient_uuid' => $fx['patient']->uuid,
            ]),
            RadiologyOrder::class => array_merge($base, [
                'order_no' => $this->nextDocNo('RAD'),
                'ordered_at' => time(),
                'patient_uuid' => $fx['patient']->uuid,
                'modality' => 'Rontgen',
                'body_part' => 'Chest',
            ]),
            default => $base,
        };
    }
}
