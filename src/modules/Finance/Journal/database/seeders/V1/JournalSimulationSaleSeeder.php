<?php

namespace Database\Seeders\V1;

use App\Modules\Finance\Journal\Enums\JournalReferenceModel;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Operational\Master\Models\Employee;
use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Operational\Sales\Models\Sale;
use App\Modules\Finance\Journal\Services\JournalService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class JournalSimulationSaleSeeder extends Seeder
{
    public function run(): void
    {
        $journalService = app(JournalService::class);
        $branch = Branch::where('short_name', 'KDM')->firstOrFail();
        $companyUuid = $branch->company_uuid;

        $account1101 = Account::where('code', '1101')->firstOrFail();
        $account4101 = Account::where('code', '4101')->firstOrFail();
        $account4102 = Account::where('code', '4102')->firstOrFail();
        $account5101 = Account::where('code', '5101')->firstOrFail();
        $account1301 = Account::where('code', '1301')->firstOrFail();

        $today = Carbon::create(2026, 3, 17);
        $startDate = $today->copy()->subMonth()->day(27);
        $endDate = $today->copy()->day(26);

        // Buat record ClosingPeriod untuk simulasi agar bisa dibaca dari Test
        // Transaksi loop dari akhir Feb (27) sampai akhir Mar (26), jadi 2 period perlu di-seed
        ClosingPeriod::withoutEvents(function () use ($branch, $startDate, $endDate) {
            ClosingPeriod::updateOrCreate(
                ['branch_uuid' => $branch->uuid, 'period' => '2026-02'],
                [
                    'name' => 'Februari 2026',
                    'start_date' => Carbon::create(2026, 2, 1)->startOfMonth()->timestamp,
                    'end_date' => Carbon::create(2026, 2, 1)->endOfMonth()->timestamp,
                    'status' => 'open'
                ]
            );
            ClosingPeriod::updateOrCreate(
                ['branch_uuid' => $branch->uuid, 'period' => '2026-03'],
                [
                    'name' => 'Maret 2026',
                    'start_date' => $startDate->timestamp,
                    'end_date' => $endDate->timestamp,
                    'status' => 'open'
                ]
            );
        });

        $patient = Patient::query()->where('company_uuid', $companyUuid)->first();
        $cashier = Employee::query()
            ->where('company_uuid', $companyUuid)
            ->where('role', 'kasir')
            ->first();

        if (!$patient || !$cashier) {
            $this->command->error("Patient or Cashier not found for Sale Seeder.");
            return;
        }

        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dateSlug = $currentDate->format('Ymd');

            // Standard Sales (1-5 per day)
            $journalsData = [];
            for ($i = 1; $i <= 5; $i++) {
                $invoiceNo = "INV-{$dateSlug}-" . str_pad($i, 3, '0', STR_PAD_LEFT) . "-" . strtoupper(\Illuminate\Support\Str::random(10));
                
                $sale = Sale::updateOrCreate(
                    ['branch_uuid' => $branch->uuid, 'sale_no' => $invoiceNo],
                    [
                        'company_uuid' => $companyUuid,
                        'patient_uuid' => $patient->uuid,
                        'cashier_uuid' => $cashier->uuid,
                        'total_amount' => 100000,
                        'paid' => 100000,
                        'payment_status' => 'paid',
                        'sale_at' => $currentDate->copy()->hour(10)->minute($i)->timestamp,
                        'created_at' => $currentDate->copy()->hour(10)->minute($i)->timestamp,
                    ]
                );

                $journalDto = [
                    'branch_uuid' => $branch->uuid,
                    'reference_no' => $invoiceNo,
                    'reference_uuid' => $sale->uuid,
                    'journal_date' => $currentDate->timestamp,
                    'description' => "Transaksi Klinik {$i} - Sale ID: {$sale->uuid}",
                    'reference_model' => JournalReferenceModel::SALE,
                    'reference_type' => JournalReferenceType::SALE,
                    'lines' => [
                        ['account_uuid' => $account1101->uuid, 'debit' => 100000, 'credit' => 0],
                        ['account_uuid' => $account4102->uuid, 'debit' => 0, 'credit' => 100000],
                    ]
                ];
                $journalService->upsertJournal(JournalEntity::fromArray($journalDto));
            }

            // Complex Sale
            $complexInvoiceNo = "INV-CPX-{$dateSlug}-99-" . strtoupper(\Illuminate\Support\Str::random(10));
            $complexSale = Sale::updateOrCreate(
                ['branch_uuid' => $branch->uuid, 'sale_no' => $complexInvoiceNo],
                [
                    'company_uuid' => $companyUuid,
                    'patient_uuid' => $patient->uuid,
                    'cashier_uuid' => $cashier->uuid,
                    'total_amount' => 150000,
                    'paid' => 150000,
                    'payment_status' => 'paid',
                    'sale_at' => $currentDate->copy()->hour(11)->timestamp,
                    'created_at' => $currentDate->copy()->hour(11)->timestamp,
                ]
            );

            $journalService->upsertJournal(JournalEntity::fromArray([
                'branch_uuid' => $branch->uuid,
                'reference_no' => $complexInvoiceNo . '-REV',
                'reference_uuid' => $complexSale->uuid,
                'journal_date' => $currentDate->timestamp,
                'description' => "Seeded: Pendapatan Sale ID: {$complexSale->uuid}",
                'reference_model' => JournalReferenceModel::SALE,
                'reference_type' => JournalReferenceType::SALE,
                'lines' => [
                    ['account_uuid' => $account1101->uuid, 'debit' => 150000, 'credit' => 0],
                    ['account_uuid' => $account4101->uuid, 'debit' => 0, 'credit' => 100000],
                    ['account_uuid' => $account4102->uuid, 'debit' => 0, 'credit' => 50000],
                ]
            ]));

            $journalService->upsertJournal(JournalEntity::fromArray([
                'branch_uuid' => $branch->uuid,
                'reference_no' => $complexInvoiceNo . '-COST',
                'reference_uuid' => $complexSale->uuid,
                'journal_date' => $currentDate->timestamp,
                'description' => "Seeded: HPP Sale ID: {$complexSale->uuid}",
                'reference_model' => JournalReferenceModel::SALE,
                'reference_type' => JournalReferenceType::SALE,
                'lines' => [
                    ['account_uuid' => $account5101->uuid, 'debit' => 30000, 'credit' => 0],
                    ['account_uuid' => $account1301->uuid, 'debit' => 0, 'credit' => 30000],
                ]
            ]));

            $currentDate->addDay();
        }
    }
}

