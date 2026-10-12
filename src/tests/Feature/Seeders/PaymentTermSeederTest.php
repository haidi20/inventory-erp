<?php

namespace Tests\Feature\Seeders;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\PaymentTerm;
use Database\Seeders\V1\PaymentTermSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTermSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_payment_terms_from_ninesoft(): void
    {
        $company = Company::factory()->create(['name' => 'Klinik Seed Test Term']);
        Branch::factory()->create(['short_name' => 'KDM', 'company_uuid' => $company->uuid]);

        $this->seed(PaymentTermSeeder::class);

        $t0 = PaymentTerm::query()->where('name', 'T0')->first();
        $this->assertNotNull($t0, 'T0 (Cash) harus ada');
        $this->assertSame(0, (int) $t0->due_days);

        $t30 = PaymentTerm::query()->where('name', '30')->first();
        $this->assertNotNull($t30, '30 hari harus ada');
        $this->assertSame(30, (int) $t30->due_days);
    }

    public function test_seeder_creates_minimum_17_terms(): void
    {
        $company = Company::factory()->create();
        Branch::factory()->create(['short_name' => 'KDM', 'company_uuid' => $company->uuid]);

        $this->seed(PaymentTermSeeder::class);

        $this->assertGreaterThanOrEqual(17, PaymentTerm::query()->count());
    }

    public function test_seeder_is_idempotent(): void
    {
        $company = Company::factory()->create();
        Branch::factory()->create(['short_name' => 'KDM', 'company_uuid' => $company->uuid]);

        $this->seed(PaymentTermSeeder::class);
        $first = PaymentTerm::query()->count();
        $this->seed(PaymentTermSeeder::class);
        $second = PaymentTerm::query()->count();

        $this->assertSame($first, $second);
    }
}