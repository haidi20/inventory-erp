<?php

namespace Database\Seeders\V1;

use App\Modules\Finance\Journal\Enums\JournalReferenceModel;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Operational\Procurement\Models\Purchase;
use App\Modules\Finance\Ledger\Models\Payable;
use App\Modules\Operational\Master\Models\Supplier;
use App\Modules\Finance\Journal\Services\JournalService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class JournalSimulationPurchaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info("RUNNING SEEDER FROM: " . __FILE__);
        $journalService = app(JournalService::class);
        $branch = Branch::where('short_name', 'KDM')->firstOrFail();
        $companyUuid = $branch->company_uuid;

        $account1301 = Account::where('code', '1301')->firstOrFail();
        $account2101 = Account::where('code', '2101')->firstOrFail();

        $today = Carbon::create(2026, 3, 17);
        $startDate = $today->copy()->subMonth()->day(27);
        $endDate = $today->copy()->day(26);

        $supplier = Supplier::query()->where('company_uuid', $companyUuid)->first();
        if (!$supplier) {
            $this->command->error("No supplier found for Purchase Seeder.");
            return;
        }

        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dateSlug = $currentDate->format('Ymd');

            $purchaseInvoiceNo = "PO-{$dateSlug}-001-" . strtoupper(\Illuminate\Support\Str::uuid());
            $purchase = Purchase::updateOrCreate(
                ['branch_uuid' => $branch->uuid, 'invoice_no' => $purchaseInvoiceNo],
                [
                    'company_uuid' => $companyUuid,
                    'supplier_uuid' => $supplier->uuid,
                    'total' => 1000000,
                    'paid' => 0,
                    'status' => 'unpaid',
                    'purchase_date' => $currentDate->timestamp,
                    'created_at' => $currentDate->copy()->hour(13)->timestamp,
                ]
            );

            $journalDto = JournalEntity::fromArray([
                'branch_uuid' => $branch->uuid,
                'reference_no' => $purchaseInvoiceNo,
                'reference_uuid' => $purchase->uuid,
                'journal_date' => $currentDate->timestamp,
                'description' => "Seeded: Pembelian Stok - Purchase ID: {$purchase->uuid}",
                'reference_model' => JournalReferenceModel::PURCHASE,
                'reference_type' => JournalReferenceType::PURCHASE,
                'lines' => [
                    ['account_uuid' => $account1301->uuid, 'debit' => 1000000, 'credit' => 0, 'contact_uuid' => $supplier->uuid],
                    ['account_uuid' => $account2101->uuid, 'debit' => 0, 'credit' => 1000000, 'contact_uuid' => $supplier->uuid],
                ]
            ]);

            $journalService->upsertJournal($journalDto);

            Payable::updateOrCreate(
                ['branch_uuid' => $branch->uuid, 'reference_type' => 'purchase', 'reference_uuid' => $purchase->uuid],
                [
                    'amount' => 1000000,
                    'paid' => 0,
                    'status' => 'unpaid',
                    'due_date' => $currentDate->copy()->addDays(30)->timestamp,
                    'created_at' => $currentDate->copy()->hour(13)->timestamp,
                    'updated_at' => $currentDate->copy()->hour(13)->timestamp,
                ]
            );

            $currentDate->addDay();
        }
    }
}

