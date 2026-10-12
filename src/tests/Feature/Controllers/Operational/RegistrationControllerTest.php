<?php

namespace Tests\Feature\Controllers\Operational;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Employee;
use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Clinical\Registration\Models\Registration;
use Tests\TestCase;

class RegistrationControllerTest extends TestCase
{
    private User $user;
    private Branch $branch;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);
        $this->user = User::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $this->withoutMiddleware();
    }

    public function test_can_list_registrations()
    {
        Registration::factory()->count(3)->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/registrations');

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'total'],
            ]);
    }

    public function test_index_only_returns_registrations_from_authenticated_users_company()
    {
        $patient = Patient::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $doctor = Employee::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        Registration::factory()->create([
            'queue_no' => 'A-01',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'patient_uuid' => $patient->uuid,
            'doctor_uuid' => $doctor->uuid,
        ]);

        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        $otherPatient = Patient::factory()->create([
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);
        $otherDoctor = Employee::factory()->create([
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);

        Registration::factory()->create([
            'queue_no' => 'A-99',
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
            'patient_uuid' => $otherPatient->uuid,
            'doctor_uuid' => $otherDoctor->uuid,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/registrations');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.queue_no', 'A-01');
    }

    public function test_can_create_registration()
    {
        $patient = Patient::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $doctor = Employee::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/v1/registrations', [
            'queue_no' => 'A-01',
            'patient_uuid' => $patient->uuid,
            'doctor_uuid' => $doctor->uuid,
            'status' => 'waiting',
            'registered_at' => now()->toDateTimeString(),
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('registrations', [
            'queue_no' => 'A-01',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
    }

    public function test_can_show_registration()
    {
        $patient = Patient::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $doctor = Employee::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $registration = Registration::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'patient_uuid' => $patient->uuid,
            'doctor_uuid' => $doctor->uuid,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/registrations/{$registration->uuid}");

        $response->assertOk()
            ->assertJsonPath('data.uuid', $registration->uuid);
    }

    public function test_can_update_registration()
    {
        $patient = Patient::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $doctor = Employee::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $registration = Registration::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'patient_uuid' => $patient->uuid,
            'doctor_uuid' => $doctor->uuid,
            'status' => 'waiting',
        ]);

        $response = $this->actingAs($this->user)->putJson("/api/v1/registrations/{$registration->uuid}", [
            'status' => 'in_progress',
        ]);

        $response->assertOk();
        $this->assertSame('in_progress', $registration->fresh()->status);
    }

    public function test_can_delete_registration()
    {
        $patient = Patient::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $doctor = Employee::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        $registration = Registration::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'patient_uuid' => $patient->uuid,
            'doctor_uuid' => $doctor->uuid,
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/registrations/{$registration->uuid}");

        $response->assertNoContent();
        $this->assertNotEquals(0, $registration->fresh()->deleted_at);
    }

    public function test_cannot_show_registration_from_another_company()
    {
        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        $otherPatient = Patient::factory()->create([
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);
        $otherDoctor = Employee::factory()->create([
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);
        $registration = Registration::factory()->create([
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
            'patient_uuid' => $otherPatient->uuid,
            'doctor_uuid' => $otherDoctor->uuid,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/registrations/{$registration->uuid}");

        $response->assertNotFound();
    }
}
