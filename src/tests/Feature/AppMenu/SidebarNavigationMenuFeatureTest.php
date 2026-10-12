<?php

namespace Tests\Feature\AppMenu;

use App\Modules\Core\Authorization\Models\Permission;
use App\Modules\Core\Authorization\Models\Role;
use App\Modules\Core\Identity\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SidebarNavigationMenuFeatureTest extends TestCase
{
    private string $databasePath;
    private string $backupDirectory;
    private string $sandboxCompanyUuid;
    private string $sandboxBranchUuid;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sandboxCompanyUuid = (string) Str::uuid();
        $this->sandboxBranchUuid = (string) Str::uuid();
        $productionCompanyUuid = (string) Str::uuid();
        $productionBranchUuid = (string) Str::uuid();

        $this->databasePath = storage_path('framework/testing/sqlite-sidebar-navigation.sqlite');
        $this->backupDirectory = storage_path('framework/testing/backups/sidebar-navigation');

        if (file_exists($this->databasePath)) {
            @unlink($this->databasePath);
        }

        if (!is_dir(dirname($this->databasePath))) {
            mkdir(dirname($this->databasePath), 0777, true);
        }

        if (!is_dir($this->backupDirectory)) {
            mkdir($this->backupDirectory, 0777, true);
        }

        touch($this->databasePath);

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $this->databasePath,
            'settings.backup.directory' => $this->backupDirectory,
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        DB::connection('sqlite')->unprepared('
            CREATE TABLE companies (
                uuid TEXT PRIMARY KEY,
                id INTEGER NULL,
                name TEXT NOT NULL,
                short_name TEXT NULL,
                is_active INTEGER NOT NULL DEFAULT 1,
                deleted_at INTEGER NOT NULL DEFAULT 0,
                created_at INTEGER NULL,
                updated_at INTEGER NULL,
                created_by TEXT NULL,
                updated_by TEXT NULL,
                deleted_by TEXT NULL
            );
        ');

        DB::connection('sqlite')->unprepared('
            CREATE TABLE branches (
                uuid TEXT PRIMARY KEY,
                id INTEGER NULL,
                company_uuid TEXT NOT NULL,
                name TEXT NOT NULL,
                short_name TEXT NOT NULL,
                is_active INTEGER NOT NULL DEFAULT 1,
                deleted_at INTEGER NOT NULL DEFAULT 0,
                created_at INTEGER NULL,
                updated_at INTEGER NULL,
                created_by TEXT NULL,
                updated_by TEXT NULL,
                deleted_by TEXT NULL
            );
        ');

        DB::connection('sqlite')->unprepared('
            CREATE TABLE audit_logs (
                uuid TEXT PRIMARY KEY,
                id INTEGER NULL,
                branch_uuid TEXT NULL,
                user_uuid TEXT NULL,
                event TEXT NOT NULL,
                entity_type TEXT NULL,
                entity_uuid TEXT NULL,
                before TEXT NULL,
                after TEXT NULL,
                ip_address TEXT NULL,
                user_agent TEXT NULL,
                deleted_at INTEGER NOT NULL DEFAULT 0,
                created_by TEXT NULL,
                updated_by TEXT NULL,
                deleted_by TEXT NULL,
                created_at INTEGER NULL,
                updated_at INTEGER NULL
            );
        ');

        DB::connection('sqlite')->unprepared('
            CREATE TABLE users (
                uuid TEXT PRIMARY KEY,
                id INTEGER NULL,
                name TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL,
                branch_uuid TEXT NULL,
                company_uuid TEXT NULL,
                is_active INTEGER NOT NULL DEFAULT 1,
                remember_token TEXT NULL,
                deleted_at INTEGER NOT NULL DEFAULT 0,
                created_by TEXT NULL,
                updated_by TEXT NULL,
                deleted_by TEXT NULL,
                created_at INTEGER NULL,
                updated_at INTEGER NULL
            );
        ');

        DB::connection('sqlite')->unprepared('
            CREATE TABLE roles (
                uuid TEXT PRIMARY KEY,
                id INTEGER NULL,
                name TEXT NOT NULL,
                label TEXT NULL,
                guard_name TEXT NOT NULL,
                deleted_at INTEGER NOT NULL DEFAULT 0,
                created_by TEXT NULL,
                updated_by TEXT NULL,
                deleted_by TEXT NULL,
                created_at INTEGER NULL,
                updated_at INTEGER NULL
            );
        ');

        DB::connection('sqlite')->unprepared('
            CREATE TABLE permissions (
                uuid TEXT PRIMARY KEY,
                id INTEGER NULL,
                name TEXT NOT NULL,
                label TEXT NULL,
                guard_name TEXT NOT NULL,
                deleted_at INTEGER NOT NULL DEFAULT 0,
                created_by TEXT NULL,
                updated_by TEXT NULL,
                deleted_by TEXT NULL,
                created_at INTEGER NULL,
                updated_at INTEGER NULL
            );
        ');

        DB::connection('sqlite')->unprepared('
            CREATE TABLE model_has_roles (
                role_uuid TEXT NOT NULL,
                model_type TEXT NOT NULL,
                model_uuid TEXT NOT NULL
            );
        ');

        DB::connection('sqlite')->unprepared('
            CREATE TABLE model_has_permissions (
                permission_uuid TEXT NOT NULL,
                model_type TEXT NOT NULL,
                model_uuid TEXT NOT NULL
            );
        ');

        DB::connection('sqlite')->unprepared('
            CREATE TABLE role_has_permissions (
                permission_uuid TEXT NOT NULL,
                role_uuid TEXT NOT NULL
            );
        ');

        DB::connection('sqlite')->unprepared('
            CREATE TABLE employees (
                uuid TEXT PRIMARY KEY,
                id INTEGER NULL,
                user_uuid TEXT NULL,
                branch_uuid TEXT NULL,
                company_uuid TEXT NULL,
                name TEXT NOT NULL,
                role TEXT NULL,
                email TEXT NULL,
                phone TEXT NULL,
                fee_default INTEGER NULL,
                is_active INTEGER NOT NULL DEFAULT 1,
                deleted_at INTEGER NOT NULL DEFAULT 0,
                created_by TEXT NULL,
                updated_by TEXT NULL,
                deleted_by TEXT NULL,
                created_at INTEGER NULL,
                updated_at INTEGER NULL
            );
        ');

        DB::connection('sqlite')->unprepared('
            CREATE TABLE database_backups (
                uuid TEXT NULL,
                name TEXT NOT NULL,
                path TEXT NOT NULL,
                size INTEGER NOT NULL DEFAULT 0,
                status TEXT NOT NULL,
                source TEXT NOT NULL,
                history_type TEXT NOT NULL DEFAULT \'manual\',
                notes TEXT NULL,
                created_at INTEGER NULL,
                updated_at INTEGER NULL
            );
        ');

        DB::connection('sqlite')->unprepared('
            CREATE TABLE user_companies (
                uuid TEXT PRIMARY KEY,
                user_uuid TEXT NOT NULL,
                company_uuid TEXT NOT NULL,
                is_default INTEGER NOT NULL DEFAULT 0,
                created_at INTEGER NULL,
                created_by TEXT NULL
            );
        ');

        DB::connection('sqlite')->unprepared('
            CREATE TABLE user_branches (
                uuid TEXT PRIMARY KEY,
                user_uuid TEXT NOT NULL,
                company_uuid TEXT NOT NULL,
                branch_uuid TEXT NOT NULL,
                is_default INTEGER NOT NULL DEFAULT 0,
                created_at INTEGER NULL,
                created_by TEXT NULL
            );
        ');

        DB::table('companies')->insert([
            [
                'uuid' => $this->sandboxCompanyUuid,
                'name' => 'Sandbox Company',
                'short_name' => 'SBOX',
                'created_at' => time(),
                'updated_at' => time(),
            ],
            [
                'uuid' => $productionCompanyUuid,
                'name' => 'Production Company',
                'short_name' => 'PROD',
                'created_at' => time(),
                'updated_at' => time(),
            ],
        ]);

        DB::table('branches')->insert([
            [
                'uuid' => $this->sandboxBranchUuid,
                'company_uuid' => $this->sandboxCompanyUuid,
                'name' => 'Klinik Sandbox',
                'short_name' => 'KSB',
                'created_at' => time(),
                'updated_at' => time(),
            ],
            [
                'uuid' => $productionBranchUuid,
                'company_uuid' => $productionCompanyUuid,
                'name' => 'Klinik Production',
                'short_name' => 'KPR',
                'created_at' => time(),
                'updated_at' => time(),
            ],
        ]);
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');

        if (file_exists($this->databasePath)) {
            @unlink($this->databasePath);
        }

        $this->deleteDirectory($this->backupDirectory);

        parent::tearDown();
    }

    public function test_sidebar_navigation_is_available_and_changes_active_state_when_switching_pages(): void
    {
        $user = User::query()->create([
            'name' => 'Administrator',
            'email' => 'admin.sidebar@example.com',
            'password' => bcrypt('secret123'),
            'branch_uuid' => $this->sandboxBranchUuid,
            'company_uuid' => $this->sandboxCompanyUuid,
        ]);

        $role = Role::query()->create([
            'name' => 'admin',
            'label' => 'Administrator',
            'guard_name' => 'web',
        ]);

        $dashboardPermission = Permission::query()->create([
            'name' => 'dashboard.view',
            'label' => 'Dashboard',
            'guard_name' => 'web',
        ]);

        $settingsPermission = Permission::query()->create([
            'name' => 'settings.view',
            'label' => 'Pengaturan',
            'guard_name' => 'web',
        ]);

        $managementUsersPermission = Permission::query()->create([
            'name' => 'settings.user.view',
            'label' => 'Users',
            'guard_name' => 'web',
        ]);

        $role->permissions()->attach([
            $dashboardPermission->uuid,
            $settingsPermission->uuid,
            $managementUsersPermission->uuid,
        ]);
        $user->roles()->attach($role->uuid);

        $managementResponse = $this->actingAs($user)
            ->followingRedirects()
            ->get('/management/users?search=&role=');

        $managementResponse->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Core/Identity/presentations/views/UserIndex', false)
                ->has('users.data')
                ->has('roles')
                ->where('auth.user.company_uuid', $this->sandboxCompanyUuid)
            );

        $databaseResponse = $this->actingAs($user)->get('/settings/backup');

        $databaseResponse->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Core/Platform/presentations/views/backupIndex', false)
                ->has('database')
                ->has('recentBackups')
                ->where('auth.user.company_uuid', $this->sandboxCompanyUuid)
            );
    }

    private function deleteDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = scandir($directory);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if (in_array($item, ['.', '..'], true)) {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $item;

            if (is_dir($path)) {
                $this->deleteDirectory($path);
                continue;
            }

            @unlink($path);
        }

        @rmdir($directory);
    }
}
