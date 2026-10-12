<?php

namespace Tests\Feature\Accounting\Journal;

use App\Modules\Core\Organization\Models\Branch;
use Database\Seeders\V1\DemoSeeder;
use App\Modules\Clinical\MedicalRecord\Models\EncounterItem;
use App\Modules\Clinical\MedicalRecord\Models\Encounter;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Services\JournalService;
use App\Modules\Clinical\Ancillary\Models\LabOrder;
use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Finance\Ledger\Models\Payable;
use App\Modules\Operational\Master\Models\Product;
use App\Modules\Operational\Procurement\Models\PurchaseReturn;
use App\Modules\Operational\Procurement\Models\Purchase;
use App\Modules\Clinical\Ancillary\Models\RadiologyOrder;
use App\Modules\Finance\Ledger\Models\Receivable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Modules\Clinical\Registration\Models\Registration;
use App\Modules\Operational\Sales\Models\SaleItem;
use App\Modules\Operational\Sales\Models\Sale;
use App\Modules\Operational\Master\Models\Supplier;
use Tests\TestCase;
use Exception;

trait SaleValidationTrait
{
    private function createDummySale(array $overrides, float $productPriceBuy = 10000): Sale
    {
        $sale = Sale::create(array_merge([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->branch->company_uuid,
            'sale_no' => 'INV-TEST-001',
            'total_amount' => 111000,
            'paid' => 111000,
            'sale_at' => time(),
        ], $overrides));

        $product = Product::create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->branch->company_uuid,
            'code' => 'PROD-001-' . rand(100,999),
            'name' => 'Test',
            'category' => 'Obat Bebas',
            'unit' => 'pcs',
            'price_buy' => $productPriceBuy,
            'price_sell' => 50000,
        ]);

        SaleItem::create([
            'sale_uuid' => $sale->uuid,
            'item_type' => 'product',
            'item_uuid' => $product->uuid,
            'qty' => 1,
            'price' => 100000,
            'subtotal' => 100000,
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->branch->company_uuid,
        ]);

        return $sale->load('items.product');
    }

    public function test_pos_validation_kas_paid_amount_cannot_be_negative(): void
    {
        $sale = $this->createDummySale(['paid' => -50000]); 

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Validasi POS Gagal (Kas): Nominal pembayaran tidak boleh negatif.');

        $this->journalService->storeJournal($sale, JournalReferenceType::POS_SALE);
    }

    public function test_pos_validation_piutang_receivable_amount_cannot_be_negative(): void
    {
        $sale = $this->createDummySale(['total_amount' => 100000, 'paid' => 150000]); 

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Validasi POS Gagal (Piutang): Pembayaran tidak boleh melebihi Grand Total (Minus Piutang).');

        $this->journalService->storeJournal($sale, JournalReferenceType::POS_SALE);
    }

    public function test_pos_validation_pendapatan_net_amount_cannot_be_zero(): void
    {
        $sale = $this->createDummySale(['total_amount' => 0, 'paid' => 0]); 

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Validasi POS Gagal (Pendapatan): Total Net Pendapatan harus lebih dari 0.');

        $this->journalService->storeJournal($sale, JournalReferenceType::POS_SALE);
    }

    public function test_pos_validation_persediaan_cost_amount_cannot_be_zero(): void
    {
        $sale = $this->createDummySale(['total_amount' => 100000, 'paid' => 100000], 0); 

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Validasi POS Gagal (HPP/Persediaan): Total Cost (HPP) barang tidak boleh 0 atau kosong.');

        $this->journalService->storeJournal($sale, JournalReferenceType::POS_SALE);
    }

    public function test_pos_validation_hpp_cost_amount_cannot_be_zero(): void
    {
        $sale = $this->createDummySale(['total_amount' => 100000, 'paid' => 100000], 0); 

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Validasi POS Gagal (HPP/Persediaan): Total Cost (HPP) barang tidak boleh 0 atau kosong.');

        $this->journalService->storeJournal($sale, JournalReferenceType::POS_SALE);
    }

    public function test_pos_validation_ppn_tax_amount_cannot_be_negative(): void
    {
        $sale = $this->createDummySale(['total_amount' => 100000, 'paid' => 100000]); 

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Validasi POS Gagal (PPN): Pajak tidak boleh negatif.');

        $this->journalService->storeJournal($sale, JournalReferenceType::POS_SALE, ['tax_percent' => -10]);
    }
}

