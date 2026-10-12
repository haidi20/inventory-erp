<?php
namespace App\Modules\Core\Authorization\Entities;
class PermissionListResultEntity {
    public function __construct(public array $items, public int $total, public int $per_page, public int $current_page) {}
}
