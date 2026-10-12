<?php

namespace App\Modules\Core\Platform\Services;

use App\Modules\Core\Platform\Entities\SidebarNavigationMenuEntity;
use App\Modules\Core\Identity\Entities\UserEntity;
use App\Modules\Core\Authorization\Repositories\AppMenuRepositoryInterface;
use App\Modules\Core\Platform\Repositories\SidebarNavigationMenuRepository;
use Illuminate\Support\Str;

class SidebarNavigationMenuService
{
    public function __construct(
        private SidebarNavigationMenuRepository $sidebarNavigationMenuRepository,
        private ?AppMenuRepositoryInterface $appMenuRepository = null,
    ) {}

    public function getSidebarMenusByUser(UserEntity $user, string $currentPath = ''): array
    {
        if (empty($user->uuid)) {
            throw new \InvalidArgumentException('user id wajib diisi.');
        }

        if ($this->appMenuRepository === null) {
            throw new \RuntimeException('AppMenuRepository belum tersedia untuk resolve permission user.');
        }

        $permissionNames = $this->appMenuRepository->getUserPermissionNames($user->uuid);

        return $this->getSidebarMenus(
            SidebarNavigationMenuEntity::fromArray([
                'current_path' => $currentPath,
                'permission_names' => $permissionNames,
            ])
        );
    }

    public function getSidebarMenus(SidebarNavigationMenuEntity $sidebarNavigationMenuEntity): array
    {
        $configuredSidebarMenu = $this->sidebarNavigationMenuRepository->getAll(
            $sidebarNavigationMenuEntity
        );

        $sidebarMenus = array_map(
            fn (array $menu): ?array => $this->transformSidebarMenu($menu, $sidebarNavigationMenuEntity),
            array_filter($configuredSidebarMenu, 'is_array')
        );

        return array_values(array_filter($sidebarMenus));
    }

    private function transformSidebarMenu(array $menu, SidebarNavigationMenuEntity $sidebarNavigationMenuEntity): ?array
    {
        $hasConfiguredItems = is_array($menu['items'] ?? null) && $menu['items'] !== [];
        $items = array_values(array_filter(
            array_map(
                fn (array $item): ?array => $this->transformSidebarLink($item, $sidebarNavigationMenuEntity),
                array_filter($menu['items'] ?? [], 'is_array')
            )
        ));

        if ($items !== []) {
            $hasActiveChild = collect($items)->contains(
                fn (array $item): bool => (bool) ($item['is_active'] ?? false)
            );
            $hasGroupPermission = $this->isVisibleForPermissions($menu, $sidebarNavigationMenuEntity->permission_names);

            if (! $hasGroupPermission) {
                return null;
            }

            return [
                'key'       => (string) ($menu['key'] ?? Str::slug((string) ($menu['label'] ?? 'menu'))),
                'type'      => 'group',
                'label'     => (string) ($menu['label'] ?? ''),
                'icon'      => (string) ($menu['icon'] ?? 'fa-circle'),
                'url'       => isset($menu['url']) && is_string($menu['url']) ? $menu['url'] : null,
                'items'     => $items,
                'is_active' => $hasActiveChild,
                'is_open'   => $hasActiveChild,
            ];
        }

        if ($hasConfiguredItems) {
            return null;
        }

        return $this->transformSidebarLink($menu, $sidebarNavigationMenuEntity);
    }

    private function transformSidebarLink(array $menu, SidebarNavigationMenuEntity $sidebarNavigationMenuEntity): ?array
    {
        if (! $this->isVisibleForPermissions($menu, $sidebarNavigationMenuEntity->permission_names)) {
            return null;
        }

        return [
            'key'       => (string) ($menu['key'] ?? Str::slug((string) ($menu['label'] ?? 'menu'))),
            'type'      => 'link',
            'label'     => (string) ($menu['label'] ?? ''),
            'icon'      => isset($menu['icon']) && is_string($menu['icon']) ? $menu['icon'] : null,
            'url'       => (string) ($menu['url'] ?? '#'),
            'is_active' => $this->matchesCurrentPath($menu, $sidebarNavigationMenuEntity->current_path),
        ];
    }

    private function isVisibleForPermissions(array $menu, array $permissionNames): bool
    {
        $requiredPermission = is_scalar($menu['permission'] ?? null)
            ? trim((string) $menu['permission'])
            : '';

        if ($requiredPermission === '') {
            return true;
        }

        if (in_array('*', $permissionNames, true)) {
            return true;
        }

        return in_array($requiredPermission, $permissionNames, true);
    }

    private function matchesCurrentPath(array $menu, string $currentPath): bool
    {
        $activeSegment = is_scalar($menu['active_segment'] ?? null)
            ? trim((string) $menu['active_segment'], '/')
            : trim((string) ($menu['url'] ?? ''), '/');

        if ($activeSegment === '' && $currentPath === '') {
            return true;
        }

        return $activeSegment !== '' && Str::is($activeSegment, $currentPath);
    }
}