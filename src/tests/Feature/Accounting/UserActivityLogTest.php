<?php

namespace Tests\Feature\Accounting;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Core\Authorization\Models\Permission;
use App\Modules\Core\Identity\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class UserActivityLogTest extends TestCase
{
    use DatabaseMigrations;

    protected User $admin;
    protected Company $company;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create([
            'company_uuid' => $this->company->uuid,
        ]);
        $this->admin = User::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $apiMasterPermission = Permission::firstOrCreate(
            ['name' => 'master.manage', 'guard_name' => 'web'],
            ['label' => 'Kelola Master API']
        );
        $this->admin->givePermissionTo($apiMasterPermission);

        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_patient_create_via_api_records_user_activity_log(): void
    {
        $this->withoutMiddleware();
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/patients', [
                'medical_record_no' => 'RM-ACT-001',
                'name' => 'Pasien Audit',
                'gender' => 'P',
                'phone' => '08123456789',
                'is_active' => true,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.branch_uuid', $this->branch->uuid)
            ->assertJsonPath('data.medical_record_no', 'RM-ACT-001');

        $this->assertDatabaseHas('audit_logs', [
            'branch_uuid' => $this->branch->uuid,
            'user_uuid' => $this->admin->uuid,
            'event' => 'patient.create',
            'entity_type' => Patient::class,
            'entity_uuid' => (string) $response->json('data.uuid'),
        ]);
    }

    public function test_patient_update_and_delete_via_api_record_activity_logs(): void
    {
        $this->withoutMiddleware();
        $patient = Patient::create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'medical_record_no' => 'RM-ACT-002',
            'name' => 'Pasien Lama',
            'gender' => 'L',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin)
            ->putJson("/api/v1/patients/{$patient->uuid}", [
                'name' => 'Pasien Baru',
                'phone' => '0899001122',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Pasien Baru');

        $this->actingAs($this->admin)
            ->deleteJson("/api/v1/patients/{$patient->uuid}")
            ->assertNoContent();

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'patient.update',
            'entity_type' => Patient::class,
            'entity_uuid' => (string) $patient->uuid,
            'user_uuid' => $this->admin->uuid,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'patient.delete',
            'entity_type' => Patient::class,
            'entity_uuid' => (string) $patient->uuid,
            'user_uuid' => $this->admin->uuid,
        ]);
    }
}