trait EncounterValidationTrait
{
    private function createDummyEncounter(array $overrides = [], float $productPriceBuy = 10000, bool $includeProduct = true, bool $includeService = true, float $servicePrice = 50000, float $productPriceSell = 20000): Encounter
    {
        $patient = Patient::create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->branch->company_uuid,
            'name' => 'Encounter Patient',
            'medical_record_no' => 'RM-ENC-' . rand(1000,9999),
            'gender' => 'F'
        ]);

        $registration = Registration::create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->branch->company_uuid,
            'patient_uuid' => $patient->uuid,
            'registered_at' => time(),
        ]);

        $encounter = Encounter::create(array_merge([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->branch->company_uuid,
            'registration_uuid' => $registration->uuid,
            'patient_uuid' => $patient->uuid,
        ], $overrides));

        if ($includeService) {
            EncounterItem::create([
                'encounter_uuid' => $encounter->uuid,
                'item_type' => 'service',
                'item_uuid' => 1, 
                'qty' => 1,
                'price' => $servicePrice,
                'subtotal' => $servicePrice,
                'branch_uuid' => $this->branch->uuid,
                'company_uuid' => $this->branch->company_uuid,
            ]);
        }

        if ($includeProduct) {
            $product = Product::create([
                'branch_uuid' => $this->branch->uuid,
                'company_uuid' => $this->branch->company_uuid,
                'code' => 'PROD-ENC-' . rand(100,999),
                'name' => 'Test Encounter Product',
                'category' => 'Obat Bebas',
                'unit' => 'pcs',
                'price_buy' => $productPriceBuy,
                'price_sell' => $productPriceSell,
            ]);

            EncounterItem::create([
                'encounter_uuid' => $encounter->uuid,
                'item_type' => 'product',
                'item_uuid' => $product->uuid,
                'qty' => 1,
                'price' => $productPriceSell,
                'subtotal' => $productPriceSell,
                'branch_uuid' => $this->branch->uuid,
                'company_uuid' => $this->branch->company_uuid,
            ]);
        }

        return $encounter->load('items.product');
    }

    public function test_encounter_validation_kas_paid_amount_cannot_be_negative(): void
    {
        $encounter = $this->createDummyEncounter(); 

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Validasi Pemeriksaan Gagal (Kas): Nominal pembayaran tidak boleh negatif.');

        $this->journalService->storeJournal($encounter, JournalReferenceType::ENCOUNTER, ['test_paid' => -1000]);
    }

    public function test_encounter_validation_piutang_receivable_amount_cannot_be_negative(): void
    {
        $encounter = $this->createDummyEncounter();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Validasi Pemeriksaan Gagal (Piutang): Nominal piutang tidak boleh negatif.');

        $this->journalService->storeJournal($encounter, JournalReferenceType::ENCOUNTER, ['test_receivable' => -500]);
    }

    public function test_encounter_validation_pendapatan_jasa_service_amount_cannot_be_negative(): void
    {
        $encounter = $this->createDummyEncounter([], 10000, true, true, -100);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Validasi Pemeriksaan Gagal (Jasa): Pendapatan Jasa tidak boleh negatif.');

        $this->journalService->storeJournal($encounter, JournalReferenceType::ENCOUNTER);
    }

    public function test_encounter_validation_penjualan_obat_net_product_cannot_be_negative(): void
    {
        $encounter = $this->createDummyEncounter([], 10000, true, true, 50000, -50);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Validasi Pemeriksaan Gagal (Obat): Pendapatan Obat tidak boleh negatif.');

        $this->journalService->storeJournal($encounter, JournalReferenceType::ENCOUNTER);
    }

    public function test_encounter_validation_persediaan_obat_cost_product_cannot_be_zero(): void
    {
        $encounter = $this->createDummyEncounter([], 0);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Validasi Pemeriksaan Gagal (HPP/Persediaan): Total Cost (HPP) obat tidak boleh 0 atau kosong jika diresepkan.');

        $this->journalService->storeJournal($encounter, JournalReferenceType::ENCOUNTER);
    }

    public function test_encounter_validation_hpp_obat_cost_product_cannot_be_zero(): void
    {
        $encounter = $this->createDummyEncounter([], 0);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Validasi Pemeriksaan Gagal (HPP/Persediaan): Total Cost (HPP) obat tidak boleh 0 atau kosong jika diresepkan.');

        $this->journalService->storeJournal($encounter, JournalReferenceType::ENCOUNTER);
    }
}

