<?php

namespace Tests\Feature\Accounting;

use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Enums\JournalWorkflowStatus;
use App\Modules\Finance\Journal\Models\Account;
use App\Modules\Finance\Ledger\Models\AuditLog;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Finance\Journal\Services\JournalService;
use Database\Seeders\V1\DemoSeeder;
use Database\Seeders\V1\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected JournalService $journalService;
    protected User $admin;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RbacSeeder::class,
            DemoSeeder::class,
        ]);

        $this->journalService = app(JournalService::class);
        $this->admin = User::where('email', 'admin@klinik.local')->firstOrFail();
        $this->branch = Branch::findOrFail($this->admin->branch_uuid);

        $this->actingAs($this->admin);
    }

    public function test_audit_log_payload_is_ready_to_be_used_as_webhook_contract(): void
    {
        $cashAccount = Account::where('code', '1101')->firstOrFail();
        $revenueAccount = Account::where('code', '4101')->firstOrFail();

        $journal = $this->journalService->createJournal(JournalEntity::fromArray([
            'branch_uuid' => $this->branch->uuid,
            'reference_no' => 'WEBHOOK-001',
            'journal_date' => now()->toDateString(),
            'reference_type' => JournalReferenceType::GENERAL,
            'status' => JournalStatus::POSTED,
            'workflow_status' => JournalWorkflowStatus::APPROVED,
            'lines' => [
                ['account_uuid' => $cashAccount->uuid, 'debit' => 250000, 'credit' => 0],
                ['account_uuid' => $revenueAccount->uuid, 'debit' => 0, 'credit' => 250000],
            ],
        ]));

        $audit = AuditLog::where('entity_uuid', (string) $journal->uuid)
            ->where('event', 'created')
            ->latest('id')
            ->firstOrFail();

        $payload = $this->buildWebhookPayload($audit);

        $this->assertEquals('created', $payload['event']);
        $this->assertEquals((string) $journal->uuid, $payload['entity']['id']);
        $this->assertEquals($this->branch->uuid, $payload['branch_uuid']);
        $this->assertEquals('WEBHOOK-001', $payload['data']['after']['reference_no']);
        $this->assertArrayHasKey('sent_at', $payload);
    }

    private function buildWebhookPayload(AuditLog $audit): array
    {
        return [
            'event' => $audit->event,
            'branch_uuid' => $audit->branch_uuid,
            'user_uuid' => $audit->user_id,
            'entity' => [
                'type' => $audit->entity_type,
                'id' => $audit->entity_uuid,
            ],
            'data' => [
                'before' => $audit->before,
                'after' => $audit->after,
            ],
            'sent_at' => now()->toIso8601String(),
        ];
    }
}
