<?php

namespace Tests\Feature\Operational;

use App\Modules\Operational\Manufacturing\Models\Bom;
use App\Modules\Core\Organization\Models\Branch;
use App\Http\Middleware\CheckApiPermission;
use App\Modules\Core\Organization\Models\Company;
use Tests\Concerns\InteractsWithSandboxRuntime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BomControllerTest extends TestCase
{
    use RefreshDatabase, InteractsWithSandboxRuntime;

    /** Create a second company + branch for isolation tests. */
    private function createCompanyB(): array
    {
        $company = Company::create([
            'uuid'      => (string) Str::uuid(),
            'name'      => 'Company B',
            'is_active' => true,
        ]);
        $branch = Branch::create([
            'uuid'         => (string) Str::uuid(),
            'company_uuid' => $company->uuid,
            'short_name'   => 'B-B1',
            'name'         => 'Branch B1',
            'is_active'    => true,
        ]);
        return [$company, $branch];
    }

    // ---------------------------------------------------------------
    // index
    // ---------------------------------------------------------------

    public function test_index_returns_boms_of_own_company(): void
    {
        $owner   = $this->sandboxOwner();
        $company = $this->sandboxCompany();
        $branch  = $this->sandboxBranch();

        Bom::create([
            'uuid'         => (string) Str::uuid(),
            'company_uuid' => $company->uuid,
            'branch_uuid'  => $branch->uuid,
            'document_no'  => 'BOM-CTL-001',
            'item_uuid'    => (string) Str::uuid(),
            'unit_uuid'    => (string) Str::uuid(),
            'quantity'     => 1,
        ]);

        $response = $this->actingAs($owner, 'api')
            ->withoutMiddleware(CheckApiPermission::class)
            ->getJson('/api/v1/operational/boms');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_index_does_not_return_other_company_boms(): void
    {
        $owner = $this->sandboxOwner();
        [$companyB, $branchB] = $this->createCompanyB();

        Bom::create([
            'uuid'         => (string) Str::uuid(),
            'company_uuid' => $companyB->uuid,
            'branch_uuid'  => $branchB->uuid,
            'document_no'  => 'BOM-CTL-B-001',
            'item_uuid'    => (string) Str::uuid(),
            'unit_uuid'    => (string) Str::uuid(),
            'quantity'     => 1,
        ]);

        $response = $this->actingAs($owner, 'api')
            ->withoutMiddleware(CheckApiPermission::class)
            ->getJson('/api/v1/operational/boms');

        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data'));
    }

    // ---------------------------------------------------------------
    // store
    // ---------------------------------------------------------------

    public function test_store_creates_bom(): void
    {
        $owner = $this->sandboxOwner();

        $payload = [
            'item_uuid'   => (string) Str::uuid(),
            'unit_uuid'   => (string) Str::uuid(),
            'qty'         => 5,
            'description' => 'Test store BOM',
        ];

        $response = $this->actingAs($owner, 'api')
            ->withoutMiddleware(CheckApiPermission::class)
            ->postJson('/api/v1/operational/boms', $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('boms', [
            'company_uuid' => $owner->company_uuid,
            'item_uuid'    => $payload['item_uuid'],
        ]);
    }

    // ---------------------------------------------------------------
    // show
    // ---------------------------------------------------------------

    public function test_show_returns_bom(): void
    {
        $owner   = $this->sandboxOwner();
        $company = $this->sandboxCompany();
        $branch  = $this->sandboxBranch();
        $uuid    = (string) Str::uuid();

        Bom::create([
            'uuid'         => $uuid,
            'company_uuid' => $company->uuid,
            'branch_uuid'  => $branch->uuid,
            'document_no'  => 'BOM-SHOW-001',
            'item_uuid'    => (string) Str::uuid(),
            'unit_uuid'    => (string) Str::uuid(),
            'quantity'     => 2,
        ]);

        $response = $this->actingAs($owner, 'api')
            ->withoutMiddleware(CheckApiPermission::class)
            ->getJson("/api/v1/operational/boms/{$uuid}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.uuid', $uuid);
    }

    public function test_show_returns_404_for_other_company_bom(): void
    {
        $owner = $this->sandboxOwner();
        [$companyB, $branchB] = $this->createCompanyB();
        $uuid = (string) Str::uuid();

        Bom::create([
            'uuid'         => $uuid,
            'company_uuid' => $companyB->uuid,
            'branch_uuid'  => $branchB->uuid,
            'document_no'  => 'BOM-OTHER-001',
            'item_uuid'    => (string) Str::uuid(),
            'unit_uuid'    => (string) Str::uuid(),
            'quantity'     => 1,
        ]);

        $response = $this->actingAs($owner, 'api')
            ->withoutMiddleware(CheckApiPermission::class)
            ->getJson("/api/v1/operational/boms/{$uuid}");

        $response->assertStatus(404);
    }

    // ---------------------------------------------------------------
    // destroy
    // ---------------------------------------------------------------

    public function test_destroy_deletes_bom(): void
    {
        $owner   = $this->sandboxOwner();
        $company = $this->sandboxCompany();
        $branch  = $this->sandboxBranch();
        $uuid    = (string) Str::uuid();

        Bom::create([
            'uuid'         => $uuid,
            'company_uuid' => $company->uuid,
            'branch_uuid'  => $branch->uuid,
            'document_no'  => 'BOM-DEL-CTL-001',
            'item_uuid'    => (string) Str::uuid(),
            'unit_uuid'    => (string) Str::uuid(),
            'quantity'     => 1,
        ]);

        $response = $this->actingAs($owner, 'api')
            ->withoutMiddleware(CheckApiPermission::class)
            ->deleteJson("/api/v1/operational/boms/{$uuid}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('boms', ['uuid' => $uuid, 'deleted_at' => 0]);
    }

    // ---------------------------------------------------------------
    // unauthorized
    // ---------------------------------------------------------------

    public function test_unauthorized_returns_401(): void
    {
        $response = $this->getJson('/api/v1/operational/boms');
        $response->assertStatus(401);
    }
}
