<?php

namespace Tests\Feature\Controllers\Organization;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Core\Identity\Models\User;
use Tests\TestCase;

class CompanyControllerTest extends TestCase
{
    private User $user;
    private Company $company;
    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->company = Company::factory()->create(['name' => 'Test Company', 'deleted_at' => 0]);
        $this->branch  = Branch::factory()->create(['company_uuid' => $this->company->uuid]);
        $this->user    = User::factory()->create([
            'company_uuid' => $this->company->uuid,
            'branch_uuid'  => $this->branch->uuid,
        ]);
        $this->withoutMiddleware();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_can_list_companies(): void
    {
        Company::factory()->count(3)->create(['deleted_at' => 0]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/master/companies');

        $response->assertOk()->assertJsonStructure(['data']);
        $this->assertGreaterThanOrEqual(1, count($response->json('data')));
    }

    public function test_can_create_company(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/master/companies', [
            'name'      => 'PT Baru Sejahtera',
            'email'     => 'baru@example.com',
            'is_active' => true,
        ]);

        $response->assertStatus(201)->assertJsonPath('data.name', 'PT Baru Sejahtera');
        $this->assertDatabaseHas('companies', ['name' => 'PT Baru Sejahtera']);
    }

    public function test_can_show_company(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/master/companies/{$this->company->uuid}");

        $response->assertOk()->assertJsonPath('data.uuid', $this->company->uuid);
    }

    public function test_show_returns_404_for_nonexistent_company(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/master/companies/00000000-0000-0000-0000-000000000000');

        $response->assertNotFound();
    }

    public function test_can_update_company(): void
    {
        $response = $this->actingAs($this->user)->putJson(
            "/api/v1/master/companies/{$this->company->uuid}",
            ['name' => 'Nama Diperbarui', 'is_active' => true]
        );

        $response->assertOk()->assertJsonPath('data.name', 'Nama Diperbarui');
        $this->assertDatabaseHas('companies', [
            'uuid' => $this->company->uuid,
            'name' => 'Nama Diperbarui',
        ]);
    }

    public function test_can_delete_company(): void
    {
        $company = Company::factory()->create(['deleted_at' => 0]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/master/companies/{$company->uuid}");

        $response->assertOk()->assertJsonPath('message', 'Deleted');
        $deleted = Company::withTrashed()->where('uuid', $company->uuid)->first();
        $this->assertNotEquals(0, (int) $deleted->getRawOriginal('deleted_at'));
    }
}
