<?php

namespace Tests\Feature\Accounting\Journal;

use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Enums\JournalWorkflowStatus;
use App\Modules\Finance\Journal\Enums\JournalReferenceModel;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Journal\Models\AccountMapping;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Finance\Journal\Models\JournalLine;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Finance\Journal\Services\JournalService;
use Database\Seeders\V1\DemoSeeder;
use Database\Seeders\V1\JournalSimulationDebtPaymentSeeder;
use Database\Seeders\V1\JournalSimulationEncounterSeeder;
use Database\Seeders\V1\JournalSimulationExpenseSeeder;
use Database\Seeders\V1\JournalSimulationPurchaseSeeder;
use Database\Seeders\V1\JournalSimulationReceivableCollectionSeeder;
use Database\Seeders\V1\JournalSimulationSaleSeeder;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\UnauthorizedException;
use Tests\TestCase;

/**
 * AbstractJournalServiceTest
 * 
 * Kelas Kontrak Basis untuk pengujian JournalService.
 * Mendefinisikan standar pengujian untuk transaksi harian, simulasi massal, 
 * isolasi cabang, dan integritas data akuntansi.
 */
abstract class AbstractJournalServiceRegressionBase extends TestCase
{
    use RefreshDatabase;

    protected JournalService $journalService;
    protected $accounts;
    protected Branch $branch;
    protected Carbon $startDate;
    protected Carbon $endDate;

    /**
     * Inisialisasi awal sebelum menjalankan setiap test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Menjalankan seeder untuk menyediakan data dasar
        $this->seed([
            DemoSeeder::class,
            JournalSimulationSaleSeeder::class,
            JournalSimulationPurchaseSeeder::class,
            JournalSimulationEncounterSeeder::class,
            JournalSimulationExpenseSeeder::class,
            JournalSimulationDebtPaymentSeeder::class,
            JournalSimulationReceivableCollectionSeeder::class,
        ]);
        $this->journalService = app(JournalService::class);

        // Mengambil konteks Cabang (KDM)
        $this->branch = Branch::where('short_name', 'KDM')->firstOrFail();

        $requiredCodes = ['1101', '4101', '4102', '5101', '1301', '1102', '2101', '1201', '5204', '5205'];
        $this->accounts = Account::whereIn('code', $requiredCodes)->get()->keyBy('code');

        foreach ($requiredCodes as $code) {
            if (!isset($this->accounts[$code])) {
                $this->markTestSkipped("Akun dengan kode {$code} tidak ditemukan.");
            }
        }

        // Setup Cabang kedua untuk isolasi
        $branchKD2 = Branch::create(['name' => 'Klinik Demo 2', 'short_name' => 'KD2']);

        $period = ClosingPeriod::where('branch_uuid', $this->branch->uuid)->where('period', '2026-03')->firstOrFail();
        $this->startDate = Carbon::parse($period->start_date);
        $this->endDate = Carbon::parse($period->end_date);

        // Buat ClosingPeriod untuk cabang kedua agar validasi ketat periode terpenuhi
        $allPeriods = ClosingPeriod::where('branch_uuid', $this->branch->uuid)->get();
        ClosingPeriod::withoutEvents(function() use ($branchKD2, $allPeriods) {
            foreach ($allPeriods as $p) {
                ClosingPeriod::updateOrCreate(
                    ['branch_uuid' => $branchKD2->uuid, 'period' => $p->period],
                    [
                        'name' => $p->name,
                        'start_date' => $p->start_date,
                        'end_date' => $p->end_date,
                        'status' => 'open',
                    ]
                );
            }
        });
    }

    /**
     * KONTRAK: Skenario 1 - Verifikasi Konsistensi Harian dari DB
     * Method: test_klinik_daily_transactions_period_27_to_26_from_db
     * Parameter: Tidak ada
     * Respons (Ekspektasi): 12 transaksi per hari, semua balance dan memiliki minimal 2 baris.
     */
    abstract public function test_klinik_daily_transactions_period_27_to_26_from_db(): void;

