<?php

namespace Tests\Feature\Infrastructure;

use App\Modules\Clinical\Ancillary\Models\LabOrderItem;
use App\Modules\Clinical\Ancillary\Models\LabOrder;
use App\Modules\Clinical\Pharmacy\Models\Dispensing;
use App\Modules\Clinical\Pharmacy\Models\DispensingItem;
use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Identity\Models\UserBranch;
use App\Modules\Core\Identity\Models\UserCompany;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Journal\Models\AccountMapping;
use App\Modules\Finance\Journal\Models\Asset;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Finance\Journal\Models\JournalAttachment;
use App\Modules\Finance\Journal\Models\JournalLine;
use App\Modules\Finance\Journal\Models\OpeningBalanceHeader;
use App\Modules\Finance\Journal\Models\RecurringJournalLine;
use App\Modules\Finance\Ledger\Models\SalesInvoice;
use App\Modules\Finance\Ledger\Models\SalesInvoiceAging;
use App\Modules\Finance\Ledger\Models\SalesInvoiceDetail;
use App\Modules\Finance\Ledger\Models\Transaction;
use App\Modules\Finance\Ledger\Models\TransactionDetail;
use App\Modules\Operational\Inventory\Models\ItemBalanceRecord;
use App\Modules\Operational\Inventory\Models\ItemDetail;
use App\Modules\Operational\Inventory\Models\ItemOpeningBalance;
use App\Modules\Operational\Inventory\Models\ItemStok;
use App\Modules\Operational\Inventory\Models\StockAdjustment;
use App\Modules\Operational\Inventory\Models\StockAdjustmentItem;
use App\Modules\Operational\Inventory\Models\StockTransfer;
use App\Modules\Operational\Inventory\Models\StockTransferItem;
use App\Modules\Operational\Master\Models\Location;
use App\Modules\Operational\Master\Models\Person;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Master\Models\Supplier;
use App\Modules\Operational\Master\Models\Unit;
use App\Modules\Operational\Manufacturing\Models\Assembly;
use App\Modules\Operational\Manufacturing\Models\AssemblyItem;
use App\Modules\Operational\Manufacturing\Models\Ingredient;
use App\Modules\Operational\Procurement\Models\GoodsReceipt;
use App\Modules\Operational\Procurement\Models\GoodsReceiptItem;
use App\Modules\Operational\Procurement\Models\Purchase;
use App\Modules\Operational\Procurement\Models\PurchaseItem;
use App\Modules\Operational\Procurement\Models\PurchaseOrder;
use App\Modules\Operational\Procurement\Models\PurchaseOrderItem;
use App\Modules\Operational\Procurement\Models\PurchaseRequisition;
use App\Modules\Operational\Procurement\Models\PurchaseRequisitionItem;
use App\Modules\Operational\Procurement\Models\PurchaseReturn;
use App\Modules\Operational\Procurement\Models\PurchaseReturnItem;
use App\Modules\Operational\Procurement\Models\PurchaseReturnPayment;
use App\Modules\Operational\Sales\Models\DeliveryOrder;
use App\Modules\Operational\Sales\Models\DeliveryOrderDetail;
use App\Modules\Operational\Sales\Models\SalesOrder;
use App\Modules\Operational\Sales\Models\SalesOrderDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * TDD test untuk Section 30.6 iterasi 2 (model child/detail dari transaksi).
 *
 * Konsep: baris detail/child ikut ter-scope company+branch sama seperti
 * parent-nya, supaya query standalone terhadap tabel detail tetap aman
 * walau lupa join ke parent. Pola sama dengan TransactionModelScopeTest.
 */
class ChildModelScopeTest extends TestCase
{
    use RefreshDatabase;

    private Company $companyA;
    private Company $companyB;
    private Branch $branchA;
    private Branch $branchB;
    private User $user;

    private array $fixtures = [];
    private int $docSeq = 0;

    private function nextDocNo(string $prefix): string
    {
        return $prefix . '-' . (++$this->docSeq) . '-' . uniqid();
    }

