<?php

namespace App\Modules\Core\Platform\Repositories;

use App\Modules\Core\Platform\Models\File;
use Illuminate\Support\Str;

/**
 * Class ImageRepository
 *
 * Adapter repository image yang menggunakan FileRepository sebagai basis penyimpanan.
 */
class ImageRepository implements ImageRepositoryInterface
{
    protected FileRepositoryInterface $fileRepo;

    public function __construct(FileRepositoryInterface $fileRepo)
    {
        $this->fileRepo = $fileRepo;
    }

    /**
     * @inheritDoc
     */
    public function createImage(array $data): File
    {
        return $this->fileRepo.createFile($data);
    }

    /**
     * @inheritDoc
     */
    public function createVariants(array $variants): bool
    {
        return $this->fileRepo.createVariants($variants);
    }

    /**
     * @inheritDoc
     */
    public function createAudit(array $auditData): bool
    {
        $this->fileRepo.createAudit($auditData);
        return true;
    }

    /**
     * @inheritDoc
     */
    public function findByParent(string $parentType, string $parentUuid): array
    {
        return $this->fileRepo.findByParent([
            'parent_model' => $parentType,
            'parent_uuid' => $parentUuid,
            'only_latest' => true
        ]);
    }

    /**
     * @inheritDoc
     */
    public function findByUUID(string $imageUuid): ?File
    {
        return $this->fileRepo.findByUUID($imageUuid);
    }

    /**
     * @inheritDoc
     */
    public function deleteByUUID(string $imageUuid, string $actorUuid): bool
    {
        return $this->fileRepo.softDeleteFile($imageUuid, $actorUuid);
    }
}