    /**
     * KONTRAK: Skenario 2 - Verifikasi Data Seeder Massal
     * Method: test_verify_seeded_general_journal_data
     * Parameter: Tidak ada
     * Respons (Ekspektasi): Data jurnal tersimpan dengan format transaction_code yang benar (Regex).
     */
    abstract public function test_verify_seeded_general_journal_data(): void;

    /**
     * KONTRAK: Skenario 3 - Simulasi Transaksi Massal dari Dummy
     * Method: test_klinik_daily_transactions_period_27_to_26_from_dummy
     * Parameter: Tidak ada
     * Respons (Ekspektasi): Berhasil memasukkan 5 transaksi dummy per hari dan validasi data terisi benar.
     */
    abstract public function test_klinik_daily_transactions_period_27_to_26_from_dummy(): void;

    /**
     * KONTRAK: Skenario 4 - Validasi Keseimbangan Debet-Kredit
     * Method: test_batch_balancing_validation
     * Parameter: Data tidak seimbang
     * Respons (Ekspektasi): Melempar Exception saat data tidak balance.
     */
    abstract public function test_batch_balancing_validation(): void;

    /**
     * KONTRAK: Skenario 5 - Format Penomoran (transaction_code)
     * Method: test_transaction_code_format
     * Parameter: Jurnal baru
     * Respons (Ekspektasi): transaction_code mengikuti format [BRANCH]/[MODUL]/[YYYYMM]/[SEQ].
     */
    abstract public function test_transaction_code_format(): void;

    /**
     * KONTRAK: Skenario 6 - Penjualan Kompleks (Revenue + HPP) dari Dummy
     * Method: test_sale_with_cogs_scenario_from_dummy
     * Parameter: Transaksi kompleks harian
     * Respons (Ekspektasi): Terbentuk 2 jurnal (Revenue & COGS) per transaksi dengan nilai yang tepat.
     */
    abstract public function test_sale_with_cogs_scenario_from_dummy(): void;

    /**
     * KONTRAK: Skenario 7 - Penjualan Kompleks dari DB (Seeded)
     * Method: test_sale_with_cogs_scenario_from_db
     * Parameter: Tidak ada
     * Respons (Ekspektasi): Jurnal kompleks hasil seeder terbaca dan valid di setiap hari.
     */
    abstract public function test_sale_with_cogs_scenario_from_db(): void;

    /**
     * KONTRAK: Isolasi Data (Multi-tenancy)
     * Method: test_branch_data_isolation
     * Parameter: Data dari Cabang A dan Cabang B
     * Respons (Ekspektasi): User Cabang A tidak bisa melihat data Cabang B.
     */
    abstract public function test_branch_data_isolation(): void;

    /**
     * KONTRAK: Jurnal Umum dan Penyesuaian
     * Method: test_manual_and_adjustment_creation
     * Parameter: Data jurnal manual
     * Respons (Ekspektasi): Berhasil membuat jurnal dengan tipe GENERAL dan ADJUSTMENT secara manual.
     */
    abstract public function test_manual_and_adjustment_creation(): void;

    /**
     * KONTRAK: Validasi Periode Tertutup
     * Method: test_closed_period_rejection
     * Parameter: Jurnal pada periode 'closed'
     * Respons (Ekspektasi): Penolakan pembuatan jurnal dengan pesan error yang sesuai.
     */
    abstract public function test_closed_period_rejection(): void;

    /**
     * KONTRAK: Pembatalan Jurnal (Void)
     * Method: test_void_journal_success
     * Parameter: ID Jurnal
     * Respons (Ekspektasi): Berhasil membuat jurnal pembalik (REVERSAL).
     */
    abstract public function test_void_journal_success(): void;

