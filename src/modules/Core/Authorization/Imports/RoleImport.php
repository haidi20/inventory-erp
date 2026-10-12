<?php

namespace App\Modules\Core\Authorization\Imports;

use App\Modules\Core\Authorization\Models\Role;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class RoleImport implements ToModel, WithHeadingRow
{
    protected string $actorUuid;

    public function __construct(string $actorUuid)
    {
        $this->actorUuid = $actorUuid;
    }

    public function model(array $row)
    {
        if (empty($row['name'])) {
            return null;
        }

        return new Role([
            'name' => $row['name'],
            'label' => $row['label'] ?? $row['name'],
        ]);
    }
}
