<?php

namespace Tests\Feature\Seeders;

use App\Modules\Core\Identity\Models\User;
use Database\Seeders\V1\V1DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class V1DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_the_superadmin_account_for_e2e_authentication(): void
    {
        $this->seed(V1DatabaseSeeder::class);

        $superadmin = User::query()->where('email', 'superadmin@klinik.local')->first();

        $this->assertNotNull($superadmin);
        $this->assertTrue($superadmin->hasRole('superadmin'));
    }
}
