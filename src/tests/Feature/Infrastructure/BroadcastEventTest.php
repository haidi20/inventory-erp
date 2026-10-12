<?php

namespace Tests\Feature\Infrastructure;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Operational\Encounter\Models\Patient;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class BroadcastEventTest extends TestCase
{
    public function test_stock_level_changed_implements_should_broadcast(): void
    {
        $event = new \App\Modules\Operational\Inventory\Events\StockLevelChanged(
            itemUuid: 'test-item-uuid',
            branchUuid: 'test-branch-uuid',
            companyUuid: 'test-company-uuid',
            locationUuid: 'test-location-uuid',
            newQuantity: 10.0,
        );

        $this->assertContains(
            \Illuminate\Contracts\Broadcasting\ShouldBroadcast::class,
            class_implements($event),
            'StockLevelChanged harus implement ShouldBroadcast'
        );
    }

    public function test_stock_level_changed_broadcasts_on_private_branch_channel(): void
    {
        $event = new \App\Modules\Operational\Inventory\Events\StockLevelChanged(
            itemUuid: 'test-item-uuid',
            branchUuid: 'test-branch-uuid',
            companyUuid: 'test-company-uuid',
            locationUuid: 'test-location-uuid',
            newQuantity: 10.0,
        );

        $channels = $event->broadcastOn();

        $this->assertNotEmpty($channels);
        $this->assertInstanceOf(
            \Illuminate\Broadcasting\PrivateChannel::class,
            $channels[0],
            'Channel harus PrivateChannel'
        );
        $this->assertStringEndsWith('branch.test-branch-uuid', $channels[0]->name);
    }

    public function test_stock_level_changed_payload_has_required_fields(): void
    {
        $event = new \App\Modules\Operational\Inventory\Events\StockLevelChanged(
            itemUuid: 'test-item-uuid',
            branchUuid: 'test-branch-uuid',
            companyUuid: 'test-company-uuid',
            locationUuid: 'test-location-uuid',
            newQuantity: 10.0,
        );

        $payload = $event->broadcastWith();

        $this->assertArrayHasKey('item_uuid', $payload);
        $this->assertArrayHasKey('location_uuid', $payload);
        $this->assertArrayHasKey('new_quantity', $payload);
        $this->assertSame('test-item-uuid', $payload['item_uuid']);
        $this->assertSame(10.0, $payload['new_quantity']);
    }

    public function test_patient_registered_implements_should_broadcast(): void
    {
        $event = new \App\Modules\Clinical\Registration\Events\PatientRegistered(
            patientUuid: 'test-patient-uuid',
            branchUuid: 'test-branch-uuid',
            companyUuid: 'test-company-uuid',
            patientName: 'John Doe',
        );

        $this->assertContains(
            \Illuminate\Contracts\Broadcasting\ShouldBroadcast::class,
            class_implements($event),
            'PatientRegistered harus implement ShouldBroadcast'
        );
    }

    public function test_patient_registered_broadcasts_on_private_branch_channel(): void
    {
        $event = new \App\Modules\Clinical\Registration\Events\PatientRegistered(
            patientUuid: 'test-patient-uuid',
            branchUuid: 'test-branch-uuid',
            companyUuid: 'test-company-uuid',
            patientName: 'John Doe',
        );

        $channels = $event->broadcastOn();

        $this->assertNotEmpty($channels);
        $this->assertStringEndsWith('branch.test-branch-uuid', $channels[0]->name);
    }

    public function test_patient_registered_payload_has_required_fields(): void
    {
        $event = new \App\Modules\Clinical\Registration\Events\PatientRegistered(
            patientUuid: 'test-patient-uuid',
            branchUuid: 'test-branch-uuid',
            companyUuid: 'test-company-uuid',
            patientName: 'John Doe',
        );

        $payload = $event->broadcastWith();

        $this->assertArrayHasKey('patient_uuid', $payload);
        $this->assertArrayHasKey('patient_name', $payload);
        $this->assertSame('test-patient-uuid', $payload['patient_uuid']);
        $this->assertSame('John Doe', $payload['patient_name']);
    }

    public function test_dashboard_metrics_updated_implements_should_broadcast(): void
    {
        $event = new \App\Modules\Analytics\Reports\Events\DashboardMetricsUpdated(
            branchUuid: 'test-branch-uuid',
            companyUuid: 'test-company-uuid',
            metricKey: 'antrian_count',
            metricValue: 5.0,
        );

        $this->assertContains(
            \Illuminate\Contracts\Broadcasting\ShouldBroadcast::class,
            class_implements($event),
            'DashboardMetricsUpdated harus implement ShouldBroadcast'
        );
    }

