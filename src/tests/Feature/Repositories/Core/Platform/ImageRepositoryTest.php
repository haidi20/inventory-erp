<?php

namespace Tests\Feature\Repositories\Core\Platform;

use App\Modules\Core\Platform\Repositories\ImageRepository;
use Tests\TestCase;

class ImageRepositoryTest extends TestCase
{

    public function test_create_image(): void
    {
        $method = new \ReflectionMethod(ImageRepository::class, 'createImage');
        $this->assertTrue($method->isPublic());
    }

    public function test_create_variants(): void
    {
        $method = new \ReflectionMethod(ImageRepository::class, 'createVariants');
        $this->assertTrue($method->isPublic());
    }

    public function test_create_audit(): void
    {
        $method = new \ReflectionMethod(ImageRepository::class, 'createAudit');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_by_parent(): void
    {
        $method = new \ReflectionMethod(ImageRepository::class, 'findByParent');
        $this->assertTrue($method->isPublic());
    }

    public function test_find_by_u_u_i_d(): void
    {
        $method = new \ReflectionMethod(ImageRepository::class, 'findByUUID');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete_by_u_u_i_d(): void
    {
        $method = new \ReflectionMethod(ImageRepository::class, 'deleteByUUID');
        $this->assertTrue($method->isPublic());
    }
}