<?php

namespace Database\Seeders\V1;

use App\Modules\Core\Authorization\Models\Permission;
use App\Modules\Core\Authorization\Models\Role;
use App\Modules\Core\Identity\Models\User;
use App\Modules\Core\Identity\Models\UserBranch;
use App\Modules\Core\Identity\Models\UserCompany;
use App\Modules\Core\Organization\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class SuperadminSeeder extends Seeder
{
    public function run(): void
    {
        // Reset permission cache
        try {
            app()[PermissionRegistrar::class]->forgetCachedPermissions();
        } catch (\Exception $e) {
            // ignore
        }

        // Buat role superadmin dengan semua permission
        $allPerms = Permission::pluck('name')->toArray();

        $role = Role::updateOrCreate(
            ['name' => 'superadmin', 'guard_name' => 'web'],
            ['label' => 'Super Administrator']
        );
        $role->syncPermissions($allPerms);

        // Ambil branch sandbox terlebih dahulu agar context superadmin konsisten dengan runtime.
        $branch = Branch::where('short_name', 'KSB')
            ->where('is_active', true)
            ->first()
            ?? Branch::where('is_active', true)->first();

        // Buat user superadmin
        // Catatan: kolom legacy branch_uuid/company_uuid sudah di-drop dari tabel users.
        // Konteks multi-org cukup lewat pivot user_companies + user_branches di bawah.
        $user = User::updateOrCreate(
            ['email' => 'superadmin@klinik.local'],
            [
                'name' => 'Super Administrator',
                'username' => 'superadmin',
                'password' => Hash::make('superadmin'),
                'is_active' => true,
            ]
        );

        // Pastikan username ter-set jika user sudah ada sebelumnya
        $user->username = 'superadmin';
        $user->save();

        if ($branch) {
            UserCompany::updateOrCreate(
                ['user_uuid' => $user->uuid, 'company_uuid' => $branch->company_uuid],
                ['is_default' => true],
            );

            UserBranch::updateOrCreate(
                [
                    'user_uuid' => $user->uuid,
                    'company_uuid' => $branch->company_uuid,
                    'branch_uuid' => $branch->uuid,
                ],
                ['is_default' => true],
            );
        }

        $user->syncRoles(['superadmin']);

        $this->command->info('Superadmin user created: username=superadmin password=superadmin');
    }
}
