<?php

namespace Tests\Unit\Services\AppMenu;

use App\Modules\Core\Identity\Entities\UserEntity;
use App\Modules\Core\Authorization\Repositories\AppMenuRepositoryInterface;
use App\Modules\Core\Platform\Repositories\SidebarNavigationMenuRepository;
use App\Modules\Core\Platform\Services\SidebarNavigationMenuService;
use Mockery;
use Tests\TestCase;

class SidebarNavigationMenuServiceTest extends TestCase
{
    private $sidebarRepoMock;
    private $appMenuRepoMock;
    private SidebarNavigationMenuService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sidebarRepoMock = Mockery::mock(SidebarNavigationMenuRepository::class);
        $this->appMenuRepoMock = Mockery::mock(AppMenuRepositoryInterface::class);
        $this->service = new SidebarNavigationMenuService($this->sidebarRepoMock, $this->appMenuRepoMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_sidebar_menus_by_user_returns_filtered_menus()
    {
        $user = new UserEntity(uuid: 'user-123');
        $permissions = ['view_dashboard', 'view_reports'];
        $rawMenu = [
            [
                'label' => 'Dashboard',
                'url' => '/dashboard',
                'permission' => 'view_dashboard',
                'items' => []
            ],
            [
                'label' => 'Admin',
                'permission' => 'admin_only',
                'items' => [
                    ['label' => 'Settings', 'url' => '/settings', 'permission' => 'view_settings']
                ]
            ]
        ];

        $this->appMenuRepoMock->shouldReceive('getUserPermissionNames')->once()->with($user->uuid)->andReturn($permissions);
        $this->sidebarRepoMock->shouldReceive('getAll')->once()->andReturn($rawMenu);

        $result = $this->service->getSidebarMenusByUser($user);

        $this->assertCount(1, $result);
        $this->assertEquals('Dashboard', $result[0]['label']);
    }

    public function test_get_sidebar_menus_by_user_throws_exception_if_uuid_empty()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->getSidebarMenusByUser(new UserEntity());
    }

    public function test_get_sidebar_menus_by_user_throws_exception_if_repo_is_null()
    {
        $service = new SidebarNavigationMenuService($this->sidebarRepoMock, null);
        $this->expectException(\RuntimeException::class);
        $service->getSidebarMenusByUser(new UserEntity(uuid: 'uuid'));
    }
}