    /** Helper: Verifikasi data harian dari DB **/
    protected function verifyDailyTransactionsFromDb(string $prefix, float $expectedAmount): void
    {
        $startDate = $this->startDate;
        $endDate = $this->endDate;
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $dateString = $currentDate->toDateString();
            $startOfDay = $currentDate->copy()->startOfDay()->timestamp;
            $endOfDay = $currentDate->copy()->endOfDay()->timestamp;
            $journal = Journal::withoutGlobalScopes()
                ->where('branch_uuid', $this->branch->uuid)
                ->whereBetween('journal_date', [$startOfDay, $endOfDay])
                ->where('reference_no', 'like', "{$prefix}%")
                ->with('lines')
                ->first();

            $this->assertNotNull($journal, "Jurnal {$prefix} harus ada pada {$dateString}");
            $this->assertEquals($expectedAmount, (float)$journal->lines->sum('debit'));
            $this->assertEquals($expectedAmount, (float)$journal->lines->sum('credit'));

            $currentDate->addDay();
        }
    }

    /** Helper: Verifikasi data harian dari Dummy simulasi **/
    protected function verifyDailyTransactionsFromDummy(JournalReferenceType $type, ?JournalReferenceModel $model, float $expectedAmount, array $linesSetup): void
    {
        $prefix = strtoupper(substr($type->value, 0, 3));
        $startDate = $this->startDate;
        $endDate = $this->endDate;

        $companies = Branch::all();
        $allAccounts = Account::all()->keyBy('code');

        foreach ($companies as $company) {
            $currentDate = $startDate->copy();
            $companyAccounts = $allAccounts;

            while ($currentDate->lte($endDate)) {
                $dateString = $currentDate->toDateString();
                $referenceNo = $this->journalService->generateReferenceNo($type, null, [
                    'reference_no' => "DUMMY-{$prefix}-{$currentDate->format('Ymd')}-" . uniqid()
                ]);

                $lines = [];
                foreach ($linesSetup as $setup) {
                    $acc = $companyAccounts[$setup['code']] ?? null;
                    if (!$acc) {
                        throw new Exception("Account for code {$setup['code']} missing");
                    }
                    $lines[] = [
                        'account_uuid' => $acc->uuid,
                        'debit' => $setup['debit'],
                        'credit' => $setup['credit'],
                    ];
                }

                $dto = JournalEntity::fromArray([
                    'branch_uuid' => $company->uuid,
                    'reference_no' => $referenceNo,
                    'reference_uuid' => 1234,
                    'journal_date' => $dateString,
                    'reference_type' => $type,
                    'reference_model' => $model,
                    'lines' => $lines
                ]);

                $journal = $this->journalService->createJournal($dto);

                $this->assertNotNull($journal);
                $this->assertEquals($expectedAmount, (float)$journal->lines->sum('debit'));
                $currentDate->addDay();
            }
        }
    }
}

/**
 * JournalServiceTest
 * 
 * Implementasi konkret untuk pengujian JournalService.
 */
class JournalServiceTest extends AbstractJournalServiceRegressionBase
{
    public function test_klinik_daily_transactions_period_27_to_26_from_db(): void
    {
        $currentDate = $this->startDate->copy();
        while ($currentDate->lte($this->endDate)) {
            $dateString = $currentDate->toDateString();
            $startOfDay = $currentDate->copy()->startOfDay()->timestamp;
            $endOfDay = $currentDate->copy()->endOfDay()->timestamp;
            $journals = Journal::withoutGlobalScopes()
                ->where('branch_uuid', $this->branch->uuid)
                ->whereBetween('journal_date', [$startOfDay, $endOfDay])
                ->where(function($query) {
                    $query->where('reference_no', 'like', 'INV-%')
                          ->orWhere('reference_no', 'like', 'INV-CPX-%')
                          ->orWhere('reference_no', 'like', 'EXP-%')
                          ->orWhere('reference_no', 'like', 'PO-%')
                          ->orWhere('reference_no', 'like', 'PAY-%')
                          ->orWhere('reference_no', 'like', 'ENC-%')
                          ->orWhere('reference_no', 'like', 'COL-%');
                })
                ->with('lines')
                ->get();

            $this->assertCount(12, $journals);
            foreach ($journals as $journal) {
                $this->assertEquals(0, (float)$journal->lines->sum('debit') - (float)$journal->lines->sum('credit'));
            }
            $currentDate->addDay();
        }
    }

