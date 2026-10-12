<?php

namespace App\Modules\Core\Platform\Repositories;

use App\Modules\Core\Platform\Entities\SidebarNavigationMenuEntity;

class SidebarNavigationMenuRepository
{
    public function getAll(SidebarNavigationMenuEntity $sidebarNavigationMenuEntity): array
    {
        unset($sidebarNavigationMenuEntity);

        $configuredSidebarMenu = config('menus.sidebar_menu', []);

        return is_array($configuredSidebarMenu) ? $configuredSidebarMenu : [];
    }
}
