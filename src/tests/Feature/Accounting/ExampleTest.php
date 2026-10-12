<?php

namespace Tests\Feature\Accounting;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Authorization\Models\Permission;
use App\Modules\Core\Authorization\Models\Role;
use App\Modules\Core\Identity\Models\User;
use Database\Seeders\V1\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->seed(RbacSeeder::class);

        $branch = Branch::create([
            'name' => 'Test Branch',
            'short_name' => 'TST',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'branch_uuid' => $branch->uuid,
        ]);

        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $user->roles()->attach($adminRole->uuid);
            // Fix 403: Controller checks dashboard.view
            $perm = Permission::firstOrCreate(['name' => 'dashboard.view']);
            $adminRole->permissions()->syncWithoutDetaching([$perm->uuid]);
        }

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
    }
}

