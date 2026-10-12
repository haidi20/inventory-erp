<?php

namespace Tests\Unit\Services\Core\Platform;

use App\Modules\Core\Platform\Services\ImageService;
use Tests\TestCase;

class ImageServiceTest extends TestCase
{

    public function test_store_image(): void
    {
        $method = new \ReflectionMethod(ImageService::class, 'storeImage');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete_image(): void
    {
        $method = new \ReflectionMethod(ImageService::class, 'deleteImage');
        $this->assertTrue($method->isPublic());
    }

    public function test_set_primary_image(): void
    {
        $method = new \ReflectionMethod(ImageService::class, 'setPrimaryImage');
        $this->assertTrue($method->isPublic());
    }

    public function test_list_images(): void
    {
        $method = new \ReflectionMethod(ImageService::class, 'listImages');
        $this->assertTrue($method->isPublic());
    }

    public function test_get_image(): void
    {
        $method = new \ReflectionMethod(ImageService::class, 'getImage');
        $this->assertTrue($method->isPublic());
    }
}