trait PurchaseValidationTrait
{
    private function createDummyPurchase(array $overrides = []): Purchase
    {
        $supplier = Supplier::create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->branch->company_uuid,
            'name' => 'Supplier Test',
            'code' => 'SUP-TEST',
            'is_active' => true,
        ]);
        return Purchase::create(array_merge([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->branch->company_uuid,
            'supplier_uuid' => $supplier->uuid,
            'invoice_no' => 'INV-PUR-001',
            'total' => 100000,
            'paid' => 100000,
            'status' => 'received',
            'purchase_date' => now()->toDateString(),
        ], $overrides));
    }

    public function test_purchase_validation_kas_paid_amount(): void
    {
        $purchase = $this->createDummyPurchase(['paid' => -50000]);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Validasi Pembelian Gagal (Kas): Nominal pembayaran tidak boleh negatif.');
        $this->journalService->storeJournal($purchase, JournalReferenceType::PURCHASE);
    }
    public function test_purchase_validation_hutang_receivable_amount(): void
    {
        $purchase = $this->createDummyPurchase(['total' => 100000, 'paid' => 150000]);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Validasi Pembelian Gagal (Hutang): Nominal hutang tidak boleh negatif.');
        $this->journalService->storeJournal($purchase, JournalReferenceType::PURCHASE);
    }
    public function test_purchase_validation_persediaan_gross_amount(): void
    {
        $purchase = $this->createDummyPurchase(['total' => 0, 'paid' => 0]);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Validasi Pembelian Gagal (Persediaan): Total pembelian tidak boleh 0 atau kosong.');
        $this->journalService->storeJournal($purchase, JournalReferenceType::PURCHASE);
    }
}

trait PayableValidationTrait
{
    private function createDummyPayable(array $overrides = []): Payable
    {
        return Payable::create(array_merge([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->branch->company_uuid,
            'reference_type' => 'Purchase',
            'reference_uuid' => 1,
            'amount' => 10000,
            'paid' => 0,
            'status' => 'paid',
            'due_date' => now()->toDateString(),
        ], $overrides));
    }
    public function test_payable_validation_kas_amount(): void
    {
        $payable = $this->createDummyPayable();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Validasi Gagal (Payable): Nominal transaksi (Jumlah) tidak boleh 0 atau negatif.');
        $this->journalService->storeJournal($payable, JournalReferenceType::DEBT_PAYMENT, ['test_amount' => 0]);
    }
    public function test_payable_validation_hutang_usaha_amount(): void
    {
        $payable = $this->createDummyPayable();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Validasi Gagal (Payable): Nominal transaksi (Jumlah) tidak boleh 0 atau negatif.');
        $this->journalService->storeJournal($payable, JournalReferenceType::DEBT_PAYMENT, ['test_amount' => -100]);
    }
}

trait ReceivableValidationTrait
{
    private function createDummyReceivable(array $overrides = []): Receivable
    {
        return Receivable::create(array_merge([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->branch->company_uuid,
            'reference_type' => 'Sale',
            'reference_uuid' => 1,
            'amount' => 10000,
            'paid' => 10000,
            'status' => 'paid',
            'due_date' => now()->toDateString(),
        ], $overrides));
    }
    public function test_receivable_validation_kas_amount(): void
    {
        $receivable = $this->createDummyReceivable();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Validasi Gagal (Receivable): Nominal transaksi (Jumlah) tidak boleh 0 atau negatif.');
        $this->journalService->storeJournal($receivable, JournalReferenceType::RECEIVABLE_COLLECTION, ['test_amount' => 0]);
    }
    public function test_receivable_validation_piutang_usaha_amount(): void
    {
        $receivable = $this->createDummyReceivable();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Validasi Gagal (Receivable): Nominal transaksi (Jumlah) tidak boleh 0 atau negatif.');
        $this->journalService->storeJournal($receivable, JournalReferenceType::RECEIVABLE_COLLECTION, ['test_amount' => -500]);
    }
}

