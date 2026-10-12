<?php

namespace App\Http\View\Composers;

use App\Modules\Core\Organization\Models\Company;
use App\Modules\Core\Platform\Entities\SidebarNavigationMenuEntity;
use App\Modules\Core\Platform\Services\SidebarNavigationMenuService;
use Illuminate\View\View;

class SidebarComposer
{
    public function __construct(
        private SidebarNavigationMenuService $service
    ) {}

    public function compose(View $view): void
    {
        $user = auth()->user();
        if (!$user) {
            $view->with('sidebarMenus', []);
            $view->with('sidebarCompanyName', '');
            return;
        }

        // Superadmin bypass: pakai wildcard agar semua menu tampil
        $isSuperAdmin = $user->isSuperAdmin();
        if ($isSuperAdmin) {
            $permissions = ['*'];
        } else {
            $permissions = $user->getAllPermissions()->pluck('name')->toArray();
        }
        $currentPath = request()->path() === '/' ? '' : request()->path();

        $entity = SidebarNavigationMenuEntity::fromArray([
            'current_path' => $currentPath,
            'permission_names' => $permissions,
        ]);

        $sidebarMenus = $this->service->getSidebarMenus($entity);

        $companyName = '';
        $companyUuid = $user->active_company_uuid;
        if ($companyUuid) {
            $company = Company::withoutGlobalScopes()->where('uuid', $companyUuid)->first();
            $companyName = $company?->name ?? '';
        }

        $view->with('sidebarMenus', $sidebarMenus);
        $view->with('sidebarCompanyName', $companyName);
    }
}
