<?php

namespace Tests\Feature\Controllers\MasterData;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Operational\Master\Models\PaymentTerm;
use Tests\TestCase;

class PaymentTermControllerTest extends TestCase
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

    public function test_can_list_payment_terms()
    {
        PaymentTerm::factory()->count(3)->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/masters/payment-terms');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['total', 'page', 'per_page'],
            ])
            ->assertJsonPath('meta.per_page', 15);
    }

    public function test_index_only_returns_payment_terms_from_authenticated_users_company()
    {
        PaymentTerm::factory()->create([
            'name' => 'Term Company A',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        PaymentTerm::factory()->create([
            'name' => 'Term Company Lain',
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/masters/payment-terms');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Term Company A');
    }

    public function test_can_create_payment_term()
    {
        $payload = [
            'name' => 'Net 30',
            'description' => 'Pembayaran 30 hari',
            'due_days' => 30,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/v1/masters/payment-terms', $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('payment_terms', [
            'name' => 'Net 30',
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);
    }

    public function test_store_requires_name()
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/masters/payment-terms', [
            'due_days' => 15,
        ]);

        $response->assertStatus(422);
    }

    public function test_can_show_payment_term_from_same_company()
    {
        $term = PaymentTerm::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/masters/payment-terms/{$term->uuid}");

        $response->assertStatus(200)
            ->assertJsonPath('data.uuid', $term->uuid);
    }

    public function test_cannot_show_payment_term_from_another_company()
    {
        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        $term = PaymentTerm::factory()->create([
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/masters/payment-terms/{$term->uuid}");

        $response->assertStatus(404);
    }

    public function test_can_update_payment_term_from_same_company()
    {
        $term = PaymentTerm::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
            'name' => 'Nama Lama',
        ]);

        $response = $this->actingAs($this->user)->putJson("/api/v1/masters/payment-terms/{$term->uuid}", [
            'name' => 'Nama Baru',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('payment_terms', [
            'uuid' => $term->uuid,
            'name' => 'Nama Baru',
        ]);
    }

    public function test_cannot_update_payment_term_from_another_company()
    {
        $otherCompany = Company::factory()->create();
        $otherBranch = Branch::factory()->create(['company_uuid' => $otherCompany->uuid]);
        $term = PaymentTerm::factory()->create([
            'branch_uuid' => $otherBranch->uuid,
            'company_uuid' => $otherCompany->uuid,
        ]);

        $response = $this->actingAs($this->user)->putJson("/api/v1/masters/payment-terms/{$term->uuid}", [
            'name' => 'Diubah Paksa',
        ]);

        $response->assertStatus(404);
    }

    public function test_can_delete_payment_term_from_same_company()
    {
        $term = PaymentTerm::factory()->create([
            'branch_uuid' => $this->branch->uuid,
            'company_uuid' => $this->company->uuid,
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/masters/payment-terms/{$term->uuid}");

        $response->assertStatus(204);
        $this->assertNotEquals(0, $term->fresh()->deleted_at);
    }
}
