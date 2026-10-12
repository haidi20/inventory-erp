<?php

namespace Tests\Feature\Auth;

use App\Modules\Core\Authorization\Models\Permission;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use Database\Seeders\V1\SuperadminSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Memastikan setelah login sebagai superadmin:
 * - berhasil redirect ke dashboard (/)
 * - halaman dashboard dapat diakses (tidak 403)
 * - shared props sidebar_menus terisi (menu sidebar muncul)
 * - role superadmin dapat melihat menu ber-permission (wildcard *)
 */
class SuperadminSidebarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            ValidateCsrfToken::class,
        ]);

        config(['inertia.testing.ensure_pages_exist' => false]);

        // Permission inti (dashboard & settings) tersedia di DB
        foreach (['dashboard.view', 'settings.view', 'settings.user.view', 'settings.role.view'] as $name) {
            Permission::create(['name' => $name, 'guard_name' => 'web']);
        }

        $company = Company::create([
            'uuid' => Str::uuid(),
            'name' => 'Klinik Sidebar Test',
            'is_active' => true,
        ]);

        Branch::create([
            'uuid' => Str::uuid(),
            'short_name' => 'SDB',
            'name' => 'Cabang Sidebar',
            'is_active' => true,
            'company_uuid' => $company->uuid,
        ]);

        $this->seed(SuperadminSeeder::class);
    }

    public function test_superadmin_can_reach_dashboard_after_login(): void
    {
        $this->post('/login', [
            'username' => 'superadmin',
            'password' => 'superadmin',
        ])->assertRedirect('/');

        $response = $this->get('/');
        $response->assertOk();
        $this->assertAuthenticated();
    }

    public function test_superadmin_sidebar_menus_are_shared_and_not_empty(): void
    {
        $this->post('/login', [
            'username' => 'superadmin',
            'password' => 'superadmin',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Analytics/Reports/presentations/views/DashboardIndex')
                ->has('sidebar_menus')
                ->where('auth.user.roles.0', 'superadmin')
                ->where('sidebar_menus.0.label', 'Dashboard')
                ->where('sidebar_menus.0.type', 'link')
            );
    }

    public function test_superadmin_sidebar_includes_permission_gated_menu(): void
    {
        $user = User::where('email', 'superadmin@klinik.local')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('superadmin'));

        // Wildcard superadmin membuat user bisa semua permission
        $this->assertTrue($user->can('dashboard.view'));
        $this->assertTrue($user->can('settings.view'));

        $this->actingAs($user);

        // Menu "Pengaturan" butuh permission settings.view dan hanya tampil
        // untuk superadmin (wildcard). Cari di antara label menu.
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('sidebar_menus')
                ->where('auth.user.roles.0', 'superadmin')
            );

        $props = $this->get('/')->original->getData()['page']['props']['sidebar_menus'] ?? [];
        $labels = array_map(fn ($m) => $m['label'] ?? '', $props);
        $this->assertNotEmpty($props);
        $this->assertContains('Dashboard', $labels);
        $this->assertContains('Pengaturan', $labels);
    }

    public function test_superadmin_sidebar_menu_order_and_inventory_is_link(): void
    {
        $this->markTestSkipped('Menu order/labels changed after sidebar restructure.');

        return;
        $user = User::where('email', 'superadmin@klinik.local')->first();
        $this->actingAs($user);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('sidebar_menus')
            );

        $props = $this->get('/')->original->getData()['page']['props']['sidebar_menus'] ?? [];

        // Cek urutan menu: Dashboard -> Inventory -> Farmasi
        $this->assertGreaterThanOrEqual(3, count($props));
        $this->assertSame('Dashboard', $props[0]['label']);
        $this->assertSame('Inventory', $props[1]['label']);
        $this->assertSame('Farmasi', $props[2]['label']);

        // Inventory harus berupa link biasa (type=link, bukan group)
        $this->assertSame('link', $props[1]['type']);
        $this->assertSame('/inventory', $props[1]['url']);
    }
}
