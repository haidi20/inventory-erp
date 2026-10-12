<?php

namespace Database\Seeders\V1;

use App\Modules\Finance\Ledger\Models\AccountBalance;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use Illuminate\Support\Carbon;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use Illuminate\Support\Facades\DB;
use App\Modules\Finance\Ledger\Services\GeneralLedgerService;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Services\JournalService;
use Illuminate\Database\Seeder;

class GeneralLedgerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branch = Branch::where('short_name', 'KDM')->firstOrFail();
        $journalService = app(JournalService::class);

        DB::transaction(function () use ($branch, $journalService) {
            
            // 1. Setup Closing Periods (27th to 26th cycle)
            $periods = ['2026-01', '2026-02', '2026-03'];
            foreach ($periods as $p) {
                // Peride 2026-03 start 2026-02-27 and end 2026-03-26
                $date = Carbon::parse($p . '-01');
                ClosingPeriod::updateOrCreate(
                    ['branch_uuid' => $branch->uuid, 'period' => $p],
                    [
                        'name' => $date->format('F Y'),
                        'start_date' => $date->copy()->subMonth()->setDay(27)->timestamp,
                        'end_date' => $date->copy()->setDay(26)->timestamp,
                        'status' => 'open'
                    ]
                );
            }

            // 2. Setup Accounts
            $kas = Account::where('code', '1101')->firstOrFail();
            $bank = Account::where('code', '1103')->firstOrFail();
            $modal = Account::where('code', '3100')->firstOrFail();
            $obat = Account::where('code', '1301')->firstOrFail();
            $pendapatan = Account::where('code', '4101')->firstOrFail();
            $bebanGaji = Account::where('code', '5201')->firstOrFail();
            $bebanListrik = Account::where('code', '5204')->firstOrFail();

            // 3. Opening Balances (Jan 2026)
            AccountBalance::updateOrCreate(
                ['branch_uuid' => $branch->uuid, 'account_uuid' => $kas->uuid, 'period' => 'Opening'],
                ['opening_balance' => 100000000, 'closing_balance' => 100000000]
            );
            AccountBalance::updateOrCreate(
                ['branch_uuid' => $branch->uuid, 'account_uuid' => $bank->uuid, 'period' => 'Opening'],
                ['opening_balance' => 500000000, 'closing_balance' => 500000000]
            );
            AccountBalance::updateOrCreate(
                ['branch_uuid' => $branch->uuid, 'account_uuid' => $modal->uuid, 'period' => 'Opening'],
                ['opening_balance' => 600000000, 'closing_balance' => 600000000]
            );

            // 4. Transactions - JANUARY
            // Jan 05: Modal Awal Tambahan (Cash to Modal)
            $journalService->createJournal(JournalEntity::fromArray([
                'branch_uuid' => $branch->uuid,
                'journal_date' => strtotime('2026-01-05'),
                'reference_type' => JournalReferenceType::GENERAL,
                'reference_uuid' => 101,
                'reference_no' => 'GJ-202601-001',
                'description' => 'Setoran Modal Pemilik',
                'lines' => [
                    ['account_uuid' => $bank->uuid, 'debit' => 100000000, 'credit' => 0],
                    ['account_uuid' => $modal->uuid, 'debit' => 0, 'credit' => 100000000],
                ]
            ]));

            // 5. Transactions - FEBRUARY
            // Feb 10: Pembelian Alat Medis (Bank)
            $journalService->createJournal(JournalEntity::fromArray([
                'branch_uuid' => $branch->uuid,
                'journal_date' => strtotime('2026-02-10'),
                'reference_type' => JournalReferenceType::PURCHASE,
                'reference_uuid' => 201,
                'reference_no' => 'PU-202602-001',
                'description' => 'Beli Stok Obat Besar',
                'lines' => [
                    ['account_uuid' => $obat->uuid, 'debit' => 50000000, 'credit' => 0],
                    ['account_uuid' => $bank->uuid, 'debit' => 0, 'credit' => 50000000],
                ]
            ]));

            // Feb 28: Gaji Karyawan
            $journalService->createJournal(JournalEntity::fromArray([
                'branch_uuid' => $branch->uuid,
                'journal_date' => strtotime('2026-02-28'),
                'reference_type' => JournalReferenceType::EXPENSE,
                'reference_uuid' => 202,
                'reference_no' => 'EXP-202602-001',
                'description' => 'Bayar Gaji Karyawan Feb',
                'lines' => [
                    ['account_uuid' => $bebanGaji->uuid, 'debit' => 15000000, 'credit' => 0],
                    ['account_uuid' => $kas->uuid, 'debit' => 0, 'credit' => 15000000],
                ]
            ]));

            // 6. Transactions - MARCH
            // Mar 05: Pendapatan Layanan Mar
            $journalService->createJournal(JournalEntity::fromArray([
                'branch_uuid' => $branch->uuid,
                'journal_date' => strtotime('2026-03-05'),
                'reference_type' => JournalReferenceType::SALE,
                'reference_uuid' => 301,
                'reference_no' => 'SA-202603-001',
                'description' => 'Pendapatan Pasien Umum',
                'lines' => [
                    ['account_uuid' => $kas->uuid, 'debit' => 25000000, 'credit' => 0],
                    ['account_uuid' => $pendapatan->uuid, 'debit' => 0, 'credit' => 25000000],
                ]
            ]));

            // Mar 15: Bayar Listrik Mar
            $journalService->createJournal(JournalEntity::fromArray([
                'branch_uuid' => $branch->uuid,
                'journal_date' => strtotime('2026-03-15'),
                'reference_type' => JournalReferenceType::EXPENSE,
                'reference_uuid' => 302,
                'reference_no' => 'EXP-202603-001',
                'description' => 'Bayar Tagihan Listrik PLN',
                'lines' => [
                    ['account_uuid' => $bebanListrik->uuid, 'debit' => 2500000, 'credit' => 0],
                    ['account_uuid' => $bank->uuid, 'debit' => 0, 'credit' => 2500000],
                ]
            ]));

            // Mar 20: Mutasi Bank ke Kas (Tarik Tunai)
            $journalService->createJournal(JournalEntity::fromArray([
                'branch_uuid' => $branch->uuid,
                'journal_date' => strtotime('2026-03-20'),
                'reference_type' => JournalReferenceType::GENERAL,
                'reference_uuid' => 303,
                'reference_no' => 'GJ-202603-001',
                'description' => 'Tarik Tunai Ops Kas',
                'lines' => [
                    ['account_uuid' => $kas->uuid, 'debit' => 10000000, 'credit' => 0],
                    ['account_uuid' => $bank->uuid, 'debit' => 0, 'credit' => 10000000],
                ]
            ]));

            // 7. Sync General Ledger Table
            app(GeneralLedgerService::class)->syncAllBranches();
        });
    }
}


