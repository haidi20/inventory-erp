<?php

namespace Tests\Feature\Controllers\Organization;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Core\Identity\Models\User;
use Tests\TestCase;

class BranchControllerTest extends TestCase
{
    private User $user;
    private Company $company;
    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->company = Company::factory()->create(['deleted_at' => 0]);
        $this->branch  = Branch::factory()->create(['company_uuid' => $this->company->uuid]);
        $this->user    = User::factory()->create([
            'company_uuid' => $this->company->uuid,
            'branch_uuid'  => $this->branch->uuid,
        ]);
        $this->withoutMiddleware();
    }

    public function test_can_list_branches(): void
    {
        Branch::factory()->count(3)->create(['company_uuid' => $this->company->uuid]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/master/branches');

        $response->assertOk()->assertJsonStructure(['data']);
        $this->assertGreaterThanOrEqual(1, count($response->json('data')));
    }

    public function test_can_create_branch(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/master/branches', [
            'company_uuid' => $this->company->uuid,
            'name'         => 'Cabang Baru',
            'short_name'   => 'CBR',
            'is_active'    => true,
        ]);

        $response->assertStatus(201)->assertJsonPath('data.name', 'Cabang Baru');
        $this->assertDatabaseHas('branches', ['name' => 'Cabang Baru', 'short_name' => 'CBR']);
    }

    public function test_can_show_branch(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/master/branches/{$this->branch->uuid}");

        $response->assertOk()->assertJsonPath('data.uuid', $this->branch->uuid);
    }

    public function test_show_returns_404_for_nonexistent_branch(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/master/branches/00000000-0000-0000-0000-000000000000');

        $response->assertNotFound();
    }

    public function test_can_update_branch(): void
    {
        $response = $this->actingAs($this->user)->putJson(
            "/api/v1/master/branches/{$this->branch->uuid}",
            [
                'company_uuid' => $this->company->uuid,
                'name'         => 'Cabang Diperbarui',
                'short_name'   => $this->branch->short_name,
                'is_active'    => true,
            ]
        );

        $response->assertOk()->assertJsonPath('data.name', 'Cabang Diperbarui');
        $this->assertDatabaseHas('branches', [
            'uuid' => $this->branch->uuid,
            'name' => 'Cabang Diperbarui',
        ]);
    }

    public function test_index_isolation_only_returns_branches_from_same_company(): void
    {
        Branch::factory()->create([
            'company_uuid' => $this->company->uuid,
            'name'         => 'Cabang Milik Sendiri',
        ]);

        $otherCompany = Company::factory()->create();
        Branch::factory()->create([
            'company_uuid' => $otherCompany->uuid,
            'name'         => 'Cabang Perusahaan Lain',
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/master/branches');
        $response->assertOk();

        $names = collect($response->json('data'))->pluck('name')->toArray();
        $this->assertNotContains('Cabang Perusahaan Lain', $names);
    }

    public function test_can_delete_branch(): void
    {
        $branch = Branch::factory()->create(['company_uuid' => $this->company->uuid]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/master/branches/{$branch->uuid}");

        $response->assertOk()->assertJsonPath('message', 'Deleted');
        $deleted = Branch::withTrashed()->where('uuid', $branch->uuid)->first();
        $this->assertNotEquals(0, (int) $deleted->getRawOriginal('deleted_at'));
    }
}