    /**
     * Ambil parent dari fixture, atau buat sekali (lazy) bila parent itu
     * sendiri adalah model yang sedang diuji scope-nya.
     */
    private function ensureParent(array $fx, ?string $companyUuid, ?string $branchUuid, string $key, \Closure $factory): object
    {
        // Konteks auto-fill (companyUuid null): parent WAJIB sudah ada di
        // fixture karena trait scope butuh konteks login saat create.
        if (isset($fx[$key])) {
            return $fx[$key];
        }

        return $this->fixtures[$companyUuid][$key] = $factory();
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

        $product = Product::query()->create([
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
            'code' => 'ITM-' . substr($companyUuid, 0, 8),
            'name' => 'Item Test',
        ]);

        $unit = Unit::query()->create([
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
            'code' => 'U-' . substr($companyUuid, 0, 8),
            'name' => 'Satuan Test',
        ]);

        $journal = Journal::query()->create([
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
            'transaction_code' => $this->nextDocNo('TRX-FX'),
            'reference_no' => $this->nextDocNo('REF-FX'),
            'journal_date' => time(),
        ]);

        $journalLine = JournalLine::query()->create([
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
            'journal_uuid' => $journal->uuid,
            'account_uuid' => $account->uuid,
            'debit' => 1000,
        ]);

        $transaction = Transaction::query()->create([
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
            'location_uuid' => $location->uuid,
            'user_uuid' => $this->user->uuid,
            'order_type_uuid' => $location->uuid,
            'date' => time(),
            'total_amount' => 10000,
            'total_qty' => 1,
        ]);

        $salesInvoice = SalesInvoice::query()->create([
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
            'invoice_no' => $this->nextDocNo('INV-FX'),
            'invoice_date' => time(),
            'person_uuid' => $person->uuid,
            'payment_term_uuid' => $location->uuid,
            'location_uuid' => $location->uuid,
        ]);

        $salesOrder = SalesOrder::query()->create([
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
            'sales_order_no' => $this->nextDocNo('SO-FX'),
            'sales_order_date' => time(),
            'person_uuid' => $person->uuid,
            'location_uuid' => $location->uuid,
            'payment_term_uuid' => $location->uuid,
        ]);

        $deliveryOrder = DeliveryOrder::query()->create([
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
            'delivery_no' => $this->nextDocNo('DO-FX'),
            'delivery_date' => time(),
            'person_uuid' => $person->uuid,
            'location_uuid' => $location->uuid,
        ]);

        $purchase = Purchase::query()->create([
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
            'invoice_no' => $this->nextDocNo('PUR-FX'),
            'supplier_uuid' => $supplier->uuid,
            'purchase_date' => time(),
        ]);

        $purchaseOrder = PurchaseOrder::query()->create([
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
            'document_no' => $this->nextDocNo('PO-FX'),
            'date' => time(),
            'supplier_uuid' => $supplier->uuid,
            'location_uuid' => $location->uuid,
        ]);

        $stockTransfer = StockTransfer::query()->create([
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
            'no_pag' => $this->nextDocNo('ST-FX'),
            'date' => time(),
            'source_location_uuid' => $location->uuid,
            'destination_location_uuid' => $location->uuid,
        ]);

        $stockAdjustment = StockAdjustment::query()->create([
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
            'location_uuid' => $location->uuid,
            'adjustment_no' => $this->nextDocNo('SA-FX'),
            'date' => time(),
        ]);

        $recurringJournal = \App\Modules\Finance\Journal\Models\RecurringJournal::query()->create([
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
            'template_name' => 'Template Test ' . substr($companyUuid, 0, 8),
            'frequency' => 'monthly',
            'next_run_date' => time() + 86400,
            'journal_data' => json_encode(['total' => 1000]),
        ]);

        $dispensing = Dispensing::query()->create([
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
            'dispensing_no' => $this->nextDocNo('DSP-FX'),
            'dispensed_at' => time(),
        ]);

        $patient = Patient::query()->create([
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
            'medical_record_no' => 'MR-' . substr($companyUuid, 0, 8) . '-' . (++$this->docSeq),
            'name' => 'Pasien Test',
        ]);

        $labOrder = LabOrder::query()->create([
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
            'order_no' => $this->nextDocNo('LAB-FX'),
            'ordered_at' => time(),
            'patient_uuid' => $patient->uuid,
        ]);

        return $this->fixtures[$companyUuid] = compact(
            'account', 'supplier', 'person', 'location', 'product', 'unit', 'patient',
            'journal', 'journalLine', 'transaction', 'salesInvoice',
            'salesOrder', 'deliveryOrder', 'purchase', 'purchaseOrder',
            'stockTransfer', 'stockAdjustment', 'recurringJournal',
            'dispensing', 'labOrder',
        );
    }

