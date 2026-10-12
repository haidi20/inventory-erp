<?php

namespace Tests\Feature\Operational;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Clinical\MedicalRecord\Models\Complaint;
use Tests\Traits\ManualDatabaseSetup;
use Illuminate\Support\Str;
use Tests\Concerns\InteractsWithSandboxRuntime;
use Tests\TestCase;

class ComplaintIsolationTest extends TestCase
{
    use ManualDatabaseSetup, InteractsWithSandboxRuntime;

    /** Create a second company + branch + user for isolation tests. */
    private function createCompanyB(): array
    {
        $company = Company::create([
            'uuid'      => (string) Str::uuid(),
            'name'      => 'Company B Complaint',
            'is_active' => true,
        ]);
        $branch = Branch::create([
            'uuid'         => (string) Str::uuid(),
            'company_uuid' => $company->uuid,
            'short_name' => 'CB1',
            'name'         => 'Branch Complaint B1',
            'is_active'    => true,
        ]);
        $user = User::factory()->create([
            'company_uuid' => $company->uuid,
            'branch_uuid'  => $branch->uuid,
        ]);
        return [$company, $branch, $user];
    }

    // ---------------------------------------------------------------
    // test_paginate_returns_only_own_company_complaints
    // ---------------------------------------------------------------

    public function test_paginate_returns_only_own_company_complaints(): void
    {
        $owner   = $this->sandboxOwner();
        $company = $this->sandboxCompany();
        $branch  = $this->sandboxBranch();

        [$companyB, $branchB] = $this->createCompanyB();

        // Complaint milik company A (sandbox)
        Complaint::create([
            'uuid'         => (string) Str::uuid(),
            'branch_uuid'  => $branch->uuid,
            'company_uuid' => $company->uuid,
            'type'         => 'pengaduan',
            'subject'      => 'Complaint Company A',
            'content'      => 'Isi pengaduan dari company A',
            'status'       => 'pending',
            'is_anonymous' => false,
        ]);

        // Complaint milik company B — tidak boleh muncul
        Complaint::create([
            'uuid'         => (string) Str::uuid(),
            'branch_uuid'  => $branchB->uuid,
            'company_uuid' => $companyB->uuid,
            'type'         => 'saran',
            'subject'      => 'Complaint Company B',
            'content'      => 'Isi saran dari company B',
            'status'       => 'pending',
            'is_anonymous' => false,
        ]);

        $response = $this->actingAs($owner, 'api')
            ->getJson('/api/v1/operational/complaints');

        $response->assertStatus(200);

        // Hanya complaint company A yang muncul
        $subjects = collect($response->json('data'))->pluck('subject')->all();
        $this->assertContains('Complaint Company A', $subjects);
        $this->assertNotContains('Complaint Company B', $subjects);
    }

    // ---------------------------------------------------------------
    // test_paginate_does_not_leak_other_company_data
    // ---------------------------------------------------------------

    public function test_paginate_does_not_leak_other_company_data(): void
    {
        $owner   = $this->sandboxOwner();
        $company = $this->sandboxCompany();
        $branch  = $this->sandboxBranch();

        [$companyB, $branchB, $userB] = $this->createCompanyB();

        // Complaint milik company A
        Complaint::create([
            'uuid'         => (string) Str::uuid(),
            'branch_uuid'  => $branch->uuid,
            'company_uuid' => $company->uuid,
            'type'         => 'pengaduan',
            'subject'      => 'Data Company A',
            'content'      => 'Isi company A',
            'status'       => 'pending',
            'is_anonymous' => false,
        ]);

        // User B query — tidak boleh lihat data company A
        $response = $this->actingAs($userB, 'api')
            ->getJson('/api/v1/operational/complaints');

        $response->assertStatus(200);

        $subjects = collect($response->json('data'))->pluck('subject')->all();
        $this->assertNotContains('Data Company A', $subjects);
    }

    // ---------------------------------------------------------------
    // test_store_via_api_sets_company_uuid_from_auth_user
    // ---------------------------------------------------------------

    public function test_store_via_api_sets_company_uuid_from_auth_user(): void
    {
        $owner   = $this->sandboxOwner();
        $company = $this->sandboxCompany();

        $payload = [
            'type'         => 'saran',
            'subject'      => 'Saran Isolasi Company',
            'content'      => 'Isi saran dari auth user sandbox',
            'is_anonymous' => false,
        ];

        $response = $this->actingAs($owner, 'api')
            ->postJson('/api/v1/operational/complaints', $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('complaints', [
            'subject'      => 'Saran Isolasi Company',
            'company_uuid' => $company->uuid,
        ]);
    }

    public function test_show_returns_not_found_for_other_company_complaint(): void
    {
        $owner = $this->sandboxOwner();
        [$companyB, $branchB] = $this->createCompanyB();

        $foreignComplaint = Complaint::create([
            'uuid'         => (string) Str::uuid(),
            'branch_uuid'  => $branchB->uuid,
            'company_uuid' => $companyB->uuid,
            'type'         => 'pengaduan',
            'subject'      => 'Complaint Company B Hidden',
            'content'      => 'Harus tersembunyi dari company sandbox',
            'status'       => 'pending',
            'is_anonymous' => false,
        ]);

        $this->actingAs($owner, 'api')
            ->getJson('/api/v1/operational/complaints/' . $foreignComplaint->uuid)
            ->assertStatus(404)
            ->assertJson(['message' => 'Data tidak ditemukan.']);
    }

    public function test_show_returns_not_found_for_unknown_uuid(): void
    {
        $owner = $this->sandboxOwner();

        $this->actingAs($owner, 'api')
            ->getJson('/api/v1/operational/complaints/' . Str::uuid())
            ->assertStatus(404)
            ->assertJson(['message' => 'Data tidak ditemukan.']);
    }

    public function test_complaint_routes_require_authentication(): void
    {
        $this->getJson('/api/v1/operational/complaints')->assertStatus(401);

        $this->postJson('/api/v1/operational/complaints', [
            'type' => 'pengaduan',
            'subject' => 'Unauthorized',
            'content' => 'Unauthorized content',
        ])->assertStatus(401);
    }
}
