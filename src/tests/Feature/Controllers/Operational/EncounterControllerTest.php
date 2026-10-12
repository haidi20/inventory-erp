<?php

namespace Tests\Feature\Controllers\Operational;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Employee;
use App\Modules\Clinical\Registration\Models\Patient;
use App\Modules\Operational\Master\Models\Service;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Clinical\MedicalRecord\Models\Encounter;
use App\Modules\Clinical\Registration\Models\Registration;
use Illuminate\Support\Str;
use Tests\TestCase;

class EncounterControllerTest extends TestCase
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

    private function createBaseMedicalData(): array
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

        return [$patient, $doctor, $registration];
    }

    public function test_can_list_encounters()
    {
        [$patient, $doctor, $registration] = $this->createBaseMedicalData();

        Encounter::query()->create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'registration_uuid' => $registration->uuid,
            'patient_uuid' => $patient->uuid,
            'doctor_uuid' => $doctor->uuid,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/encounters');

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'total'],
            ]);
    }

    public function test_index_only_returns_encounters_from_authenticated_users_company()
    {
        [$patient, $doctor, $registration] = $this->createBaseMedicalData();

        Encounter::query()->create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'registration_uuid' => $registration->uuid,
            'patient_uuid' => $patient->uuid,
            'doctor_uuid' => $doctor->uuid,
            'complaint' => 'Sandbox complaint',
            'status' => 'draft',
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
        $otherRegistration = Registration::factory()->create([
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
            'patient_uuid' => $otherPatient->uuid,
            'doctor_uuid' => $otherDoctor->uuid,
        ]);

        Encounter::query()->create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
            'registration_uuid' => $otherRegistration->uuid,
            'patient_uuid' => $otherPatient->uuid,
            'doctor_uuid' => $otherDoctor->uuid,
            'complaint' => 'Foreign complaint',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/encounters');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.complaint', 'Sandbox complaint');
    }

    public function test_can_create_encounter()
    {
        [$patient, $doctor, $registration] = $this->createBaseMedicalData();
        $service = Service::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/v1/encounters', [
            'registration_uuid' => $registration->uuid,
            'patient_uuid' => $patient->uuid,
            'doctor_uuid' => $doctor->uuid,
            'complaint' => 'Demam',
            'status' => 'draft',
            'items' => [
                [
                    'item_type' => 'service',
                    'item_uuid' => $service->uuid,
                    'qty' => 1,
                    'price' => 50000,
                ],
            ],
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('encounters', [
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'complaint' => 'Demam',
        ]);
        $this->assertDatabaseHas('encounter_items', [
            'company_uuid' => $this->company->uuid,
            'item_uuid' => $service->uuid,
        ]);
    }

    public function test_can_show_encounter()
    {
        [$patient, $doctor, $registration] = $this->createBaseMedicalData();

        $encounter = Encounter::query()->create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'registration_uuid' => $registration->uuid,
            'patient_uuid' => $patient->uuid,
            'doctor_uuid' => $doctor->uuid,
            'complaint' => 'Show complaint',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/encounters/{$encounter->uuid}");

        $response->assertOk()
            ->assertJsonPath('data.uuid', $encounter->uuid);
    }

    public function test_can_update_encounter()
    {
        [$patient, $doctor, $registration] = $this->createBaseMedicalData();

        $encounter = Encounter::query()->create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'registration_uuid' => $registration->uuid,
            'patient_uuid' => $patient->uuid,
            'doctor_uuid' => $doctor->uuid,
            'complaint' => 'Old complaint',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->user)->putJson("/api/v1/encounters/{$encounter->uuid}", [
            'complaint' => 'Updated complaint',
        ]);

        $response->assertOk();
        $this->assertSame('Updated complaint', $encounter->fresh()->complaint);
    }

    public function test_can_delete_encounter()
    {
        [$patient, $doctor, $registration] = $this->createBaseMedicalData();

        $encounter = Encounter::query()->create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'registration_uuid' => $registration->uuid,
            'patient_uuid' => $patient->uuid,
            'doctor_uuid' => $doctor->uuid,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/encounters/{$encounter->uuid}");

        $response->assertNoContent();
        $this->assertNotEquals(0, $encounter->fresh()->deleted_at);
    }

    public function test_cannot_show_encounter_from_another_company()
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
        $otherRegistration = Registration::factory()->create([
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
            'patient_uuid' => $otherPatient->uuid,
            'doctor_uuid' => $otherDoctor->uuid,
        ]);
        $encounter = Encounter::query()->create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
            'registration_uuid' => $otherRegistration->uuid,
            'patient_uuid' => $otherPatient->uuid,
            'doctor_uuid' => $otherDoctor->uuid,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/encounters/{$encounter->uuid}");

        $response->assertNotFound();
    }
}