    public static function childModelProvider(): array
    {
        return [
            'JournalAttachment (Finance)' => [JournalAttachment::class],
            'OpeningBalanceHeader (Finance)' => [OpeningBalanceHeader::class],
            'RecurringJournalLine (Finance)' => [RecurringJournalLine::class],
            'SalesInvoiceAging (Finance)' => [SalesInvoiceAging::class],
            'SalesInvoiceDetail (Finance)' => [SalesInvoiceDetail::class],
            'TransactionDetail (Finance)' => [TransactionDetail::class],
            'AccountMapping (Finance)' => [AccountMapping::class],
            'Asset (Finance)' => [Asset::class],
            'ItemDetail (Inventory)' => [ItemDetail::class],
            'ItemOpeningBalance (Inventory)' => [ItemOpeningBalance::class],
            'ItemStok (Inventory)' => [ItemStok::class],
            'ItemBalanceRecord (Inventory)' => [ItemBalanceRecord::class],
            'StockTransferItem (Inventory)' => [StockTransferItem::class],
            'StockAdjustmentItem (Inventory)' => [StockAdjustmentItem::class],
            'Assembly (Manufacturing)' => [Assembly::class],
            'AssemblyItem (Manufacturing)' => [AssemblyItem::class],
            'Ingredient (Manufacturing)' => [Ingredient::class],
            'GoodsReceipt (Procurement)' => [GoodsReceipt::class],
            'GoodsReceiptItem (Procurement)' => [GoodsReceiptItem::class],
            'PurchaseItem (Procurement)' => [PurchaseItem::class],
            'PurchaseOrderItem (Procurement)' => [PurchaseOrderItem::class],
            'PurchaseRequisition (Procurement)' => [PurchaseRequisition::class],
            'PurchaseRequisitionItem (Procurement)' => [PurchaseRequisitionItem::class],
            'PurchaseReturn (Procurement)' => [PurchaseReturn::class],
            'PurchaseReturnItem (Procurement)' => [PurchaseReturnItem::class],
            'PurchaseReturnPayment (Procurement)' => [PurchaseReturnPayment::class],
            'SalesOrderDetail (Sales)' => [SalesOrderDetail::class],
            'DeliveryOrderDetail (Sales)' => [DeliveryOrderDetail::class],
            'DispensingItem (Clinical\Pharmacy)' => [DispensingItem::class],
            'LabOrderItem (Clinical\Ancillary)' => [LabOrderItem::class],
        ];
    }

    /**
     * @dataProvider childModelProvider
     */
    public function test_child_models_are_scoped_by_company_of_logged_in_user(string $modelClass): void
    {
        $this->actingAsScopedUser();

        $fxA = $this->fixturesFor($this->companyA->uuid, $this->branchA->uuid);
        $fxB = $this->fixturesFor($this->companyB->uuid, $this->branchB->uuid);

        $modelA = $modelClass::query()->create($this->attributesFor($modelClass, $fxA, $this->companyA->uuid, $this->branchA->uuid));
        $modelClass::query()->create($this->attributesFor($modelClass, $fxB, $this->companyB->uuid, $this->branchB->uuid));

        $this->assertSame(1, $modelClass::query()->count(), "{$modelClass} harus terfilter ke company A saja.");
        $this->assertSame($modelA->uuid, $modelClass::query()->first()->uuid);
    }

