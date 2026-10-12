<?php

namespace Tests\Feature\Controllers\MasterData;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Operational\Master\Models\LookupType;
use App\Modules\Operational\Master\Models\LookupValue;
use App\Modules\Operational\Master\Models\LookupSet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class LookupControllerTest extends TestCase
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
        // Pivot user_companies sudah dibuat otomatis oleh compat mutator
        // User::setCompanyUuidAttribute (is_default=true).
        $this->withoutMiddleware();
    }

    private function apiGet(string $url): TestResponse
    {
        return $this->actingAs($this->user)
            ->withHeader('X-Klinik-Company-Uuid', $this->company->uuid)
            ->getJson($url);
    }

    private function apiPost(string $url, array $data): TestResponse
    {
        return $this->actingAs($this->user)
            ->withHeader('X-Klinik-Company-Uuid', $this->company->uuid)
            ->postJson($url, $data);
    }

    private function apiPut(string $url, array $data): TestResponse
    {
        return $this->actingAs($this->user)
            ->withHeader('X-Klinik-Company-Uuid', $this->company->uuid)
            ->putJson($url, $data);
    }

    private function apiDelete(string $url): TestResponse
    {
        return $this->actingAs($this->user)
            ->withHeader('X-Klinik-Company-Uuid', $this->company->uuid)
            ->deleteJson($url);
    }

    public function test_can_list_lov_types(): void
    {
        LookupType::factory()->count(3)->create(['company_uuid' => $this->company->uuid]);

        $response = $this->apiGet('/api/v1/lov/types');

        $response->assertOk();
        $data = $response->json('data') ?? $response->json('items') ?? [];
        $this->assertGreaterThanOrEqual(3, count($data));
    }

    public function test_can_create_lov_type(): void
    {
        $response = $this->apiPost('/api/v1/lov/types', [
            'code'        => 'BLOOD_TYPE_TEST',
            'name'        => 'Golongan Darah',
            'description' => 'Test',
            'is_active'   => true,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('lookup_types', [
            'code'         => 'BLOOD_TYPE_TEST',
            'company_uuid' => $this->company->uuid,
        ]);
    }

    public function test_can_list_values_by_type_code(): void
    {
        $type = LookupType::factory()->create([
            'code'         => 'GENDER_TEST',
            'company_uuid' => $this->company->uuid,
        ]);
        LookupValue::factory()->create([
            'lookup_set_uuid' => $type->uuid,
            'code'            => 'MALE',
            'value'           => 'Laki-laki',
            'display_name'    => 'Laki-laki',
            'order_priority'  => 1,
            'company_uuid'    => $this->company->uuid,
        ]);

        $response = $this->apiGet("/api/v1/lov/types/{$type->code}/values");

        $response->assertOk();
        $items = $response->json('data') ?? $response->json('items') ?? [];
        $this->assertGreaterThanOrEqual(1, count($items));
    }

    public function test_can_add_value_to_type(): void
    {
        $type = LookupType::factory()->create([
            'code'         => 'STATUS_TEST',
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->apiPost("/api/v1/lov/types/{$type->code}/values", [
            'code'           => 'ACTIVE',
            'value'          => 'Aktif',
            'display_name'   => 'Aktif',
            'order_priority' => 1,
        ]);

        $response->assertOk();
        // API auto-fill company_uuid dari konteks multi-org (Section 30).
        $this->assertDatabaseHas('lookup_values', [
            'lookup_set_uuid' => $type->uuid,
            'display_name'    => 'Aktif',
            'company_uuid'    => $this->company->uuid,
        ]);
    }

    public function test_can_delete_lov_type(): void
    {
        $type = LookupType::factory()->create(['company_uuid' => $this->company->uuid]);

        $response = $this->apiDelete("/api/v1/lov/types/{$type->uuid}");

        $response->assertNoContent();
        $deleted = LookupType::withTrashed()->where('uuid', $type->uuid)->first();
        $this->assertNotNull($deleted->deleted_at);
    }

    public function test_can_list_lov_sets(): void
    {
        LookupSet::factory()->count(3)->create(['company_uuid' => $this->company->uuid]);

        $response = $this->apiGet('/api/v1/lov/sets');

        $response->assertOk();
        $data = $response->json('data') ?? [];
        $this->assertGreaterThanOrEqual(3, count($data));
    }

    public function test_can_create_lov_set(): void
    {
        $response = $this->apiPost('/api/v1/lov/sets', [
            'name'         => 'Set Test Baru',
            'program_code' => 'SET_TEST_CTRL',
            'description'  => 'Keterangan test',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('lookup_sets', [
            'program_code' => 'SET_TEST_CTRL',
            'company_uuid' => $this->company->uuid,
        ]);
    }

    public function test_can_show_lov_set(): void
    {
        $set = LookupSet::factory()->create(['company_uuid' => $this->company->uuid]);

        $response = $this->apiGet("/api/v1/lov/sets/{$set->uuid}");

        $response->assertOk();
        $uuid = $response->json('data.uuid') ?? $response->json('uuid');
        $this->assertEquals($set->uuid, $uuid);
    }

    public function test_can_update_lov_set(): void
    {
        $set = LookupSet::factory()->create(['company_uuid' => $this->company->uuid]);

        $response = $this->apiPut("/api/v1/lov/sets/{$set->uuid}", [
            'name'        => 'Nama Set Diperbarui',
            'description' => 'Keterangan baru',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('lookup_sets', [
            'uuid' => $set->uuid,
            'name' => 'Nama Set Diperbarui',
        ]);
    }

    public function test_can_add_value_to_lov_set(): void
    {
        $set = LookupSet::factory()->create(['company_uuid' => $this->company->uuid]);

        $response = $this->apiPost("/api/v1/lov/sets/{$set->uuid}/values", [
            'program_code1'  => 'VAL001',
            'value'          => 'Nilai Pertama',
            'display_name'   => 'Nilai Pertama',
            'order_priority' => 1,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('lookup_values', [
            'lookup_set_uuid' => $set->uuid,
            'display_name'    => 'Nilai Pertama',
        ]);
    }

    public function test_can_delete_lov_set(): void
    {
        $set = LookupSet::factory()->create(['company_uuid' => $this->company->uuid]);

        $response = $this->apiDelete("/api/v1/lov/sets/{$set->uuid}");

        $response->assertNoContent();
        $deleted = LookupSet::withTrashed()->where('uuid', $set->uuid)->first();
        $this->assertNotNull($deleted->deleted_at);
    }
}