    public function test_dashboard_metrics_updated_broadcasts_on_private_branch_channel(): void
    {
        $event = new \App\Modules\Analytics\Reports\Events\DashboardMetricsUpdated(
            branchUuid: 'test-branch-uuid',
            companyUuid: 'test-company-uuid',
            metricKey: 'antrian_count',
            metricValue: 5.0,
        );

        $channels = $event->broadcastOn();

        $this->assertNotEmpty($channels);
        $this->assertStringEndsWith('branch.test-branch-uuid', $channels[0]->name);
    }

    public function test_dashboard_metrics_updated_payload_has_required_fields(): void
    {
        $event = new \App\Modules\Analytics\Reports\Events\DashboardMetricsUpdated(
            branchUuid: 'test-branch-uuid',
            companyUuid: 'test-company-uuid',
            metricKey: 'antrian_count',
            metricValue: 5.0,
        );

        $payload = $event->broadcastWith();

        $this->assertArrayHasKey('metric_key', $payload);
        $this->assertArrayHasKey('metric_value', $payload);
        $this->assertSame('antrian_count', $payload['metric_key']);
        $this->assertSame(5.0, $payload['metric_value']);
    }

    public function test_antrian_updated_implements_should_broadcast(): void
    {
        $event = new \App\Modules\Clinical\Registration\Events\AntrianUpdated(
            branchUuid: 'test-branch-uuid',
            companyUuid: 'test-company-uuid',
            registrationUuid: 'test-reg-uuid',
            status: 'called',
            doctorName: 'Dr. Test',
        );

        $this->assertContains(
            \Illuminate\Contracts\Broadcasting\ShouldBroadcast::class,
            class_implements($event),
            'AntrianUpdated harus implement ShouldBroadcast'
        );
    }

    public function test_antrian_updated_broadcasts_on_private_branch_channel(): void
    {
        $event = new \App\Modules\Clinical\Registration\Events\AntrianUpdated(
            branchUuid: 'test-branch-uuid',
            companyUuid: 'test-company-uuid',
            registrationUuid: 'test-reg-uuid',
            status: 'called',
            doctorName: 'Dr. Test',
        );

        $channels = $event->broadcastOn();

        $this->assertNotEmpty($channels);
        $this->assertStringEndsWith('branch.test-branch-uuid', $channels[0]->name);
    }

    public function test_antrian_updated_payload_has_required_fields(): void
    {
        $event = new \App\Modules\Clinical\Registration\Events\AntrianUpdated(
            branchUuid: 'test-branch-uuid',
            companyUuid: 'test-company-uuid',
            registrationUuid: 'test-reg-uuid',
            status: 'called',
            doctorName: 'Dr. Test',
        );

        $payload = $event->broadcastWith();

        $this->assertArrayHasKey('registration_uuid', $payload);
        $this->assertArrayHasKey('status', $payload);
        $this->assertArrayHasKey('doctor_name', $payload);
        $this->assertSame('called', $payload['status']);
        $this->assertSame('Dr. Test', $payload['doctor_name']);
    }

    public function test_satusehat_sync_progress_implements_should_broadcast(): void
    {
        $event = new \App\Modules\Clinical\Integration\Events\SatusehatSyncProgress(
            companyUuid: 'test-company-uuid',
            syncBatchUuid: 'test-batch-uuid',
            status: 'processing',
            percentage: 50,
        );

        $this->assertContains(
            \Illuminate\Contracts\Broadcasting\ShouldBroadcast::class,
            class_implements($event),
            'SatusehatSyncProgress harus implement ShouldBroadcast'
        );
    }

    public function test_satusehat_sync_progress_broadcasts_on_private_company_channel(): void
    {
        $event = new \App\Modules\Clinical\Integration\Events\SatusehatSyncProgress(
            companyUuid: 'test-company-uuid',
            syncBatchUuid: 'test-batch-uuid',
            status: 'processing',
            percentage: 50,
        );

        $channels = $event->broadcastOn();

        $this->assertNotEmpty($channels);
        $this->assertStringEndsWith('company.test-company-uuid', $channels[0]->name);
    }

    public function test_satusehat_sync_progress_payload_has_required_fields(): void
    {
        $event = new \App\Modules\Clinical\Integration\Events\SatusehatSyncProgress(
            companyUuid: 'test-company-uuid',
            syncBatchUuid: 'test-batch-uuid',
            status: 'failed',
            percentage: 30,
            errors: ['Patient 1 mapping gagal'],
        );

        $payload = $event->broadcastWith();

        $this->assertArrayHasKey('sync_batch_uuid', $payload);
        $this->assertArrayHasKey('status', $payload);
        $this->assertArrayHasKey('percentage', $payload);
        $this->assertArrayHasKey('errors', $payload);
        $this->assertSame('failed', $payload['status']);
        $this->assertSame(30, $payload['percentage']);
        $this->assertSame(['Patient 1 mapping gagal'], $payload['errors']);
    }
}
