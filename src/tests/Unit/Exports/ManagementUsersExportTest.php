<?php

namespace Tests\Unit\Exports;

use App\Modules\Core\Identity\Exports\ManagementUsersExport;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ManagementUsersExportTest extends TestCase
{
    #[Test]
    public function it_builds_two_sheets_with_data_and_penjelasan(): void
    {
        $user = (object) [
            'name' => 'Export User',
            'email' => 'export-user@example.com',
            'roles' => collect([(object) ['label' => 'Manager']]),
            'employee' => (object) ['name' => 'Pegawai A'],
            'created_at' => Carbon::create(2026, 4, 18),
        ];

        $export = new ManagementUsersExport(collect([$user]));
        $sheets = $export->sheets();

        $this->assertCount(2, $sheets);
        $this->assertSame('Data User', $sheets[0]->title());
        $this->assertSame('Penjelasan', $sheets[1]->title());
        $this->assertNotEmpty($sheets[1]->collection());
    }
}

