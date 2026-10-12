<?php

namespace Database\Seeders\V1;

use App\Modules\Finance\Journal\Enums\JournalReferenceModel;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Clinical\MedicalRecord\Models\Encounter;
use App\Modules\Finance\Ledger\Models\Receivable;
use App\Modules\Finance\Journal\Services\JournalService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class JournalSimulationReceivableCollectionSeeder extends Seeder
{
    public function run(): void
    {
        $journalService = app(JournalService::class);
        $branch = Branch::where('short_name', 'KDM')->firstOrFail();
        $companyUuid = $branch->company_uuid;

        $account1101 = Account::where('code', '1101')->firstOrFail();
        $account1201 = Account::where('code', '1201')->firstOrFail();

        $today = Carbon::create(2026, 3, 17);
        $startDate = $today->copy()->subMonth()->day(27);
        $endDate = $today->copy()->day(26);

        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dateSlug = $currentDate->format('Ymd');

            // Find an existing encounter for this day to "collect"
            $encounter = Encounter::query()
                ->where('branch_uuid', $branch->uuid)
                ->where('company_uuid', $companyUuid)
                ->whereBetween('created_at', [
                    $currentDate->copy()->startOfDay()->timestamp,
                    $currentDate->copy()->endOfDay()->timestamp
                ])
                ->first();

            if ($encounter) {
                $collectionRef = "COL-{$dateSlug}-" . strtoupper(\Illuminate\Support\Str::random(10));
                $journalDto = JournalEntity::fromArray([
                    'branch_uuid' => $branch->uuid,
                    'reference_no' => $collectionRef,
                    'reference_uuid' => $encounter->uuid,
                    'journal_date' => $currentDate->timestamp,
                    'description' => "Seeded: Pelunasan Encounter ID: {$encounter->uuid}",
                    'reference_model' => JournalReferenceModel::ENCOUNTER,
                    'reference_type' => JournalReferenceType::RECEIVABLE_COLLECTION,
                    'lines' => [
                        ['account_uuid' => $account1101->uuid, 'debit' => 150000, 'credit' => 0, 'contact_uuid' => $encounter->patient_uuid],
                        ['account_uuid' => $account1201->uuid, 'debit' => 0, 'credit' => 150000, 'contact_uuid' => $encounter->patient_uuid],
                    ]
                ]);

                $journalService->upsertJournal($journalDto);

                Receivable::query()
                    ->where('branch_uuid', $branch->uuid)
                    ->where('company_uuid', $companyUuid)
                    ->where('reference_uuid', $encounter->uuid)
                    ->first()
                    ?->update(['paid' => 150000, 'status' => 'partial']);
            }

            $currentDate->addDay();
        }
    }
}

