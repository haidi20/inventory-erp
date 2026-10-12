<?php

namespace App\Modules\Core\Authorization\Repositories;

/**
 * Interface AppMenuRepositoryInterface
 *
 * Mendefinisikan kontrak untuk pengelolaan menu aplikasi dan permission sidebar.
 */
interface AppMenuRepositoryInterface
{
    /**
     * Mengambil definisi sidebar mentah dari konfigurasi sistem.
     *
     * @return array
     */
    public function getSidebarDefinitions(): array;

    /**
     * Mengambil daftar nama permission unik milik user berdasarkan role.
     *
     * @param string $userId UUID user.
     * @return array Daftar nama nama permission (sorted).
     */
    public function getUserPermissionNames(string $userId): array;
}
