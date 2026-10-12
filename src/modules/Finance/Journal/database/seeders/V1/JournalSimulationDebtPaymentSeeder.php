<?php

namespace Database\Seeders\V1;

use App\Modules\Finance\Journal\Enums\JournalReferenceModel;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Operational\Procurement\Models\Purchase;
use App\Modules\Finance\Ledger\Models\Payable;
use App\Modules\Finance\Journal\Services\JournalService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class JournalSimulationDebtPaymentSeeder extends Seeder
{
    public function run(): void
    {
        $journalService = app(JournalService::class);
        $branch = Branch::where('short_name', 'KDM')->firstOrFail();
        $companyUuid = $branch->company_uuid;

        $account2101 = Account::where('code', '2101')->firstOrFail();
        $account1101 = Account::where('code', '1101')->firstOrFail();

        $today = Carbon::create(2026, 3, 17);
        $startDate = $today->copy()->subMonth()->day(27);
        $endDate = $today->copy()->day(26);

        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dateSlug = $currentDate->format('Ymd');

            // Find an existing purchase for this day to "pay"
            $purchase = Purchase::query()
                ->where('branch_uuid', $branch->uuid)
                ->where('company_uuid', $companyUuid)
                ->where('purchase_date', $currentDate->timestamp)
                ->first();

            if ($purchase) {
                $paymentRef = "PAY-{$dateSlug}-" . strtoupper(\Illuminate\Support\Str::random(10));
                $journalDto = JournalEntity::fromArray([
                    'branch_uuid' => $branch->uuid,
                    'reference_no' => $paymentRef,
                    'reference_uuid' => $purchase->uuid,
                    'journal_date' => $currentDate->timestamp,
                    'description' => "Seeded: Pelunasan Purchase ID: {$purchase->uuid}",
                    'reference_model' => JournalReferenceModel::PURCHASE,
                    'reference_type' => JournalReferenceType::DEBT_PAYMENT,
                    'lines' => [
                        ['account_uuid' => $account2101->uuid, 'debit' => 500000, 'credit' => 0, 'contact_uuid' => $purchase->supplier_uuid],
                        ['account_uuid' => $account1101->uuid, 'debit' => 0, 'credit' => 500000, 'contact_uuid' => $purchase->supplier_uuid],
                    ]
                ]);

                $journalService->upsertJournal($journalDto);
                
                $purchase->update(['paid' => 500000, 'status' => 'partial']);
                Payable::query()
                    ->where('branch_uuid', $branch->uuid)
                    ->where('company_uuid', $companyUuid)
                    ->where('reference_uuid', $purchase->uuid)
                    ->first()
                    ?->update(['paid' => 500000, 'status' => 'partial']);
            }

            $currentDate->addDay();
        }
    }
}