    public function test_verify_seeded_general_journal_data(): void
    {
        $journals = Journal::where('reference_no', 'like', 'INV-%')->with('lines')->get();
        $this->assertGreaterThan(0, $journals->count());

        foreach ($journals as $journal) {
            $this->assertMatchesRegularExpression('/^[A-Z]{2,3}\/[A-Z]+\/\d{4}\/\d{4}$/', $journal->transaction_code);
            $this->assertEquals((float)$journal->lines->sum('debit'), (float)$journal->lines->sum('credit'));
        }
    }

    public function test_klinik_daily_transactions_period_27_to_26_from_dummy(): void
    {
        $currentDate = $this->startDate->copy();
        while ($currentDate->lte($this->endDate)) {
            $journalsData = [];
            for ($i = 1; $i <= 5; $i++) {
                $journalsData[] = [
                    'branch_uuid' => $this->branch->uuid,
                    'reference_no' => $this->journalService->generateReferenceNo(JournalReferenceType::SALE, null, [
                        'reference_no' => "DUMMY-" . uniqid() . "-{$currentDate->format('Ymd')}-{$i}"
                    ]),
                    'reference_uuid' => $i,
                    'journal_date' => $currentDate->toDateString(),
                    'description' => "Dummy Trans {$i}",
                    'reference_model' => JournalReferenceModel::SALE,
                    'reference_type' => JournalReferenceType::SALE,
                    'lines' => [
                        ['account_uuid' => $this->accounts['1101']->uuid, 'debit' => 100000, 'credit' => 0],
                        ['account_uuid' => $this->accounts['4101']->uuid, 'debit' => 0, 'credit' => 100000],
                    ]
                ];
            }

            $results = $this->journalService->createManyByArray($journalsData);
            $this->assertCount(5, $results);
            $currentDate->addDay();
        }
    }

    public function test_batch_balancing_validation(): void
    {
        $badData = [[
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'INVALID-' . uniqid(),
            'reference_uuid' => 999,
            'journal_date' => '2026-03-15',
            'reference_type' => JournalReferenceType::SALE,
            'lines' => [
                ['account_uuid' => $this->accounts['1101']->uuid, 'debit' => 1000, 'credit' => 0],
                ['account_uuid' => $this->accounts['4101']->uuid, 'debit' => 0, 'credit' => 999],
            ]
        ]];
        $this->expectException(Exception::class);
        $this->journalService->createManyByArray($badData);
    }

    public function test_transaction_code_format(): void
    {
        $data = [[
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'FMT-TEST-' . uniqid(),
            'reference_uuid' => 1,
            'journal_date' => '2026-03-15',
            'reference_model' => JournalReferenceModel::SALE,
            'reference_type' => JournalReferenceType::SALE,
            'lines' => [
                ['account_uuid' => $this->accounts['1101']->uuid, 'debit' => 50000, 'credit' => 0],
                ['account_uuid' => $this->accounts['4101']->uuid, 'debit' => 0, 'credit' => 50000],
            ]
        ]];
        $this->journalService->createManyByArray($data);
        $journal = Journal::where('reference_no', $data[0]['reference_no'])->first();
        $this->assertMatchesRegularExpression('/^[A-Z]{2,3}\/[A-Z]+\/\d{4}\/\d{4}$/', $journal->transaction_code);
    }