    /**
     * @dataProvider childModelProvider
     */
    public function test_child_models_create_fill_company_branch_from_context(string $modelClass): void
    {
        $this->actingAsScopedUser();

        $fxA = $this->fixturesFor($this->companyA->uuid, $this->branchA->uuid);

        $model = $modelClass::query()->create($this->attributesFor($modelClass, $fxA, null, null));

        $this->assertSame($this->companyA->uuid, $model->company_uuid, "{$modelClass} harus mengisi company_uuid dari konteks.");
        $this->assertSame($this->branchA->uuid, $model->branch_uuid, "{$modelClass} harus mengisi branch_uuid dari konteks.");
    }

    private function attributesFor(string $modelClass, array $fx, ?string $companyUuid, ?string $branchUuid): array
    {
        $base = [
            'company_uuid' => $companyUuid,
            'branch_uuid' => $branchUuid,
        ];

        return match ($modelClass) {
            JournalAttachment::class => array_merge($base, [
                'journal_uuid' => $fx['journal']->uuid,
                'file_name' => $this->nextDocNo('ATT') . '.pdf',
                'file_path' => 'attachments/' . uniqid() . '.pdf',
                'file_type' => 'application/pdf',
                'file_size' => 1024,
            ]),
            OpeningBalanceHeader::class => array_merge($base, [
                'transaction_no' => $this->nextDocNo('OB'),
                'transaction_date' => time(),
                'period_month' => '2026-10',
                'type' => 'item',
            ]),
            RecurringJournalLine::class => array_merge($base, [
                'recurring_journal_uuid' => $fx['recurringJournal']->uuid,
                'account_uuid' => $fx['account']->uuid,
                'debit' => 1000,
            ]),
            SalesInvoiceAging::class => array_merge($base, [
                'sales_invoice_uuid' => $fx['salesInvoice']->uuid,
                'due_date' => time() + 86400,
                'amount' => 10000,
                'remaining' => 10000,
            ]),
            SalesInvoiceDetail::class => array_merge($base, [
                'sales_invoice_uuid' => $fx['salesInvoice']->uuid,
                'item_uuid' => $fx['product']->uuid,
                'qty' => 1,
                'price' => 10000,
                'total' => 10000,
            ]),
            TransactionDetail::class => array_merge($base, [
                'transaction_uuid' => $fx['transaction']->uuid,
                'product_uuid' => $fx['product']->uuid,
                'product_name' => 'Item Test',
                'product_price' => 10000,
                'qty' => 1,
                'subtotal' => 10000,
            ]),
            AccountMapping::class => array_merge($base, [
                'key' => 'map.' . $this->nextDocNo('K'),
                'account_uuid' => $fx['account']->uuid,
            ]),
            Asset::class => array_merge($base, [
                'code' => $this->nextDocNo('AST'),
                'name' => 'Asset Test',
                'quantity' => 1,
                'type_uuid' => 1,
                'asset_value' => 100000,
                'buy_date' => time(),
            ]),
            ItemDetail::class => array_merge($base, [
                'item_uuid' => $fx['product']->uuid,
                'location_uuid' => $fx['location']->uuid,
                'serial_no' => $this->nextDocNo('SN'),
            ]),
            ItemOpeningBalance::class => array_merge($base, [
                'item_uuid' => $fx['product']->uuid,
                'location_uuid' => $fx['location']->uuid,
                'period_month' => '2026-10',
                'opening_qty' => 10,
            ]),
            ItemStok::class => array_merge($base, [
                'item_uuid' => $fx['product']->uuid,
                'location_uuid' => $fx['location']->uuid,
                'stock_date' => time(),
                'stock_awal' => 10,
            ]),
            ItemBalanceRecord::class => array_merge($base, [
                'item_uuid' => $fx['product']->uuid,
                'location_uuid' => $fx['location']->uuid,
                'period_name' => 202610,
                'stock_date' => time(),
                'qty_awal' => 10,
            ]),
            StockTransferItem::class => array_merge($base, [
                'transfer_uuid' => $fx['stockTransfer']->uuid,
                'product_uuid' => $fx['product']->uuid,
                'unit_uuid' => $fx['unit']->uuid,
                'qty' => 5,
            ]),
            StockAdjustmentItem::class => array_merge($base, [
                'adjustment_uuid' => $fx['stockAdjustment']->uuid,
                'item_uuid' => $fx['product']->uuid,
                'unit_uuid' => $fx['unit']->uuid,
                'book_qty' => 10,
                'actual_qty' => 8,
                'adjustment_qty' => 2,
            ]),
            Assembly::class => array_merge($base, [
                'document_no' => $this->nextDocNo('ASM'),
                'date' => time(),
                'item_uuid' => $fx['product']->uuid,
                'unit_uuid' => $fx['unit']->uuid,
                'location_uuid' => $fx['location']->uuid,
                'quantity' => 1,
            ]),
            AssemblyItem::class => array_merge($base, [
                'assembly_uuid' => $this->ensureParent($fx, $companyUuid, $branchUuid, 'assembly', function () use ($fx, $companyUuid, $branchUuid) {
                    return Assembly::query()->create([
                        'company_uuid' => $companyUuid,
                        'branch_uuid' => $branchUuid,
                        'document_no' => $this->nextDocNo('ASMP'),
                        'date' => time(),
                        'item_uuid' => $fx['product']->uuid,
                        'unit_uuid' => $fx['unit']->uuid,
                        'location_uuid' => $fx['location']->uuid,
                        'quantity' => 1,
                    ]);
                })->uuid,
                'item_uuid' => $fx['product']->uuid,
                'unit_uuid' => $fx['unit']->uuid,
                'quantity' => 5,
            ]),
            Ingredient::class => array_merge($base, [
                'name' => 'Ingredient Test ' . $this->nextDocNo('ING'),
                'unit_uuid' => $fx['unit']->uuid,
            ]),
            GoodsReceipt::class => array_merge($base, [
                'document_no' => $this->nextDocNo('GR'),
                'date' => time(),
                'supplier_uuid' => $fx['supplier']->uuid,
                'location_uuid' => $fx['location']->uuid,
            ]),
            GoodsReceiptItem::class => array_merge($base, [
                'goods_receipt_uuid' => $this->ensureParent($fx, $companyUuid, $branchUuid, 'goodsReceipt', function () use ($fx, $companyUuid, $branchUuid) {
                    return GoodsReceipt::query()->create([
                        'company_uuid' => $companyUuid,
                        'branch_uuid' => $branchUuid,
                        'document_no' => $this->nextDocNo('GRP'),
                        'date' => time(),
                        'supplier_uuid' => $fx['supplier']->uuid,
                        'location_uuid' => $fx['location']->uuid,
                    ]);
                })->uuid,
                'product_uuid' => $fx['product']->uuid,
                'unit_uuid' => $fx['unit']->uuid,
                'quantity' => 5,
                'price' => 10000,
            ]),
            PurchaseItem::class => array_merge($base, [
                'purchase_uuid' => $fx['purchase']->uuid,
                'product_uuid' => $fx['product']->uuid,
                'unit_uuid' => $fx['unit']->uuid,
                'qty' => 5,
                'price' => 10000,
            ]),
            PurchaseOrderItem::class => array_merge($base, [
                'purchase_order_uuid' => $fx['purchaseOrder']->uuid,
                'product_uuid' => $fx['product']->uuid,
                'unit_uuid' => $fx['unit']->uuid,
                'quantity' => 5,
                'price' => 10000,
                'subtotal' => 50000,
            ]),
            PurchaseRequisition::class => array_merge($base, [
                'document_no' => $this->nextDocNo('PR'),
                'reference_no' => $this->nextDocNo('REF-PR'),
                'date' => time(),
                'requested_by' => $this->user->uuid,
                'status' => 'draft',
            ]),
            PurchaseRequisitionItem::class => array_merge($base, [
                'purchase_requisition_uuid' => $this->ensureParent($fx, $companyUuid, $branchUuid, 'purchaseRequisition', function () use ($fx, $companyUuid, $branchUuid) {
                    return PurchaseRequisition::query()->create([
                        'company_uuid' => $companyUuid,
                        'branch_uuid' => $branchUuid,
                        'document_no' => $this->nextDocNo('PRP'),
                        'reference_no' => $this->nextDocNo('REF-PRP'),
                        'date' => time(),
                        'requested_by' => $this->user->uuid,
                        'status' => 'draft',
                    ]);
                })->uuid,
                'product_uuid' => $fx['product']->uuid,
                'unit_uuid' => $fx['unit']->uuid,
                'quantity' => 5,
            ]),
            PurchaseReturn::class => array_merge($base, [
                'purchase_uuid' => $fx['purchase']->uuid,
                'supplier_uuid' => $fx['supplier']->uuid,
                'return_no' => $this->nextDocNo('PRET'),
                'returned_at' => time(),
            ]),
            PurchaseReturnItem::class => array_merge($base, [
                'return_uuid' => $this->ensureParent($fx, $companyUuid, $branchUuid, 'purchaseReturn', function () use ($fx, $companyUuid, $branchUuid) {
                    return PurchaseReturn::query()->create([
                        'company_uuid' => $companyUuid,
                        'branch_uuid' => $branchUuid,
                        'purchase_uuid' => $fx['purchase']->uuid,
                        'supplier_uuid' => $fx['supplier']->uuid,
                        'return_no' => $this->nextDocNo('PRETP'),
                        'returned_at' => time(),
                    ]);
                })->uuid,
                'product_uuid' => $fx['product']->uuid,
                'qty' => 5,
                'price' => 10000,
            ]),
            PurchaseReturnPayment::class => array_merge($base, [
                'purchase_return_uuid' => $this->ensureParent($fx, $companyUuid, $branchUuid, 'purchaseReturn', function () use ($fx, $companyUuid, $branchUuid) {
                    return PurchaseReturn::query()->create([
                        'company_uuid' => $companyUuid,
                        'branch_uuid' => $branchUuid,
                        'purchase_uuid' => $fx['purchase']->uuid,
                        'supplier_uuid' => $fx['supplier']->uuid,
                        'return_no' => $this->nextDocNo('PRETPAY'),
                        'returned_at' => time(),
                    ]);
                })->uuid,
                'voucher_no' => $this->nextDocNo('VOUCH'),
                'date' => time(),
                'account_uuid' => $fx['account']->uuid,
                'amount' => 5000,
            ]),
            SalesOrderDetail::class => array_merge($base, [
                'sales_order_uuid' => $fx['salesOrder']->uuid,
                'item_uuid' => $fx['product']->uuid,
                'qty' => 1,
                'price' => 10000,
                'total' => 10000,
            ]),
            DeliveryOrderDetail::class => array_merge($base, [
                'delivery_order_uuid' => $fx['deliveryOrder']->uuid,
                'item_uuid' => $fx['product']->uuid,
                'qty' => 1,
            ]),
            DispensingItem::class => array_merge($base, [
                'dispensing_uuid' => $fx['dispensing']->uuid,
                'product_uuid' => $fx['product']->uuid,
                'qty' => 1,
                'price_sell' => 10000,
            ]),
            LabOrderItem::class => array_merge($base, [
                'lab_order_uuid' => $fx['labOrder']->uuid,
                'test_name' => 'Hematologi',
                'test_code' => $this->nextDocNo('TEST'),
            ]),
            default => $base,
        };
    }
}
