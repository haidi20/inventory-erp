<?php

namespace Database\Seeders\V1;

use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Finance\Journal\Models\JournalLine;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Operational\Procurement\Models\Purchase;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExcelAkutansiSeeder extends Seeder
{
    // Parent accounts from Neraca 1225.xls (PT. Sentra Medika Utama Desember 2025)
    private array $parentData = [
        ['code' => '10000', 'name' => 'KAS',                          'type' => 'asset'],
        ['code' => '11000', 'name' => 'BANK',                         'type' => 'asset'],
        ['code' => '12000', 'name' => 'PIUTANG',                      'type' => 'asset'],
        ['code' => '13000', 'name' => 'DEPOSITO',                     'type' => 'asset'],
        ['code' => '14000', 'name' => 'ASURANSI DIBAYAR DIMUKA',      'type' => 'asset'],
        ['code' => '15000', 'name' => 'PERSEDIAAN',                   'type' => 'asset'],
        ['code' => '16000', 'name' => 'UANG MUKA PEMBELIAN',          'type' => 'asset'],
        ['code' => '17000', 'name' => 'PRA OPERASI',                  'type' => 'asset'],
        ['code' => '20000', 'name' => 'BANGUNAN',                     'type' => 'asset'],
        ['code' => '21000', 'name' => 'MEUBEL',                       'type' => 'asset'],
        ['code' => '22000', 'name' => 'MESIN & PERALATAN',            'type' => 'asset'],
        ['code' => '24000', 'name' => 'SOUND SYSTEM',                 'type' => 'asset'],
        ['code' => '25000', 'name' => 'PERLENGKAPAN LAIN-LAIN',       'type' => 'asset'],
        ['code' => '26000', 'name' => 'PERALATAN LABORATORIUM',       'type' => 'asset'],
        ['code' => '27000', 'name' => 'KENDARAAN',                    'type' => 'asset'],
        ['code' => '28000', 'name' => 'AKUMULASI PENYUSUTAN',         'type' => 'liability'],
        ['code' => '29000', 'name' => 'AKUMULASI AMORTISASI',         'type' => 'liability'],
        ['code' => '40000', 'name' => 'HUTANG DAGANG',                'type' => 'liability'],
        ['code' => '41000', 'name' => 'HUTANG PAJAK',                 'type' => 'liability'],
        ['code' => '42000', 'name' => 'BIAYA YANG HARUS DIBAYAR',     'type' => 'liability'],
        ['code' => '50000', 'name' => 'MODAL',                        'type' => 'equity'],
        ['code' => '55000', 'name' => 'LABA / RUGI',                  'type' => 'equity'],
        ['code' => '60000', 'name' => 'PENDAPATAN',                   'type' => 'revenue'],
        ['code' => '70000', 'name' => 'APOTEK',                       'type' => 'expense'],
        ['code' => '71000', 'name' => 'LABORATORIUM',                 'type' => 'expense'],
        ['code' => '72000', 'name' => 'CAFE',                         'type' => 'expense'],
        ['code' => '79000', 'name' => 'UMUM & ADMINISTRASI',          'type' => 'expense'],
        ['code' => '80000', 'name' => 'PENDAPATAN LAIN',              'type' => 'revenue'],
    ];

    public function run(): void
    {
        $this->command->info('=== ExcelAkutansiSeeder: Import Saldo Awal Des 2025 ===');

        // 1. Clear accounting data in FK-safe order
        JournalLine::query()->delete();
        Journal::query()->delete();
        Account::whereNotNull('parent_uuid')->delete(); // sub-accounts first
        Account::whereNull('parent_uuid')->delete();    // then parents
        ClosingPeriod::query()->delete();
        $this->command->info('Cleared: journal_lines, journals, accounts, closing_periods');

        // 2. Seed parent accounts
        $parents = $this->seedParents();

        // 3. Seed sub-accounts from Excel JSON
        $this->seedSubAccounts($parents);

        // 4. Seed closing periods
        $this->seedPeriods();

        // 5. Create Saldo Awal journal for 2025-12
        $this->seedSaldoAwal();

        // 6. Recreate journals from purchases
        $this->seedJournalsFromPurchases();

        $this->command->info('=== Done: '.Account::count().' accounts, '.Journal::count().' journals ===');
    }

    private function seedParents(): array
    {
        $parentTypeMap = array_column($this->parentData, 'type', 'code');
        $parents = [];
        foreach ($this->parentData as $data) {
            $account = Account::firstOrCreate(
                ['code' => $data['code']],
                [
                    'name' => $data['name'],
                    'type' => $data['type'],
                    'parent_uuid' => null,
                    'is_active' => true,
                ]
            );
            $parents[$data['code']] = ['uuid' => $account->uuid, 'type' => $data['type']];
        }
        $this->command->info('Parent accounts: '.count($parents));

        return $parents;
    }

    private function seedSubAccounts(array $parents): void
    {
        $jsonPath = database_path('data/excel_akutansi_saldo.json');
        if (! file_exists($jsonPath)) {
            $this->command->error("JSON not found: $jsonPath");

            return;
        }

        $subAccounts = json_decode(file_get_contents($jsonPath), true);
        $count = 0;
        foreach ($subAccounts as $data) {
            $parentInfo = $parents[$data['parent']] ?? null;
            if (! $parentInfo) {
                $this->command->warn("No parent for code {$data['code']} (parent {$data['parent']})");

                continue;
            }
            Account::firstOrCreate(
                ['code' => $data['code']],
                [
                    'name' => $data['name'],
                    'type' => $parentInfo['type'],
                    'parent_uuid' => $parentInfo['uuid'],
                    'is_active' => true,
                ]
            );
            $count++;
        }
        $this->command->info("Sub-accounts created: $count");
    }

    private function seedPeriods(): void
    {
        ClosingPeriod::withoutEvents(function () {
            $periods = ['2025-12', '2026-01', '2026-02', '2026-03'];
            foreach ($periods as $period) {
                ClosingPeriod::create([
                    'period' => $period,
                    'status' => $period === '2025-12' ? 'closed' : 'open',
                    'closed_at' => $period === '2025-12' ? '2025-12-31' : null,
                ]);
            }
        });
        $this->command->info('Closing periods seeded: 2025-12 (closed), 2026-01..03 (open)');
    }

    private function seedSaldoAwal(): void
    {
        $jsonPath = database_path('data/excel_akutansi_saldo.json');
        $subAccounts = json_decode(file_get_contents($jsonPath), true);

        // Build account code → id map
        $accountMap = Account::whereNotNull('parent_uuid')
            ->pluck('uuid', 'code')
            ->toArray();

        // Build journal lines
        $lines = [];
        foreach ($subAccounts as $data) {
            $accountId = $accountMap[$data['code']] ?? null;
            if (! $accountId) {
                continue;
            }

            $salD = $data['sal_d'];
            $salK = $data['sal_k'];

            if ($salD > 0) {
                $lines[] = ['account_uuid' => $accountId, 'debit' => $salD,  'credit' => 0];
            } elseif ($salK < 0) {
                // Negative credit → flip to debit
                $lines[] = ['account_uuid' => $accountId, 'debit' => abs($salK), 'credit' => 0];
            } elseif ($salK > 0) {
                $lines[] = ['account_uuid' => $accountId, 'debit' => 0, 'credit' => $salK];
            }
        }

        if (empty($lines)) {
            $this->command->warn('No saldo awal lines to insert.');

            return;
        }

        $totalD = array_sum(array_column($lines, 'debit'));
        $totalK = array_sum(array_column($lines, 'credit'));

        $this->command->info(sprintf(
            'Saldo Awal lines: %d | Total Debit: %s | Total Kredit: %s | Balanced: %s',
            count($lines),
            number_format($totalD, 0, ',', '.'),
            number_format($totalK, 0, ',', '.'),
            $totalD === $totalK ? 'YES' : 'NO (diff: '.number_format(abs($totalD - $totalK), 0, ',', '.').')'
        ));

        DB::transaction(function () use ($lines) {
            $journalUuid = (string) Str::uuid();
            Journal::create([
                'uuid' => $journalUuid,
                'reference_no' => 'SA-2025-12',
                'reference_type' => 'saldo_awal',
                'reference_uuid' => null,
                'journal_date' => strtotime('2025-12-31'),
                'description' => 'Saldo Awal - Neraca Percobaan Desember 2025',
                'status' => 'posted',
                'created_at' => time(),
                'updated_at' => time(),
            ]);

            $now = time();
            $batch = [];
            foreach ($lines as $line) {
                $batch[] = [
                    'uuid' => (string) Str::uuid(),
                    'journal_uuid' => $journalUuid,
                    'account_uuid' => $line['account_uuid'],
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            // Insert in chunks to avoid query size limits
            foreach (array_chunk($batch, 100) as $chunk) {
                JournalLine::insert($chunk);
            }
        });

        $this->command->info('Saldo Awal journal (SA-2025-12) created.');
    }

    private function seedJournalsFromPurchases(): void
    {
        $persediaan = Account::where('code', '15000')->value('uuid');
        $hutangDagang = Account::where('code', '40000')->value('uuid');

        if (! $persediaan || ! $hutangDagang) {
            $this->command->warn('Accounts 15000/40000 not found, skipping purchase journals.');

            return;
        }

        $purchases = Purchase::with('supplier')->get();

        $count = 0;
        foreach ($purchases as $p) {
            if (Journal::where('reference_type', 'purchase')
                ->where('reference_type', 'purchase')
                ->where('reference_uuid', $p->uuid)
                ->exists()) {
                continue;
            }

            DB::transaction(function () use ($p, $persediaan, $hutangDagang, &$count) {
                $journalUuid = (string) Str::uuid();
                Journal::create([
                    'uuid' => $journalUuid,
                    'reference_no' => 'JRN-'.$p->invoice_no,
                    'reference_type' => 'purchase',
                    'reference_uuid' => $p->uuid,
                    'journal_date' => $p->purchase_date,
                    'description' => 'Pembelian - '.($p->supplier?->name ?? 'Supplier'),
                    'status' => 'posted',
                    'created_at' => time(),
                    'updated_at' => time(),
                ]);

                JournalLine::insert([
                    ['uuid' => (string) Str::uuid(), 'journal_uuid' => $journalUuid, 'account_uuid' => $persediaan,   'debit' => $p->total, 'credit' => 0,        'created_at' => time(), 'updated_at' => time()],
                    ['uuid' => (string) Str::uuid(), 'journal_uuid' => $journalUuid, 'account_uuid' => $hutangDagang, 'debit' => 0,         'credit' => $p->total, 'created_at' => time(), 'updated_at' => time()],
                ]);
                $count++;
            });
        }

        $this->command->info("Purchase journals created: $count");
    }
}
