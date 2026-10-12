<?php

namespace App\Modules\Core\Authorization\Exports;

use App\Modules\Core\Authorization\Models\Role;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RoleExport implements FromCollection, WithHeadings, WithMapping
{
    protected array $params;

    public function __construct(array $params = [])
    {
        $this->params = $params;
    }

    public function collection()
    {
        $query = Role::query()->with('permissions');

        if (!empty($this->params['search'])) {
            $s = $this->params['search'];
            $query->where('name', 'like', "%{$s}%")
                  ->orWhere('label', 'like', "%{$s}%");
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'UUID',
            'Name',
            'Label',
            'Permissions',
        ];
    }

    public function map($role): array
    {
        return [
            $role->id,
            $role->uuid,
            $role->name,
            $role->label,
            $role->permissions->pluck('name')->implode(', '),
        ];
    }
}
