<?php

namespace Database\Seeders\V1;

use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Journal\Services\JournalService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class JournalSimulationExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $journalService = app(JournalService::class);
        $branch = Branch::where('short_name', 'KDM')->firstOrFail();

        $account5204 = Account::where('code', '5204')->firstOrFail();
        $account5205 = Account::where('code', '5205')->firstOrFail();
        $account1101 = Account::where('code', '1101')->firstOrFail();

        $today = Carbon::create(2026, 3, 17);
        $startDate = $today->copy()->subMonth()->day(27);
        $endDate = $today->copy()->day(26);

        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dateSlug = $currentDate->format('Ymd');

            $expenseRef = "EXP-{$dateSlug}-" . strtoupper(\Illuminate\Support\Str::random(10));
            $journalDto = JournalEntity::fromArray([
                'branch_uuid' => $branch->uuid,
                'reference_no' => $expenseRef,
                'reference_uuid' => 0,
                'journal_date' => $currentDate->timestamp,
                'description' => "Seeded: Biaya Listrik & Air - {$dateSlug}",
                'reference_model' => null,
                'reference_type' => JournalReferenceType::EXPENSE,
                'lines' => [
                    ['account_uuid' => $account5204->uuid, 'debit' => 30000, 'credit' => 0],
                    ['account_uuid' => $account5205->uuid, 'debit' => 20000, 'credit' => 0],
                    ['account_uuid' => $account1101->uuid, 'debit' => 0, 'credit' => 50000],
                ]
            ]);

            $journalService->upsertJournal($journalDto);

            $currentDate->addDay();
        }
    }
}

