<?php
namespace App\Modules\Core\Authorization\Entities;
class PermissionEntity {
    public function __construct(public ?string $uuid = null, public string $name = "", public ?string $label = null, public string $guard_name = "api", public ?string $created_by = null, public ?string $updated_by = null) {}
}
