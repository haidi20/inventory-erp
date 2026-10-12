<?php
namespace App\Modules\Core\Authorization\Entities;
class PermissionFilterEntity {
    public function __construct(public ?string $search = null, public int $page = 1, public int $per_page = 10) {}
}
