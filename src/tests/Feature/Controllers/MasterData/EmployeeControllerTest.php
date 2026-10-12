<?php

namespace Tests\Feature\Controllers\MasterData;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Employee;
use App\Modules\Core\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeControllerTest extends TestCase
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

    /**
     * Test Index (Pagination)
     */
    public function test_can_list_employees()
    {
        Employee::factory()->count(5)->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/employees');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => [
                    'total',
                    'page',
                    'per_page'
                ]
            ]);
    }

    public function test_index_only_returns_employees_from_authenticated_users_company()
    {
        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);

        Employee::factory()->create([
            'name' => 'Sandbox Employee',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        Employee::factory()->create([
            'name' => 'Foreign Employee',
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/employees');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Sandbox Employee');
    }

    /**
     * Test Store (Create)
     */
    public function test_can_create_employee()
    {
        $payload = [
            'name' => 'Dr. Jane Doe',
            'role' => 'dokter',
            'nik' => '1234567890123456',
            'specialization' => 'Umum',
            'fee_default' => 50000,
            'is_active' => true
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/employees', $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('employees', [
            'name' => 'Dr. Jane Doe',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
    }

    /**
     * Test Show
     */
    public function test_can_show_employee()
    {
        $employee = Employee::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/employees/{$employee->uuid}");

        $response->assertStatus(200)
            ->assertJsonPath('data.uuid', $employee->uuid);
    }

    /**
     * Test Update
     */
    public function test_can_update_employee()
    {
        $employee = Employee::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
        
        $payload = [
            'name' => 'Dr. Jane Updated'
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/employees/{$employee->uuid}", $payload);

        $response->assertStatus(200);
        $this->assertEquals('Dr. Jane Updated', $employee->fresh()->name);
    }

    /**
     * Test Delete (Soft Delete)
     */
    public function test_can_delete_employee()
    {
        $employee = Employee::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/employees/{$employee->uuid}");

        $response->assertStatus(204);
        $this->assertNotEquals(0, $employee->fresh()->deleted_at);
    }

    public function test_cannot_show_employee_from_another_company()
    {
        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        $employee = Employee::factory()->create([
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/employees/{$employee->uuid}");

        $response->assertNotFound();
    }
}