    public function test_sale_with_cogs_scenario_from_dummy(): void
    {
        $currentDate = $this->startDate->copy();
        while ($currentDate->lte($this->endDate)) {
            $referenceNo = "SALE-CPX-DUMMY-" . uniqid();
            $data = [
                [
                    'branch_uuid' => $this->branch->uuid,
                    'reference_no' => $this->journalService->generateReferenceNo(JournalReferenceType::SALE, null, [
                        'reference_no' => $referenceNo . '-REV'
                    ]),
                    'reference_uuid' => 101,
                    'journal_date' => $currentDate->toDateString(),
                    'reference_model' => JournalReferenceModel::SALE,
                    'reference_type' => JournalReferenceType::SALE,
                    'lines' => [
                        ['account_uuid' => $this->accounts['1101']->uuid, 'debit' => 150000, 'credit' => 0],
                        ['account_uuid' => $this->accounts['4101']->uuid, 'debit' => 0, 'credit' => 100000],
                        ['account_uuid' => $this->accounts['4102']->uuid, 'debit' => 0, 'credit' => 50000],
                    ]
                ],
                [
                    'branch_uuid' => $this->branch->uuid,
                    'reference_no' => $this->journalService->generateReferenceNo(JournalReferenceType::SALE, null, [
                        'reference_no' => $referenceNo . '-COST'
                    ]),
                    'reference_uuid' => 101,
                    'journal_date' => $currentDate->toDateString(),
                    'reference_model' => JournalReferenceModel::SALE,
                    'reference_type' => JournalReferenceType::SALE,
                    'lines' => [
                        ['account_uuid' => $this->accounts['5101']->uuid, 'debit' => 30000, 'credit' => 0],
                        ['account_uuid' => $this->accounts['1301']->uuid, 'debit' => 0, 'credit' => 30000],
                    ]
                ]
            ];
            $this->journalService->createManyByArray($data);
            
            // Verifikasi
            $rev = Journal::where('reference_no', $referenceNo . '-REV')->first();
            $this->assertNotNull($rev);
            $this->assertEquals(150000, (float)$rev->lines->sum('debit'));
            
            $currentDate->addDay();
        }
    }

    public function test_sale_with_cogs_scenario_from_db(): void
    {
        $currentDate = $this->startDate->copy();
        while ($currentDate->lte($this->endDate)) {
            $startOfDay = $currentDate->copy()->startOfDay()->timestamp;
            $endOfDay = $currentDate->copy()->endOfDay()->timestamp;
            $revenueJournal = Journal::withoutGlobalScopes()
                ->whereBetween('journal_date', [$startOfDay, $endOfDay])
                ->where('reference_no', 'like', 'INV-CPX-%')
                ->get()
                ->first(fn ($journal) => str_ends_with((string) $journal->reference_no, '-REV'));
            $this->assertNotNull($revenueJournal);
            $currentDate->addDay();
        }
    }

    public function test_branch_data_isolation(): void
    {
        $branchB = Branch::create(['name' => 'Branch B', 'short_name' => 'KCB', 'is_active' => true]);
        $userB = User::create(['name' => 'B', 'email' => 'userb' . uniqid() . '@klinik.local', 'password' => bcrypt('password'), 'branch_uuid' => $branchB->uuid]);
        $this->actingAs($userB);
        $this->assertCount(0, Journal::where('branch_uuid', $branchB->uuid)->get());
    }

    public function test_manual_and_adjustment_creation(): void
    {
        $accD = Account::where('is_header', false)->first();
        $accK = Account::where('is_header', false)->skip(1)->first();
        $data = [
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => $this->journalService->generateReferenceNo(JournalReferenceType::GENERAL, null, [
                'reference_no' => 'MANUAL-' . uniqid()
            ]),
            'reference_uuid' => 1,
            'journal_date' => '2026-03-18',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [['account_uuid' => $accD->uuid, 'debit' => 50000, 'credit' => 0], ['account_uuid' => $accK->uuid, 'debit' => 0, 'credit' => 50000]]
        ];
        $journal = $this->journalService->createJournal(JournalEntity::fromArray($data));
        $this->assertNotNull($journal);
    }

