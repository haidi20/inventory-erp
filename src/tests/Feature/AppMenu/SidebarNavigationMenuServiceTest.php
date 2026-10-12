<?php

namespace Tests\Feature\AppMenu;

use App\Modules\Core\Platform\Entities\SidebarNavigationMenuEntity;
use App\Modules\Core\Platform\Repositories\SidebarNavigationMenuRepository;
use App\Modules\Core\Platform\Services\SidebarNavigationMenuService;
use Tests\TestCase;

class SidebarNavigationMenuServiceTest extends TestCase
{
    public function test_service_builds_sidebar_menu_from_libraries_config_and_marks_active_child(): void
    {
        $service = new SidebarNavigationMenuService(
            new SidebarNavigationMenuRepository()
        );

        $menus = $service->getSidebarMenus(
            SidebarNavigationMenuEntity::fromArray([
                'current_path' => 'settings/backup',
                'permission_names' => [
                    'dashboard.view',
                    'settings.view',
                ],
            ])
        );

        $this->assertSame(['Dashboard', 'Pengaturan'], array_column($menus, 'label'));

        $settingsMenu = collect($menus)->firstWhere('key', 'settings');

        $this->assertNotNull($settingsMenu);
        $this->assertTrue($settingsMenu['is_open']);

        $profileMenu = collect($settingsMenu['items'])->firstWhere('label', 'Profil Klinik');
        $backupMenu = collect($settingsMenu['items'])->firstWhere('label', 'Backup Data');

        $this->assertFalse($profileMenu['is_active']);
        $this->assertTrue($backupMenu['is_active']);
        $this->assertSame('/settings/backup', $backupMenu['url']);
    }
}
