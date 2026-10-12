<?php

namespace Tests\Traits;

use Illuminate\Support\Facades\DB;

/**
 * Trait ManualDatabaseSetup
 * 
 * Trait kustom untuk menggantikan RefreshDatabase/DatabaseTransactions pada Windows,
 * karena stored procedure migrations menghasilkan output sangat besar yang menyebabkan
 * Symfony Process OOM saat membaca pipe output.
 * 
 * Prasyarat: Jalankan `php artisan migrate:fresh --env=testing` secara manual sebelum test.
 * 
 * Cara kerja: Sebelum setiap test, tabel-tabel yang digunakan akan di-truncate
 * supaya data dari test sebelumnya tidak mengganggu.
 */
trait ManualDatabaseSetup
{
    /**
     * Tabel-tabel yang akan dibersihkan sebelum setiap test.
     * Override di test class jika perlu tabel tambahan.
     */
    protected function tablesToCleanup(): array
    {
        return [
            'audit_logs',
            'model_has_roles',
            'model_has_permissions',
            'role_has_permissions',
            'roles',
            'permissions',
            'users',
            'branches',
            'companies',
            'complaints',
            'lab_orders',
            'radiology_orders',
            'patients',
            'employees',
            'sales',
            'products',
            'sale_items',
            'sessions',
            'cache',
        ];
    }

    /**
     * Boot trait: bersihkan tabel sebelum tiap test.
     */
    protected function setUpManualDatabaseSetup(): void
    {
        $isSqlite = DB::getDriverName() === 'sqlite';
        if ($isSqlite) {
            DB::statement('PRAGMA foreign_keys = OFF');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        foreach ($this->tablesToCleanup() as $table) {
            if (\Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        if ($isSqlite) {
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
}
