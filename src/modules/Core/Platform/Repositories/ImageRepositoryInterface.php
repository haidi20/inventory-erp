<?php

namespace App\Modules\Core\Platform\Repositories;

use App\Modules\Core\Platform\Models\File;

/**
 * Interface ImageRepositoryInterface
 *
 * Kontrak khusus untuk pengelolaan image yang di-hosting oleh FileRepository.
 */
interface ImageRepositoryInterface
{
    public function createImage(array $data): File;
    public function createVariants(array $variants): bool;
    public function createAudit(array $auditData): bool;
    public function findByParent(string $parentType, string $parentUuid): array;
    public function findByUUID(string $imageUuid): ?File;
    public function deleteByUUID(string $imageUuid, string $actorUuid): bool;
}
