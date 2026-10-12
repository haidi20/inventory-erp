<?php

namespace App\Modules\Core\Platform\Entities;

class SidebarNavigationMenuEntity
{
    public function __construct(
        public string $current_path = '',
        public array $permission_names = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        $permissionNames = array_values(array_unique(array_filter(
            array_map(
                static fn ($permission): ?string => is_scalar($permission) ? trim((string) $permission) : null,
                $data['permission_names'] ?? []
            ),
            static fn (?string $permission): bool => $permission !== null && $permission !== ''
        )));

        return new self(
            current_path: is_scalar($data['current_path'] ?? null)
                ? trim((string) $data['current_path'], '/')
                : '',
            permission_names: $permissionNames,
        );
    }
}
