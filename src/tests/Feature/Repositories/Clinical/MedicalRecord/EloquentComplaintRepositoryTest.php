<?php

namespace Tests\Feature\Repositories\Clinical\MedicalRecord;

use App\Modules\Clinical\MedicalRecord\Repositories\EloquentComplaintRepository;
use Tests\TestCase;

class EloquentComplaintRepositoryTest extends TestCase
{

    public function test_paginate(): void
    {
        $method = new \ReflectionMethod(EloquentComplaintRepository::class, 'paginate');
        $this->assertTrue($method->isPublic());
    }

    public function test_save(): void
    {
        $method = new \ReflectionMethod(EloquentComplaintRepository::class, 'save');
        $this->assertTrue($method->isPublic());
    }

    public function test_find(): void
    {
        $method = new \ReflectionMethod(EloquentComplaintRepository::class, 'find');
        $this->assertTrue($method->isPublic());
    }

    public function test_delete(): void
    {
        $method = new \ReflectionMethod(EloquentComplaintRepository::class, 'delete');
        $this->assertTrue($method->isPublic());
    }
}