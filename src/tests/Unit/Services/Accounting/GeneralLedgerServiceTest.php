<?php

namespace Tests\Unit\Services\Accounting;

use App\Modules\Finance\Ledger\Entities\GeneralLedgerFilterEntity;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Ledger\Models\ClosingPeriod;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Ledger\Repositories\GeneralLedgerRepository;
use App\Modules\Finance\Journal\Repositories\AccountSettingRepository;
use App\Modules\Core\Organization\Services\BranchService;
use App\Modules\Finance\Ledger\Services\GeneralLedgerService;
use Mockery;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GeneralLedgerServiceTest extends TestCase
{
    private $repositoryMock;
    private $accountSettingMock;
    private $branchMock;
    private GeneralLedgerService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(GeneralLedgerRepository::class);
        $this->accountSettingMock = Mockery::mock(AccountSettingRepository::class);
        $this->branchMock = Mockery::mock(BranchService::class);
        
        $this->service = new GeneralLedgerService(
            $this->repositoryMock,
            $this->accountSettingMock,
            $this->branchMock
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_ledger_report_calculates_running_balance()
    {
        $startDate = '2026-02-01';
        $endDate = '2026-02-28';
        $accountUuid = 'acc-123';

        $account = new Account(['uuid' => $accountUuid, 'code' => '1001', 'name' => 'Cash', 'type' => 'asset']);
        
        $this->accountSettingMock->shouldReceive('findAccountById')->with($accountUuid)->andReturn($account);
        $this->repositoryMock->shouldReceive('getOpeningBalance')->andReturn(1000.0);
        
        $lines = [
            (object)[
                'uuid' => 'l1',
                'journal_uuid' => 'j1',
                'journal_date' => Carbon::parse('2026-02-10')->timestamp,
                'reference_no' => 'REF-1',
                'description' => 'Desc 1',
                'debit' => 500,
                'credit' => 0,
                'is_reconciled' => false
            ],
            (object)[
                'uuid' => 'l2',
                'journal_uuid' => 'j2',
                'journal_date' => Carbon::parse('2026-02-15')->timestamp,
                'reference_no' => 'REF-2',
                'description' => 'Desc 2',
                'debit' => 0,
                'credit' => 200,
                'is_reconciled' => true
            ]
        ];

        $this->repositoryMock->shouldReceive('getJournalLines')->andReturn($lines);

        $report = $this->service->getLedgerReport(new GeneralLedgerFilterEntity(
            account_uuid: $accountUuid,
            date_from: $startDate,
            date_to: $endDate,
        ));

        $this->assertEquals(1000.0, $report['opening_balance']);
        $this->assertEquals(1500.0, $report['transactions'][0]['balance']);
        $this->assertEquals(1300.0, $report['transactions'][1]['balance']);
        $this->assertEquals(1300.0, $report['closing_balance']);
    }

    public function test_check_ledger_balance_returns_balanced_status()
    {
        $periodUuid = 'period-123';
        $period = Mockery::mock(ClosingPeriod::class);
        $period->shouldReceive('getAttribute')->with('branch_uuid')->andReturn('branch-123');
        $period->shouldReceive('getAttribute')->with('name')->andReturn('Feb 2026');

        $this->accountSettingMock->shouldReceive('findPeriodById')->with($periodUuid)->andReturn($period);
        $this->repositoryMock->shouldReceive('getTrialBalanceSums')->andReturn(['debit' => 5000, 'credit' => 5000]);

        $result = $this->service->checkLedgerBalance($periodUuid);

        $this->assertTrue($result['is_balanced']);
        $this->assertEquals(0, $result['difference']);
    }

    public function test_sync_by_period_calls_repository()
    {
        $this->repositoryMock->shouldReceive('syncByPeriod')->once();
        $this->service->syncByPeriod('uuid-123');
        $this->assertTrue(true);
    }
}
