<?php

namespace Tests\Feature\Operational;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Clinical\MedicalRecord\Models\Complaint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use App\Modules\Core\Identity\Models\User;

class ComplaintFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);
        
        config(['cache.default' => 'array']);
        config(['permission.cache.store' => 'array']);

        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_uuid' => $company->uuid]);
        $this->user = User::factory()->create([
            'company_uuid' => $company->uuid,
            'branch_uuid' => $branch->uuid
        ]);
    }

    /** @test */
    public function it_can_submit_a_complaint_via_web()
    {
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
        
        $data = [
            'type' => 'pengaduan',
            'subject' => 'Antrian Terlalu Lama',
            'content' => 'Saya menunggu lebih dari 2 jam di poli gigi.',
            'is_anonymous' => false,
            'branch_uuid' => $this->user->branch_uuid
        ];

        $response = $this->actingAs($this->user)
            ->post('/operational/complaints', $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('complaints', [
            'subject' => 'Antrian Terlalu Lama',
            'type' => 'pengaduan'
        ]);
    }

    /** @test */
    public function it_can_submit_a_complaint_via_api()
    {
        $data = [
            'type' => 'saran',
            'subject' => 'Penambahan Kursi Tunggu',
            'content' => 'Mohon tambahkan kursi di ruang tunggu depan.',
            'is_anonymous' => true
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/operational/complaints', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.subject', 'Penambahan Kursi Tunggu');
            
        $this->assertDatabaseHas('complaints', [
            'subject' => 'Penambahan Kursi Tunggu',
            'is_anonymous' => true,
            'branch_uuid' => $this->user->branch_uuid // Verified scoping
        ]);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/operational/complaints', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['subject', 'content', 'type']);
    }
}
