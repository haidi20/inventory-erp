<?php

namespace Tests\Feature\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * TDD untuk memastikan tabel sessions kompatibel dengan
 * Database Session Handler bawaan Laravel.
 */
class AlignSessionsUserIdColumnTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_sessions_table_has_user_id_and_index_for_laravel_handler(): void
    {
        $this->assertTrue(Schema::hasTable('sessions'));
        $this->assertTrue(
            Schema::hasColumn('sessions', 'user_id'),
            "Kolom 'user_id' wajib ada pada tabel 'sessions' untuk Session Database Handler Laravel."
        );
        $this->assertTrue(
            Schema::hasColumn('sessions', 'user_uuid'),
            "Kolom legacy 'user_uuid' tetap dipertahankan untuk kompatibilitas backward."
        );
        $this->assertTrue(
            $this->hasLeadingIndex('sessions', 'user_id'),
            "Kolom 'user_id' pada tabel 'sessions' wajib memiliki index."
        );
    }

    private function hasLeadingIndex(string $table, string $column): bool
    {
        foreach (Schema::getIndexes($table) as $index) {
            $columns = $index['columns'] ?? [];
            if (!is_array($columns) || $columns === []) {
                continue;
            }

            if ((string) $columns[0] === $column) {
                return true;
            }
        }

        return false;
    }
}
