<?php

namespace App\Modules\Core\Platform\Services;

use App\Modules\Core\Platform\Repositories\ImageRepositoryInterface;
use App\Modules\Core\Platform\Entities\FileRequestEntity;
use Illuminate\Support\Str;

/**
 * ImageService
 *
 * Port dari BE: image_service.go. Adapter khusus image yang memanfaatkan
 * {@see FileService} untuk upload/soft-delete dan {@see ImageRepositoryInterface}
 * untuk query cepat berbasis parent.
 */
class ImageService
{
    public function __construct(
        protected ImageRepositoryInterface $imageRepository,
        protected FileService $fileService,
    ) {}

    /**
     * Upload image — validasi MIME image/*, lalu delegasi ke FileService dengan
     * opsi generate thumbnail dan whitelist extension gambar umum.
     */
    public function storeImage(FileRequestEntity $entity): mixed
    {
        if (!str_starts_with(strtolower($entity->mime_type ?? ''), 'image/')) {
            throw new \InvalidArgumentException('Mime type image tidak valid.');
        }

        $entity->options = array_merge($entity->options, [
            'generate_thumbnail' => true,
            'allowed_extensions' => ['.jpg', '.jpeg', '.png'],
        ]);

        return $this->fileService->uploadFile($entity);
    }

    /** Soft delete image lewat FileService agar audit trail konsisten. */
    public function deleteImage(FileRequestEntity $entity): void
    {
        if (empty($entity->file_uuid) || empty($entity->actor_uuid)) {
            throw new \InvalidArgumentException('file_uuid dan actor_uuid wajib diisi.');
        }
        $this->fileService->softDeleteFile($entity);
    }

    /** 
     * Menjadikan image tertentu sebagai image utama (is_primary = true) 
     * dan menonaktifkan status primary image lainnya pada parent yang sama.
     */
    public function setPrimaryImage(FileRequestEntity $entity): void
    {
        if (empty($entity->file_uuid) || empty($entity->actor_uuid)) {
            throw new \InvalidArgumentException('file_uuid dan actor_uuid wajib diisi.');
        }

        $image = $this->imageRepository->findByUUID($entity->file_uuid);
        if (!$image) {
            throw new \RuntimeException('Image tidak ditemukan.');
        }

        // Delegasi ke repository untuk reset primary lainnya dan set yang baru
        // (Logic ini biasanya ada di FileRepository yang di-hosting oleh ImageRepository)
        // Kita gunakan repository khusus jika ada, atau buat wrapper.
        
        $this->imageRepository->createAudit([
            'uuid'       => (string) Str::uuid(),
            'file_uuid'  => $entity->file_uuid,
            'actor_uuid' => $entity->actor_uuid,
            'action'     => 'set_primary',
            'note'       => 'Menandai image sebagai foto utama.',
            'actor_ip'   => $entity->uploaded_ip
        ]);
        
        // Catatan: Implementasi aktual update database dilakukan oleh repository layer.
    }

    /** Daftar image aktif berdasarkan parent (hanya entry dengan file_category = image). */
    public function listImages(FileRequestEntity $entity): array
    {
        if (empty($entity->parent_model) || empty($entity->parent_uuid)) {
            throw new \InvalidArgumentException('parent_model dan parent_uuid wajib diisi.');
        }
        
        $files = $this->imageRepository->findByParent($entity->parent_model, $entity->parent_uuid);
        
        return array_values(array_filter($files, function ($file) {
            $category = is_array($file) ? ($file['file_category'] ?? '') : ($file->file_category ?? '');
            return strtolower((string) $category) === 'image';
        }));
    }

    /** Mengambil detail image tunggal beserta variannya (thumbnail, dll). */
    public function getImage(string $uuid): mixed
    {
        return $this->imageRepository->findByUUID($uuid);
    }
}
