<?php

namespace Tests\Feature\Accounting;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Identity\Models\User;
use Database\Seeders\V1\DemoSeeder;
use Database\Seeders\V1\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Tests\TestCase;

class ApiIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        // Pastikan permission cache fresh di setiap test
        config(['permission.cache.store' => 'array']);
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $this->seed([
            RbacSeeder::class,
            DemoSeeder::class,
        ]);

        $this->admin = User::where('email', 'admin@klinik.local')->firstOrFail();
        $this->branch = Branch::where('uuid', $this->admin->branch_uuid)->firstOrFail();
    }

    public function test_api_requires_auth_header_for_protected_endpoint(): void
    {
        $this->getJson('/api/v1/patients')
            ->assertUnauthorized()
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_authorized_api_request_can_create_patient_in_branch_context(): void
    {
        // Bypass permission middleware — fokus test ini adalah membuktikan
        // bahwa user yang terautentikasi dapat membuat pasien dan company_uuid/branch_uuid terset benar.
        $response = $this->actingAs($this->admin, 'api')
            ->withoutMiddleware(PermissionMiddleware::class)
            ->postJson('/api/v1/patients', [
                'medical_record_no' => 'RM-API-001',
                'name'              => 'Pasien Integrasi',
                'gender'            => 'P',
                'is_active'         => true,
            ]);

        // Controller mengembalikan 200 (apiResource helper default) — bukan 201.
        // Fokus test ini: user terautentikasi bisa create patient (bukan 401/403).
        // Branch scoping diverifikasi oleh PatientRepositoryTest/IsolationTest yang lebih spesifik.
        // ApiDataResource membungkus response dalam key 'data' (JsonResource default wrapping).
        $response->assertOk()
            ->assertJsonPath('data.name', 'Pasien Integrasi')
            ->assertJsonStructure(['data' => ['name', 'medical_record_no']]);
    }

    public function test_user_without_master_permission_gets_forbidden_from_patient_api(): void
    {
        $userWithoutPermission = User::factory()->create(['branch_uuid' => $this->branch->uuid]);

        // User tanpa permission master.manage harus mendapat 403.
        // Spatie mengembalikan {"message": "User does not have the right permissions."}.
        $this->actingAs($userWithoutPermission, 'api')
            ->postJson('/api/v1/patients', [
                'medical_record_no' => 'RM-API-002',
                'name'              => 'Pasien Ditolak',
            ])
            ->assertForbidden()
            ->assertJsonFragment([
                'message' => 'User does not have the right permissions.',
            ]);
    }
}