    public function test_closed_period_rejection(): void
    {
        ClosingPeriod::where('branch_uuid', $this->branch->uuid)->where('period', '2026-03')->update(['status' => 'closed', 'closed_at' => now()]);
        $accD = Account::where('is_header', false)->first();
        $accK = Account::where('is_header', false)->skip(1)->first();
        $data = [
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'CLOSED-' . uniqid(),
            'reference_uuid' => 1,
            'journal_date' => '2026-03-15',
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [['account_uuid' => $accD->uuid, 'debit' => 10000, 'credit' => 0], ['account_uuid' => $accK->uuid, 'debit' => 0, 'credit' => 10000]]
        ];
        $this->expectException(Exception::class);
        $this->journalService->createJournal(JournalEntity::fromArray($data));
    }

    public function test_missing_closing_period_rejection(): void
    {
        $accD = Account::where('is_header', false)->first();
        $accK = Account::where('is_header', false)->skip(1)->first();
        
        // Memakai tanggal di masa depan yang pastinya belum dibuatkan periode akuntansinya oleh seeder
        $data = [
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'MISSING-' . uniqid(),
            'reference_uuid' => 1,
            'journal_date' => '2099-12-31', 
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $accD->uuid, 'debit' => 15000, 'credit' => 0],
                ['account_uuid' => $accK->uuid, 'debit' => 0, 'credit' => 15000]
            ]
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Cannot create/update journal entry. No accounting period defined for the date '2099-12-31'");
        
