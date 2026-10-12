<?php

namespace Database\Seeders\V1;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Finance\Journal\Models\JournalLine;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Operational\Procurement\Models\Purchase;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAccounts();
        $this->seedPeriods();
        $this->seedJournalsFromPurchases();
    }

    private function seedAccounts(): void
    {
        $branch = Branch::where('short_name', 'KDM')->first()
            ?? Branch::where('short_name', RuntimeCompanySeeder::SANDBOX_BRANCH_SHORT_NAME)->first()
            ?? Branch::where('is_active', true)->first();
        $branchUuid = $branch?->uuid;
        $companyUuid = $branch?->company_uuid;

        $parentData = [
            // DEBET types - Assets
            ['code' => '10000', 'name' => 'KAS',                           'type' => 'asset'],
            ['code' => '11000', 'name' => 'BANK',                          'type' => 'asset'],
            ['code' => '12000', 'name' => 'PIUTANG',                       'type' => 'asset'],
            ['code' => '13000', 'name' => 'DEPOSITO',                      'type' => 'asset'],
            ['code' => '14000', 'name' => 'ASURANSI DIBAYAR DIMUKA',       'type' => 'asset'],
            ['code' => '15000', 'name' => 'PERSEDIAAN OBAT & BHP',         'type' => 'asset'],
            ['code' => '16000', 'name' => 'UANG MUKA PEMBELIAN',           'type' => 'asset'],
            ['code' => '17000', 'name' => 'PRA OPERASI',                   'type' => 'asset'],
            ['code' => '20000', 'name' => 'BANGUNAN',                      'type' => 'asset'],
            ['code' => '21000', 'name' => 'MEUBEL',                        'type' => 'asset'],
            ['code' => '22000', 'name' => 'MESIN & PERALATAN',             'type' => 'asset'],
            ['code' => '24000', 'name' => 'SOUND SYSTEM',                  'type' => 'asset'],
            ['code' => '25000', 'name' => 'PERLENGKAPAN LAIN-LAIN',        'type' => 'asset'],
            ['code' => '26000', 'name' => 'PERALATAN LABORATORIUM',        'type' => 'asset'],
            ['code' => '27000', 'name' => 'KENDARAAN',                     'type' => 'asset'],
            // KREDIT types - Accumulated
            ['code' => '28000', 'name' => 'AKUMULASI PENYUSUTAN',          'type' => 'liability'],
            ['code' => '29000', 'name' => 'AKUMULASI AMORTISASI',          'type' => 'liability'],
            // Liabilities
            ['code' => '40000', 'name' => 'HUTANG DAGANG',                 'type' => 'liability'],
            ['code' => '41000', 'name' => 'HUTANG PAJAK',                  'type' => 'liability'],
            ['code' => '42000', 'name' => 'BIAYA YANG HARUS DIBAYAR',      'type' => 'liability'],
            ['code' => '43000', 'name' => 'HUTANG BANK',                   'type' => 'liability'],
            ['code' => '44000', 'name' => 'HUTANG LAIN-LAIN',              'type' => 'liability'],
            // Equity
            ['code' => '50000', 'name' => 'MODAL DISETOR',                 'type' => 'equity'],
            ['code' => '51000', 'name' => 'LABA DITAHAN',                  'type' => 'equity'],
            ['code' => '52000', 'name' => 'LABA/RUGI BERJALAN',            'type' => 'equity'],
            // Revenue (KREDIT)
            ['code' => '60000', 'name' => 'PENDAPATAN JASA MEDIS',         'type' => 'revenue'],
            ['code' => '61000', 'name' => 'PENDAPATAN OBAT & FARMASI',     'type' => 'revenue'],
            ['code' => '62000', 'name' => 'PENDAPATAN LABORATORIUM',       'type' => 'revenue'],
            ['code' => '63000', 'name' => 'PENDAPATAN LAIN-LAIN',          'type' => 'revenue'],
            // Expense (DEBET)
            ['code' => '70000', 'name' => 'BIAYA OBAT & BHP',              'type' => 'expense'],
            ['code' => '71000', 'name' => 'BIAYA GAJI & TUNJANGAN',        'type' => 'expense'],
            ['code' => '72000', 'name' => 'BIAYA OPERASIONAL',             'type' => 'expense'],
            ['code' => '73000', 'name' => 'BIAYA PEMASARAN',               'type' => 'expense'],
            ['code' => '74000', 'name' => 'BIAYA ADMINISTRASI',            'type' => 'expense'],
            ['code' => '75000', 'name' => 'BIAYA PENYUSUTAN',              'type' => 'expense'],
            ['code' => '79000', 'name' => 'BIAYA LAIN-LAIN',               'type' => 'expense'],
        ];

        $parents = [];
        foreach ($parentData as $data) {
            $account = Account::updateOrCreate(
                ['code' => $data['code']],
                [
                    'name' => $data['name'],
                    'type' => $data['type'],
                    'branch_uuid' => $branchUuid,
                    'company_uuid' => $companyUuid,
                    'parent_uuid' => null,
                    'is_active' => true,
                    'is_header' => false,
                ]
            );
            $parents[$data['code']] = $account->uuid;
        }

        $subData = [
            // Under 10000 KAS
            ['code' => '10001', 'name' => 'KAS KANTOR',                             'parent' => '10000'],
            ['code' => '10002', 'name' => 'KAS APOTEK',                             'parent' => '10000'],
            ['code' => '10003', 'name' => 'KAS LABORATORIUM',                       'parent' => '10000'],
            ['code' => '10004', 'name' => 'KAS BASUKI RAHMAD',                      'parent' => '10000'],
            ['code' => '10005', 'name' => 'KAS MERDEKA',                            'parent' => '10000'],
            // Under 11000 BANK
            ['code' => '11001', 'name' => 'BANK BCA GIRO 027-183.5051',             'parent' => '11000'],
            ['code' => '11002', 'name' => 'BANK BRI Rek. 0671-0107-3343-313',      'parent' => '11000'],
            ['code' => '11003', 'name' => 'BANK COMMONWEALTH Rek. 278-000-4952',   'parent' => '11000'],
            ['code' => '11005', 'name' => 'BANK DBS Rek. 209-033-883',             'parent' => '11000'],
            // Under 12000 PIUTANG
            ['code' => '12001', 'name' => 'PIUTANG BI',                             'parent' => '12000'],
            ['code' => '12003', 'name' => 'PIUTANG ASKES',                          'parent' => '12000'],
            ['code' => '12004', 'name' => 'HBS',                                    'parent' => '12000'],
            ['code' => '12006', 'name' => 'CC BCA',                                 'parent' => '12000'],
            ['code' => '12007', 'name' => 'AGE',                                    'parent' => '12000'],
            ['code' => '12008', 'name' => 'LAIN-LAIN',                              'parent' => '12000'],
            ['code' => '12020', 'name' => 'PIUTANG BPJS',                           'parent' => '12000'],
            ['code' => '12021', 'name' => 'PIUTANG ASKES JAMSOSTEK',                'parent' => '12000'],
            // Under 15000 PERSEDIAAN
            ['code' => '15001', 'name' => 'OBAT',                                   'parent' => '15000'],
            ['code' => '15002', 'name' => 'BHP',                                    'parent' => '15000'],
            ['code' => '15003', 'name' => 'SUPLEMEN',                               'parent' => '15000'],
            // Under 40000 HUTANG DAGANG
            ['code' => '40001', 'name' => 'PT. ANUGRAH ARGON MEDICA',              'parent' => '40000'],
            ['code' => '40002', 'name' => 'PT. MILLENIUM PHARMACON INTERNASIONAL', 'parent' => '40000'],
            ['code' => '40003', 'name' => 'PT. KEBAYORAN PHARMA',                  'parent' => '40000'],
            ['code' => '40004', 'name' => 'PT. PENTA VALENT',                      'parent' => '40000'],
            ['code' => '40005', 'name' => 'PT. KALLISTA PRIMA',                    'parent' => '40000'],
            ['code' => '40006', 'name' => 'PT. TEMPO SCAN',                        'parent' => '40000'],
            ['code' => '40007', 'name' => 'PT. APL',                               'parent' => '40000'],
            ['code' => '40008', 'name' => 'PT. BINA SAN PRIMA',                    'parent' => '40000'],
            ['code' => '40009', 'name' => 'PT. PARIT PADANG',                      'parent' => '40000'],
            ['code' => '40010', 'name' => 'PT. KIMIA FARMA',                       'parent' => '40000'],
            ['code' => '40011', 'name' => 'PT. ENSEVAL PUTERA',                    'parent' => '40000'],
            ['code' => '40012', 'name' => 'PT. ANTARMITRA SEMBADA',                'parent' => '40000'],
            ['code' => '40013', 'name' => 'PT. MENSA BINA SUKSES',                 'parent' => '40000'],
            ['code' => '40014', 'name' => 'PT. METTA KARYA HUTAMA',                'parent' => '40000'],
            ['code' => '40015', 'name' => 'PT. MUSTANG FARMA JAYA',                'parent' => '40000'],
            ['code' => '40099', 'name' => 'HUTANG SUPPLIER LAIN-LAIN',             'parent' => '40000'],
            // Under 42000 BIAYA YANG HARUS DIBAYAR
            ['code' => '42001', 'name' => 'GAJI',                                   'parent' => '42000'],
            ['code' => '42002', 'name' => 'INSENTIF DOKTER',                        'parent' => '42000'],
            ['code' => '42003', 'name' => 'JAMSOSTEK',                              'parent' => '42000'],
            ['code' => '42004', 'name' => 'DOKTER',                                 'parent' => '42000'],
            ['code' => '42005', 'name' => 'JAMSOSTEK',                              'parent' => '42000'],
            ['code' => '42006', 'name' => 'INSENTIF',                               'parent' => '42000'],
            ['code' => '42007', 'name' => 'DOKTER FEE',                             'parent' => '42000'],
            // Under 60000 PENDAPATAN JASA
            ['code' => '60001', 'name' => 'PENDAPATAN KONSULTASI DOKTER',           'parent' => '60000'],
            ['code' => '60002', 'name' => 'PENDAPATAN TINDAKAN MEDIS',              'parent' => '60000'],
            ['code' => '60003', 'name' => 'PENDAPATAN RAWAT JALAN',                 'parent' => '60000'],
            // Under 61000 PENDAPATAN OBAT
            ['code' => '61001', 'name' => 'PENJUALAN OBAT KERAS',                   'parent' => '61000'],
            ['code' => '61002', 'name' => 'PENJUALAN BHP',                          'parent' => '61000'],
            ['code' => '61003', 'name' => 'PENJUALAN SUPLEMEN',                     'parent' => '61000'],
            // Under 70000 BIAYA OBAT
            ['code' => '70001', 'name' => 'HPP OBAT KERAS',                         'parent' => '70000'],
            ['code' => '70002', 'name' => 'HPP BHP',                                'parent' => '70000'],
            // Under 71000 BIAYA GAJI
            ['code' => '71001', 'name' => 'GAJI DOKTER',                            'parent' => '71000'],
            ['code' => '71002', 'name' => 'GAJI PERAWAT',                           'parent' => '71000'],
            ['code' => '71003', 'name' => 'GAJI APOTEKER',                          'parent' => '71000'],
            ['code' => '71004', 'name' => 'GAJI ADMIN',                             'parent' => '71000'],
            // Under 72000 BIAYA OPERASIONAL
            ['code' => '72001', 'name' => 'LISTRIK & AIR',                          'parent' => '72000'],
            ['code' => '72002', 'name' => 'TELEPON & INTERNET',                     'parent' => '72000'],
            ['code' => '72003', 'name' => 'SEWA GEDUNG',                            'parent' => '72000'],
            ['code' => '72004', 'name' => 'BIAYA KEBERSIHAN',                       'parent' => '72000'],
        ];

        foreach ($subData as $data) {
            $parentCode = $data['parent'];
            $parentId = $parents[$parentCode] ?? null;
            $parentType = collect($parentData)->firstWhere('code', $parentCode)['type'] ?? 'asset';
            Account::updateOrCreate(
                ['code' => $data['code']],
                [
                    'name' => $data['name'],
                    'type' => $parentType,
                    'branch_uuid' => $branchUuid,
                    'company_uuid' => $companyUuid,
                    'parent_uuid' => $parentId,
                    'is_active' => true,
                    'is_header' => false,
                ]
            );
        }

        // Add 4-digit codes for Journal Simulations compatibility
        $compatAccounts = [
            ['code' => '1101', 'name' => 'Kas Utama', 'type' => 'asset'],
            ['code' => '1201', 'name' => 'Piutang Pasien', 'type' => 'asset'],
            ['code' => '1301', 'name' => 'Persediaan Obat', 'type' => 'asset'],
            ['code' => '2101', 'name' => 'Hutang Dagang', 'type' => 'liability'],
            ['code' => '4101', 'name' => 'Pendapatan Jasa', 'type' => 'revenue'],
            ['code' => '4102', 'name' => 'Pendapatan Obat', 'type' => 'revenue'],
            ['code' => '5101', 'name' => 'HPP Obat', 'type' => 'expense'],
            ['code' => '5204', 'name' => 'Biaya Listrik', 'type' => 'expense'],
            ['code' => '5205', 'name' => 'Biaya Air', 'type' => 'expense'],
        ];
        foreach ($compatAccounts as $acc) {
            Account::updateOrCreate(
                ['code' => $acc['code']],
                $acc + ['branch_uuid' => $branchUuid, 'company_uuid' => $companyUuid, 'is_active' => true, 'is_header' => false]
            );
        }

        $this->command->info('Chart of Accounts seeded: '.Account::count().' accounts.');
    }

    private function seedPeriods(): void
    {
        ClosingPeriod::withoutEvents(function (): void {
            $this->createSeedPeriods();
        });
    }

    private function createSeedPeriods(): void
    {
        $branchId = Branch::where('short_name', 'KDM')->value('uuid')
            ?? Branch::where('short_name', RuntimeCompanySeeder::SANDBOX_BRANCH_SHORT_NAME)->value('uuid')
            ?? Branch::where('is_active', true)->value('uuid');

        if (! $branchId) {
            $this->command->warn('No active branch found, skipping closing period seed.');

            return;
        }

        $periods = ['2026-01', '2026-02', '2026-03'];
        foreach ($periods as $period) {
            $carbon = Carbon::createFromFormat('Y-m', $period);
            ClosingPeriod::firstOrCreate(
                ['branch_uuid' => $branchId, 'period' => $period],
                [
                    'status' => 'open',
                    'closed_at' => null,
                    'start_date' => $carbon->copy()->startOfMonth()->timestamp,
                    'end_date' => $carbon->copy()->endOfMonth()->timestamp,
                ]
            );
        }
        $this->command->info('Closing periods seeded: Jan-Mar 2026.');
    }

    private function seedJournalsFromPurchases(): void
    {
        $persediaan = Account::where('code', '15000')->value('uuid');
        $hutangDagang = Account::where('code', '40000')->value('uuid');

        if (! $persediaan || ! $hutangDagang) {
            $this->command->warn('Required accounts (15000/40000) not found, skipping journal seed.');

            return;
        }

        $purchases = Purchase::with('supplier')->get();

        $count = 0;
        foreach ($purchases as $p) {
            if (Journal::where('reference_type', 'purchase')->where('reference_uuid', $p->uuid)->exists()) {
                continue;
            }

            DB::transaction(function () use ($p, $persediaan, $hutangDagang, &$count) {
                $uuid = (string) Str::uuid();
                $branchShortName = Branch::where('uuid', $p->branch_uuid)->value('short_name') ?? 'GEN';
                $transactionCode = $branchShortName.'/PUR/'.date('Ym', $p->purchase_date).'/'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);

                Journal::create([
                    'uuid' => $uuid,
                    'branch_uuid' => $p->branch_uuid,
                    'company_uuid' => $p->company_uuid,
                    'transaction_code' => $transactionCode,
                    'reference_no' => 'JRN-'.$p->invoice_no,
                    'reference_type' => 'purchase',
                    'reference_uuid' => $p->uuid,
                    'journal_date' => $p->purchase_date,
                    'description' => 'Hutang Pembelian - '.($p->supplier?->name ?? 'Supplier'),
                    'status' => 'posted',
                    'created_at' => time(),
                    'updated_at' => time(),
                ]);

                JournalLine::create([
                    'uuid' => (string) Str::uuid(),
                    'journal_uuid' => $uuid,
                    'branch_uuid' => $p->branch_uuid,
                    'company_uuid' => $p->company_uuid,
                    'account_uuid' => $persediaan,
                    'debit' => $p->total,
                    'credit' => 0,
                    'created_at' => time(),
                    'updated_at' => time(),
                ]);

                JournalLine::create([
                    'uuid' => (string) Str::uuid(),
                    'journal_uuid' => $uuid,
                    'branch_uuid' => $p->branch_uuid,
                    'company_uuid' => $p->company_uuid,
                    'account_uuid' => $hutangDagang,
                    'debit' => 0,
                    'credit' => $p->total,
                    'created_at' => time(),
                    'updated_at' => time(),
                ]);

                $count++;
            });
        }

        $this->command->info("Journals from purchases seeded: {$count} entries.");
    }
}
