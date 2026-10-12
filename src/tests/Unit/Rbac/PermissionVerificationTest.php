<?php

namespace Tests\Unit\Rbac;

use App\Modules\Core\Platform\Services\SidebarNavigationMenuService;
use App\Modules\Core\Platform\Entities\SidebarNavigationMenuEntity;
use App\Modules\Core\Platform\Repositories\SidebarNavigationMenuRepository;
use PHPUnit\Framework\TestCase;

class PermissionVerificationTest extends TestCase
{
    private SidebarNavigationMenuService $service;
    private SidebarNavigationMenuRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repository = $this->createMock(SidebarNavigationMenuRepository::class);
        $this->service = new SidebarNavigationMenuService($this->repository);
    }

    /** @test */
    public function it_filters_top_level_menus_without_permission(): void
    {
        $mockMenus = [
            [
                'key' => 'dashboard',
                'label' => 'Dashboard',
                'permission' => 'dashboard.view',
            ],
            [
                'key' => 'master',
                'label' => 'Master Data',
                'permission' => 'master.view',
                'items' => [
                    ['label' => 'Produtos', 'permission' => 'master.produk.view'],
                ]
            ],
        ];

        $this->repository->method('getAll')->willReturn($mockMenus);

        // Case 1: Only dashboard permission
        $entity = SidebarNavigationMenuEntity::fromArray([
            'current_path' => '/',
            'permission_names' => ['dashboard.view'],
        ]);

        $filtered = $this->service->getSidebarMenus($entity);

        $this->assertCount(1, $filtered);
        $this->assertEquals('dashboard', $filtered[0]['key']);

        // Case 2: Only master permission
        $entity = SidebarNavigationMenuEntity::fromArray([
            'current_path' => '/',
            'permission_names' => ['master.view', 'master.produk.view'],
        ]);

        $filtered = $this->service->getSidebarMenus($entity);

        $this->assertCount(1, $filtered);
        $this->assertEquals('master', $filtered[0]['key']);
        $this->assertCount(1, $filtered[0]['items']);

        // Case 3: Master product item permission BUT NO group permission
        $entity = SidebarNavigationMenuEntity::fromArray([
            'current_path' => '/',
            'permission_names' => ['master.produk.view'],
        ]);

        $filtered = $this->service->getSidebarMenus($entity);

        // Should be EMPTY because the top-level group 'master.view' is required
        $this->assertEmpty($filtered);
    }

    /** @test */
    public function it_hides_empty_groups(): void
    {
        $mockMenus = [
            [
                'key' => 'master',
                'label' => 'Master Data',
                'permission' => 'master.view',
                'items' => [
                    ['label' => 'Produtos', 'permission' => 'master.produk.view'],
                ]
            ],
        ];

        $this->repository->method('getAll')->willReturn($mockMenus);

        // Only group permission, NO item permission
        $entity = SidebarNavigationMenuEntity::fromArray([
            'current_path' => '/',
            'permission_names' => ['master.view'],
        ]);

        $filtered = $this->service->getSidebarMenus($entity);

        // Should be EMPTY because the items inside are all filtered out, leaving an empty group
        $this->assertEmpty($filtered);
    }
}