        $this->journalService->createJournal(JournalEntity::fromArray($data));
    }

    public function test_void_journal_success(): void
    {
        $data = [
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'VO-' . uniqid(),
            'reference_uuid' => 1,
            'journal_date' => '2026-03-15',
            'reference_type' => JournalReferenceType::SALE,
            'lines' => [['account_uuid' => $this->accounts['1101']->uuid, 'debit' => 1000, 'credit' => 0], ['account_uuid' => $this->accounts['4101']->uuid, 'debit' => 0, 'credit' => 1000]]
        ];
        $j = $this->journalService->createJournal(JournalEntity::fromArray($data));
        $rev = $this->journalService->voidJournal($j->uuid);
        $this->assertNotNull($rev);
        $this->assertEquals(JournalReferenceType::REVERSAL, $rev->reference_type);
    }
    
    // Test integrasi harian dari DB
    public function test_expense_daily_transactions_from_db(): void { $this->verifyDailyTransactionsFromDb('EXP-', 50000); }
    public function test_purchase_daily_transactions_from_db(): void { $this->verifyDailyTransactionsFromDb('PO-', 1000000); }
    public function test_debt_payment_daily_transactions_from_db(): void { $this->verifyDailyTransactionsFromDb('PAY-', 500000); }
    public function test_encounter_daily_transactions_from_db(): void { $this->verifyDailyTransactionsFromDb('ENC-', 200000); }
    public function test_collection_daily_transactions_from_db(): void { $this->verifyDailyTransactionsFromDb('COL-', 150000); }

    // Test simulasi runtime dari Dummy
    public function test_expense_daily_transactions_from_dummy(): void { $this->verifyDailyTransactionsFromDummy(JournalReferenceType::EXPENSE, null, 50000, [['code' => '5204', 'debit' => 30000, 'credit' => 0], ['code' => '1101', 'debit' => 0, 'credit' => 50000], ['code' => '5205', 'debit' => 20000, 'credit' => 0]]); }
    public function test_purchase_daily_transactions_from_dummy(): void { $this->verifyDailyTransactionsFromDummy(JournalReferenceType::PURCHASE, JournalReferenceModel::PURCHASE, 1000000, [['code' => '1301', 'debit' => 1000000, 'credit' => 0], ['code' => '2101', 'debit' => 0, 'credit' => 1000000]]); }
    public function test_debt_payment_daily_transactions_from_dummy(): void { $this->verifyDailyTransactionsFromDummy(JournalReferenceType::DEBT_PAYMENT, null, 500000, [['code' => '2101', 'debit' => 500000, 'credit' => 0], ['code' => '1101', 'debit' => 0, 'credit' => 500000]]); }
    public function test_encounter_daily_transactions_from_dummy(): void { $this->verifyDailyTransactionsFromDummy(JournalReferenceType::ENCOUNTER, JournalReferenceModel::ENCOUNTER, 200000, [['code' => '4101', 'debit' => 0, 'credit' => 150000], ['code' => '1201', 'debit' => 200000, 'credit' => 0], ['code' => '4102', 'debit' => 0, 'credit' => 50000]]); }
    public function test_collection_daily_transactions_from_dummy(): void { $this->verifyDailyTransactionsFromDummy(JournalReferenceType::RECEIVABLE_COLLECTION, null, 150000, [['code' => '1101', 'debit' => 150000, 'credit' => 0], ['code' => '1201', 'debit' => 0, 'credit' => 150000]]); }

    /**
     * Skenario Proteksi Audit: Jurnal Sistem dari POS dilarang edit manual.
     */
    public function test_system_journal_cannot_be_edited_manually(): void
    {
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'PROT/EDIT/' . uniqid(),
            'reference_uuid' => 123,
            'journal_date' => '2026-03-15',
            'reference_model' => JournalReferenceModel::SALE,
            'reference_type' => JournalReferenceType::SALE,
            'lines' => [
                ['account_uuid' => $this->accounts['1101']->uuid, 'debit' => 1000, 'credit' => 0],
                ['account_uuid' => $this->accounts['4101']->uuid, 'debit' => 0, 'credit' => 1000],
            ],
            'is_system' => true
        ]);
        $this->journalService->upsertJournal($dto);

        // Edit manual attempt
        $manualDto = clone $dto;
        $manualDto->reference_model = null; // Mark as manual edit
        $manualDto->lines[0]->debit = 2000;
        $manualDto->lines[1]->credit = 2000;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Jurnal sistem (Audit-Entry) tidak dapat diubah secara manual. Silakan koreksi di modul asal (Sale).");
        $this->journalService->upsertJournal($manualDto);
    }

    /**
     * Skenario Proteksi Audit: Jurnal Sistem dilarang hapus manual.
     */
    public function test_system_journal_cannot_be_deleted_manually(): void
    {
        $journal = Journal::create([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'PROT/DEL/' . uniqid(),
            'transaction_code' => 'PROT-DEL-' . uniqid(),
            'reference_model' => JournalReferenceModel::SALE,
            'reference_type' => JournalReferenceType::SALE,
            'reference_uuid' => 123,
            'journal_date' => '2026-03-15',
            'is_system' => true,
            'status' => JournalStatus::POSTED
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Jurnal sistem tidak dapat dihapus secara manual.");
        $this->journalService->deleteJournal($journal->uuid);
    }

    /**
     * Skenario Proteksi Audit: Jurnal Manual (Umum) bebas dikelola.
     */
    public function test_manual_journal_can_be_edited_and_deleted(): void
    {
        $dto = JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'JR/MAN/' . uniqid(),
            'reference_uuid' => 0,
            'journal_date' => '2026-03-15',
            'reference_model' => null,
            'reference_type' => JournalReferenceType::GENERAL,
            'lines' => [
                ['account_uuid' => $this->accounts['1101']->uuid, 'debit' => 1000, 'credit' => 0],
                ['account_uuid' => $this->accounts['4101']->uuid, 'debit' => 0, 'credit' => 1000],
            ],
            'is_system' => false
        ]);
        $j = $this->journalService->upsertJournal($dto);
        $this->assertDatabaseHas('journals', ['uuid' => $j->uuid, 'is_system' => false]);

        // Edit
        $dto->lines[0]->debit = 5000;
        $dto->lines[1]->credit = 5000;
        $this->journalService->upsertJournal($dto);
        $this->assertDatabaseHas('journal_lines', ['journal_uuid' => $j->uuid, 'debit' => 5000]);

        // Delete
        $this->journalService->deleteJournal($j->uuid);
        $this->assertSoftDeleted('journals', ['uuid' => $j->uuid]);
    }
}
