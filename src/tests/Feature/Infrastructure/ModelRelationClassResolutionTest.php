<?php

namespace Tests\Feature\Infrastructure;

use Tests\TestCase;

/**
 * TDD test resolusi kelas relasi model (2026-10-09).
 *
 * Seluruh model di modules (semua direktori Models) sebelumnya punya pola bug sistemik:
 * relasi Eloquent (belongsTo/hasMany/hasOne/belongsToMany) mereferensikan
 * kelas lintas modul (Branch, Company, User, MasterProduct, dst.) tanpa
 * statement use -- PHP me-resolve ke namespace model itu sendiri, dan
 * fatal "Class not found" baru muncul saat relasi dipanggil runtime.
 *
 * Test ini memanggil SETIAP method relasi (via reflection, tanpa query:
 * relation resolved tanpa eksekusi) dan memastikan tidak ada yang melempar
 * Error karena kelas tidak ditemukan.
 */
class ModelRelationClassResolutionTest extends TestCase
{
    /** Direktori berisi model yang discan, relatif ke root modules/. */
    private const MODEL_DIRS = [
        'Clinical/Ancillary/Models',
        'Clinical/Classification/Models',
        'Clinical/Integration/Models',
        'Clinical/MedicalRecord/Models',
        'Clinical/Pharmacy/Models',
        'Clinical/Registration/Models',
        'Core/Authorization/Models',
        'Core/Identity/Models',
        'Core/Organization/Models',
        'Core/Platform/Models',
        'Finance/Journal/Models',
        'Finance/Ledger/Models',
        'Operational/Inventory/Models',
        'Operational/Manufacturing/Models',
        'Operational/Master/Models',
        'Operational/Procurement/Models',
        'Operational/Sales/Models',
        'Analytics/Dashboard/Models',
        'Analytics/Reports/Models',
    ];

    /** Method magic Eloquent yang bukan relasi, dikecualikan dari pemanggilan. */
    private const SKIP_METHODS = [
        'getCreatedAtColumn', 'getUpdatedAtColumn', 'getDeletedAtColumn',
    ];

    /** Data provider: satu entri per method relasi di seluruh model. */
    public static function relationMethodProvider(): array
    {
        // Static provider tidak bisa akses container/base_path -> scan via file system biasa.
        $base = __DIR__ . '/../../../modules';
        $relationNames = [
            'belongsTo', 'hasMany', 'hasOne', 'belongsToMany', 'hasManyThrough',
            'morphMany', 'morphOne', 'morphTo', 'morphToMany', 'morphedByMany',
        ];
        $relRe = '/(' . implode('|', $relationNames) . ')\s*\(/i';
        $cases = [];

        foreach (self::MODEL_DIRS as $dir) {
            $path = $base . '/' . $dir;
            if (!is_dir($path)) {
                continue;
            }
            foreach (glob($path . '/*.php') as $file) {
                $src = file_get_contents($file);
                if (!preg_match('/^\s*(?:abstract\s+|final\s+)*class\s+(\w+)/m', $src, $m)) {
                    continue;
                }
                $class = 'App\\Modules\\' . str_replace('/', '\\', $dir) . '\\' . $m[1];
                // Kumpulkan nama method yang body-nya memanggil method relasi.
                if (!preg_match_all('/public\s+function\s+(\w+)\s*\([^)]*\)\s*\{/', $src, $mm, PREG_OFFSET_CAPTURE)) {
                    continue;
                }
                $count = count($mm[0]);
                for ($i = 0; $i < $count; $i++) {
                    $start = $mm[0][$i][1];
                    $end = ($i + 1 < $count) ? $mm[0][$i + 1][1] : strlen($src);
                    $body = substr($src, $start, $end - $start);
                    if (preg_match($relRe, $body)) {
                        $cases[$class . '::' . $mm[1][$i][0]] = [$class, $mm[1][$i][0]];
                    }
                }
            }
        }

        return $cases;
    }

    /**
     * @dataProvider relationMethodProvider
     */
    public function test_relation_method_resolves_class(string $modelClass, string $method): void
    {
        $this->assertTrue(class_exists($modelClass), "Kelas {$modelClass} harus ada.");
        // Buat instance tanpa query: relation resolution terjadi tanpa eksekusi SQL.
        $model = new $modelClass;

        $related = $model->{$method}();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\Relation::class,
            $related,
            "{$modelClass}::{$method}() harus mengembalikan Relation."
        );

        // Kunci resolusi kelas terjadi di sini: getRelated() memanggil
        // (new $relatedClass) pada kelas hasil resolve ::class.
        $relatedModel = $related->getRelated();
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Model::class,
            $relatedModel,
            "{$modelClass}::{$method}() harus me-resolve kelas terkait yang valid."
        );
    }
}