trait ExpenseValidationTrait
{
    private function createDummyExpense(array $overrides = []): \Illuminate\Database\Eloquent\Model
    {
        $model = new Branch();
        $model->forceFill(array_merge([
            'amount' => 10000,
            'date' => now()->toDateString(),
        ], $overrides));
        return $model;
    }
    // Gaji
    public function test_expense_validation_kas_gaji_amount(): void
    {
        $expense = $this->createDummyExpense();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Validasi Generic Gagal (Amount): Nominal transaksi harus lebih dari 0.');
        $this->journalService->storeJournal($expense, JournalReferenceType::EXPENSE, ['test_amount' => 0, 'menu_name' => 'Biaya Gaji']);
    }
    public function test_expense_validation_beban_gaji_amount(): void {
        $expense = $this->createDummyExpense();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Validasi Generic Gagal (Amount): Nominal transaksi harus lebih dari 0.');
        $this->journalService->storeJournal($expense, JournalReferenceType::EXPENSE, ['test_amount' => -10, 'menu_name' => 'Biaya Gaji']);
    }
    // Listrik
    public function test_expense_validation_kas_listrik_amount(): void {
        $expense = $this->createDummyExpense();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Validasi Generic Gagal (Amount): Nominal transaksi harus lebih dari 0.');
        $this->journalService->storeJournal($expense, JournalReferenceType::EXPENSE, ['test_amount' => 0, 'menu_name' => 'Biaya Listrik']);
    }
    public function test_expense_validation_beban_listrik_amount(): void {
        $expense = $this->createDummyExpense();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Validasi Generic Gagal (Amount): Nominal transaksi harus lebih dari 0.');
        $this->journalService->storeJournal($expense, JournalReferenceType::EXPENSE, ['test_amount' => 0, 'menu_name' => 'Biaya Listrik']);
    }
    // Air
    public function test_expense_validation_kas_air_amount(): void {
        $expense = $this->createDummyExpense();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Validasi Generic Gagal (Amount): Nominal transaksi harus lebih dari 0.');
        $this->journalService->storeJournal($expense, JournalReferenceType::EXPENSE, ['test_amount' => 0, 'menu_name' => 'Biaya Air']);
    }
    public function test_expense_validation_beban_air_amount(): void {
        $expense = $this->createDummyExpense();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Validasi Generic Gagal (Amount): Nominal transaksi harus lebih dari 0.');
        $this->journalService->storeJournal($expense, JournalReferenceType::EXPENSE, ['test_amount' => 0, 'menu_name' => 'Biaya Air']);
    }
    // Umum
    public function test_expense_validation_kas_umum_amount(): void {
        $expense = $this->createDummyExpense();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Validasi Generic Gagal (Amount): Nominal transaksi harus lebih dari 0.');
        $this->journalService->storeJournal($expense, JournalReferenceType::EXPENSE, ['test_amount' => 0, 'menu_name' => 'Biaya Umum']);
    }
    public function test_expense_validation_beban_umum_amount(): void {
        $expense = $this->createDummyExpense();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Validasi Generic Gagal (Amount): Nominal transaksi harus lebih dari 0.');
        $this->journalService->storeJournal($expense, JournalReferenceType::EXPENSE, ['test_amount' => 0, 'menu_name' => 'Biaya Umum']);
    }
}

trait PurchaseReturnValidationTrait
{
    private function createDummyPurchaseReturn(array $overrides = []): PurchaseReturn
    {
        $purchase = $this->createDummyPurchase();
        return PurchaseReturn::create(array_merge([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->branch->company_uuid,
            'purchase_uuid' => $purchase->uuid,
            'supplier_uuid' => $purchase->supplier_uuid,
            'return_no' => 'RET-TEST',
            'amount_paid' => 10000,
            'returned_at' => now()->toDateString(),
            'date' => now()->toDateString(),
        ], $overrides));
    }
    public function test_purchase_return_validation_hutang_amount(): void
    {
        $r = $this->createDummyPurchaseReturn();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Validasi Gagal (PurchaseReturn): Nominal transaksi (Jumlah) tidak boleh 0 atau negatif.');
        $this->journalService->storeJournal($r, JournalReferenceType::PURCHASE_RETURN, ['test_amount' => 0]);
    }
    public function test_purchase_return_validation_stok_amount(): void
    {
        $r = $this->createDummyPurchaseReturn();
        $this->expectException(Exception::class);
        $this->journalService->storeJournal($r, JournalReferenceType::PURCHASE_RETURN, ['test_amount' => 0]);
    }
}

trait StockAdjustmentValidationTrait
{
    private function createDummyStockAdj(array $overrides = []): \Illuminate\Database\Eloquent\Model
    {
        $model = new Branch();
        $model->forceFill(array_merge([
            'amount' => 10000, 
            'date' => now()->toDateString(),
        ], $overrides));
        return $model;
    }
    public function test_stock_adjustment_validation_beban_amount(): void
    {
        $obj = $this->createDummyStockAdj();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Validasi Generic Gagal (Amount): Nominal transaksi harus lebih dari 0.');
        $this->journalService->storeJournal($obj, JournalReferenceType::STOCK_ADJUSTMENT, ['test_amount' => 0]);
    }
    public function test_stock_adjustment_validation_stok_amount(): void
    {
        $obj = $this->createDummyStockAdj();
        $this->expectException(Exception::class);
        $this->journalService->storeJournal($obj, JournalReferenceType::STOCK_ADJUSTMENT, ['test_amount' => 0]);
    }
}

