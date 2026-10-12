<?php

namespace Tests\Feature\Accounting;

use App\Modules\Finance\Journal\Models\AccountMapping;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Services\JournalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Exception;

class AccountValidationTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $journalService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->journalService = app(JournalService::class);
    }

    /**
     * Skenario #21: Validasi Akun Header
     * Mencegah posting ke akun yang is_header = 1
     */
    public function test_cannot_post_to_header_account()
    {
        // 1. Setup Branch & Period
        $branch = Branch::create(['name' => 'Klinik Demo', 'short_name' => 'KDM']);
        ClosingPeriod::create([
            'branch_uuid' => $branch->uuid,
            'period'    => now()->format('Y-m'),
            'name'      => 'Periode Demo',
            'start_date'=> now()->startOfMonth()->toDateString(),
            'end_date'  => now()->endOfMonth()->toDateString(),
            'status'    => 'open',
        ]);

        // 2. Create Header Account (is_header = 1)
        $headerAccount = Account::create([
            'code' => '1000',
            'name' => 'AKTIVA LANCAR',
            'type' => 'asset',
            'type_saldo' => 'debit',
            'is_header' => true,
        ]);

        // 3. Create Regular Account for balance
        $regularAccount = Account::create([
            'code' => '1101',
            'name' => 'Kas',
            'type' => 'asset',
            'type_saldo' => 'debit',
            'is_header' => false,
        ]);

        // Mock Account Mapping for validateMapping() step
        AccountMapping::create([
            'key'        => 'hutang.uji',
            'account_uuid' => $regularAccount->uuid,
            'type_saldo' => 'credit',
        ]);

        // 4. Build Journal with Header Account in Line
        $lines = [
            [
                'account_uuid' => $headerAccount->uuid, // Mencegah ini!
                'debit'  => 50000,
                'credit' => 0,
            ],
            [
                'account_uuid' => $regularAccount->uuid,
                'debit'  => 0,
                'credit' => 50000,
            ],
        ];

        $dto = JournalEntity::fromArray([
            'branch_uuid'      => $branch->uuid,
            'reference_no'   => 'TEST-HDR-' . time(),
            'reference_uuid'   => 1,
            'journal_date'   => now()->toDateString(),
            'reference_type' => JournalReferenceType::DEBT_PAYMENT,
            'description'    => 'Posting ke Akun Header',
            'lines'          => $lines,
        ]);

        // 5. Expect Exception
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Cannot post to header account");

        // 6. Action
        $this->journalService->createJournal($dto);
    }
}
