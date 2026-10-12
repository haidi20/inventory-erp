<?php
namespace Database\Seeders\V1;

use App\Modules\Core\Authorization\Models\Role;
use App\Modules\Core\Authorization\Models\Permission;
use Illuminate\Database\Seeder;

class InventoryPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $perms = [
            'inventory.view',
            'inventory.stock_transfer.view',
            'inventory.stock_adjustment.view',
            'inventory.stock_balance.view',
        ];

        foreach (['admin', 'owner', 'superadmin'] as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($perms);
                $this->command->info("Assigned inventory permissions to: {$roleName}");
            }
        }
    }
}