trait LabValidationTrait
{
    private function createDummyLabOrder(array $overrides = []): LabOrder
    {
        $patient = Patient::create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->branch->company_uuid,
            'name' => 'Lab Patient',
            'medical_record_no' => 'RM-LAB-' . rand(1000,9999),
            'gender' => 'M'
        ]);
        $registration = Registration::create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->branch->company_uuid,
            'patient_uuid' => $patient->uuid,
            'registered_at' => time(),
        ]);
        return LabOrder::create(array_merge([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->branch->company_uuid,
            'order_no' => 'LAB-TEST',
            'encounter_uuid' => null,
            'registration_uuid' => $registration->uuid,
            'patient_uuid' => $patient->uuid,
            'total_amount' => 50000,
            'status' => 'completed',
            'ordered_at' => now()->toDateTimeString(),
        ], $overrides));
    }
    public function test_lab_validation_kas_amount(): void
    {
        $obj = $this->createDummyLabOrder();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Validasi Gagal (LabOrder): Pendapatan/Kas harus lebih dari 0.');
        $this->journalService->storeJournal($obj, JournalReferenceType::LABORATORY, ['test_amount' => 0]);
    }
    public function test_lab_validation_pendapatan_amount(): void
    {
        $obj = $this->createDummyLabOrder();
        $this->expectException(Exception::class);
        $this->journalService->storeJournal($obj, JournalReferenceType::LABORATORY, ['test_amount' => -100]);
    }
}

trait RadiologyValidationTrait
{
    private function createDummyRadOrder(array $overrides = []): RadiologyOrder
    {
        $patient = Patient::create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->branch->company_uuid,
            'name' => 'Rad Patient',
            'medical_record_no' => 'RM-RAD-' . rand(1000,9999),
            'gender' => 'F'
        ]);
        $registration = Registration::create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->branch->company_uuid,
            'patient_uuid' => $patient->uuid,
            'registered_at' => time(),
        ]);
        return RadiologyOrder::create(array_merge([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->branch->company_uuid,
            'order_no' => 'RAD-TEST',
            'encounter_uuid' => null,
            'registration_uuid' => $registration->uuid,
            'patient_uuid' => $patient->uuid,
            'total_amount' => 70000,
            'status' => 'completed',
            'modality' => 'X-Ray',
            'body_part' => 'Thorax',
            'ordered_at' => now()->toDateTimeString(),
        ], $overrides));
    }
    public function test_radiology_validation_kas_amount(): void
    {
        $obj = $this->createDummyRadOrder();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Validasi Gagal (RadiologyOrder): Pendapatan/Kas harus lebih dari 0.');
        $this->journalService->storeJournal($obj, JournalReferenceType::RADIOLOGY, ['test_amount' => 0]);
    }
    public function test_radiology_validation_pendapatan_amount(): void
    {
        $obj = $this->createDummyRadOrder();
        $this->expectException(Exception::class);
        $this->journalService->storeJournal($obj, JournalReferenceType::RADIOLOGY, ['test_amount' => -100]);
    }
}

trait RegistrationValidationTrait
{
    private function createDummyRegistration(array $overrides = []): Registration
    {
        $patient = Patient::create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->branch->company_uuid,
            'name' => 'Reg Patient',
            'medical_record_no' => 'RM-REG-' . rand(1000,9999),
            'gender' => 'F'
        ]);
        return Registration::create(array_merge([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->branch->company_uuid,
            'patient_uuid' => $patient->uuid,
            'amount' => 15000, 
            'registered_at' => time(),
        ], $overrides));
    }
    public function test_registration_validation_kas_amount(): void
    {
        $obj = $this->createDummyRegistration();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Validasi Generic Gagal (Amount): Nominal transaksi harus lebih dari 0.');
        $this->journalService->storeJournal($obj, JournalReferenceType::REGISTRATION, ['test_amount' => 0]);
    }
    public function test_registration_validation_pendapatan_amount(): void
    {
        $obj = $this->createDummyRegistration();
        $this->expectException(Exception::class);
        $this->journalService->storeJournal($obj, JournalReferenceType::REGISTRATION, ['test_amount' => -50]);
    }
}

class JournalServiceValidationTest extends TestCase
{
    use RefreshDatabase, SaleValidationTrait, EncounterValidationTrait, PurchaseValidationTrait, PayableValidationTrait, ReceivableValidationTrait, ExpenseValidationTrait, PurchaseReturnValidationTrait, StockAdjustmentValidationTrait, LabValidationTrait, RadiologyValidationTrait, RegistrationValidationTrait;

    protected JournalService $journalService;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->journalService = app(JournalService::class);
        $this->seed(DemoSeeder::class);
        $this->branch = Branch::where('short_name', 'KDM')->firstOrFail();
    }
}
