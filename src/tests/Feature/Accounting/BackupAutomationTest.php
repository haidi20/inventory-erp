<?php

namespace Tests\Feature\Accounting;

use App\Modules\Finance\Ledger\Models\AccountBalance;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Finance\Journal\Models\Journal;
use App\Modules\Finance\Journal\Models\JournalLine;
use Database\Seeders\V1\DemoSeeder;
use Database\Seeders\V1\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackupAutomationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RbacSeeder::class,
            DemoSeeder::class,
        ]);
    }

    public function test_backup_snapshot_contains_critical_financial_datasets(): void
    {
        $snapshot = $this->buildSnapshot();

        $this->assertArrayHasKey('meta', $snapshot);
        $this->assertArrayHasKey('counts', $snapshot['meta']);
        $this->assertGreaterThan(0, $snapshot['meta']['counts']['branches']);
        $this->assertGreaterThan(0, $snapshot['meta']['counts']['journals']);
        $this->assertGreaterThan(0, $snapshot['meta']['counts']['journal_lines']);
        $this->assertGreaterThan(0, $snapshot['meta']['counts']['account_balances']);
        $this->assertNotEmpty($snapshot['branches']);
        $this->assertNotEmpty($snapshot['journals']);
    }

    public function test_backup_snapshot_is_json_serializable_for_automation_pipeline(): void
    {
        $snapshot = $this->buildSnapshot();
        $json = json_encode($snapshot, JSON_THROW_ON_ERROR);
        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $this->assertEquals($snapshot['meta']['counts']['journals'], $decoded['meta']['counts']['journals']);
        $this->assertEquals($snapshot['meta']['counts']['journal_lines'], $decoded['meta']['counts']['journal_lines']);
    }

    private function buildSnapshot(): array
    {
        return [
            'meta' => [
                'job' => 'daily-accounting-backup',
                'generated_at' => now()->toIso8601String(),
                'counts' => [
                    'branches' => Branch::count(),
                    'journals' => Journal::count(),
                    'journal_lines' => JournalLine::count(),
                    'account_balances' => AccountBalance::count(),
                ],
            ],
            'branches' => Branch::query()->select('id', 'name', 'short_name')->get()->toArray(),
            'journals' => Journal::query()->select('id', 'branch_uuid', 'reference_no', 'journal_date', 'status')->limit(25)->get()->toArray(),
            'journal_lines' => JournalLine::query()->select('id', 'journal_uuid', 'account_uuid', 'debit', 'credit')->limit(50)->get()->toArray(),
            'account_balances' => AccountBalance::query()->select('id', 'branch_uuid', 'account_uuid', 'period', 'closing_balance')->limit(50)->get()->toArray(),
        ];
    }
}
