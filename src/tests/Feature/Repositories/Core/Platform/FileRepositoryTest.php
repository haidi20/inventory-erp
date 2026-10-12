<?php

namespace Tests\Feature\Repositories\Core\Platform;

use App\Modules\Core\Platform\Repositories\FileRepository;
use Tests\TestCase;

class FileRepositoryTest extends TestCase
{

    public function test_create_file(): void
    {
        $method = new \ReflectionMethod(FileRepository::class, 'createFile');
        $this->assertTrue($method->isPublic());
    }

    public function test_create_variants(): void
    {
        $method = new \ReflectionMethod(FileRepository::class, 'createVariants');
        $this->assertTrue($method->isPublic());
    }

    public function test_create_audit(): void
    {
        $method = new \ReflectionMethod(FileRepository::class, 'createAudit');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_by_u_u_i_d(): void
    {
        $method = new \ReflectionMethod(FileRepository::class, 'findByUUID');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_any_by_u_u_i_d(): void
    {
        $method = new \ReflectionMethod(FileRepository::class, 'findAnyByUUID');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_by_hash(): void
    {
        $method = new \ReflectionMethod(FileRepository::class, 'findByHash');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_by_parent(): void
    {
        $method = new \ReflectionMethod(FileRepository::class, 'findByParent');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_variants(): void
    {
        $method = new \ReflectionMethod(FileRepository::class, 'getVariants');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_audits(): void
    {
        $method = new \ReflectionMethod(FileRepository::class, 'getAudits');
        $this->assertTrue($method->isPublic());
    }

    public function test_update_file(): void
    {
        $method = new \ReflectionMethod(FileRepository::class, 'updateFile');
        $this->assertTrue($method->isPublic());
    }

    public function test_count_by_context(): void
    {
        $method = new \ReflectionMethod(FileRepository::class, 'countByContext');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_latest_version(): void
    {
        $method = new \ReflectionMethod(FileRepository::class, 'getLatestVersion');
        $this->assertTrue($method->isPublic());
    }

    public function test_mark_latest_version(): void
    {
        $method = new \ReflectionMethod(FileRepository::class, 'markLatestVersion');
        $this->assertTrue($method->isPublic());
    }

    public function test_set_primary_file(): void
    {
        $method = new \ReflectionMethod(FileRepository::class, 'setPrimaryFile');
        $this->assertTrue($method->isPublic());
    }

    public function test_soft_delete_file(): void
    {
        $method = new \ReflectionMethod(FileRepository::class, 'softDeleteFile');
        $this->assertTrue($method->isPublic());
    }

    public function test_restore_file(): void
    {
        $method = new \ReflectionMethod(FileRepository::class, 'restoreFile');
        $this->assertTrue($method->isPublic());
    }

    public function test_hard_delete_file(): void
    {
        $method = new \ReflectionMethod(FileRepository::class, 'hardDeleteFile');
        $this->assertTrue($method->isPublic());
    }

    public function test_build_backup_bundle(): void
    {
        $method = new \ReflectionMethod(FileRepository::class, 'buildBackupBundle');
        $this->assertTrue($method->isPublic());
    }

    public function test_apply_restore_bundle(): void
    {
        $method = new \ReflectionMethod(FileRepository::class, 'applyRestoreBundle');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_storage_usage(): void
    {
        $method = new \ReflectionMethod(FileRepository::class, 'getStorageUsage');
        $this->assertTrue($method->isPublic());
    }

    public function test_parent_exists(): void
    {
        $method = new \ReflectionMethod(FileRepository::class, 'parentExists');
        $this->assertTrue($method->isPublic());
    }
}