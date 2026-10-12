<?php

namespace Tests\Unit\Exports;

use App\Modules\Finance\Journal\Exports\AssetExport;
use App\Modules\Finance\Journal\Exports\AssetTemplateExport;
use App\Modules\Finance\Journal\Exports\CoaExport;
use App\Modules\Finance\Journal\Exports\CoaTemplateExport;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FinanceJournalExportTest extends TestCase
{
    #[Test]
    public function coa_export_accepts_array_and_converts_to_collection()
    {
        $export = new CoaExport([
            ['code' => '1-0000', 'name' => 'Kas', 'type' => 'asset', 'type_saldo' => 'debit', 'is_header' => 0, 'is_active' => 1],
        ]);

        $this->assertInstanceOf(Collection::class, $export->collection());
        $this->assertCount(1, $export->collection());
    }

    #[Test]
    public function coa_export_accepts_collection_directly()
    {
        $export = new CoaExport(collect([['code' => '2-0000']]));

        $this->assertInstanceOf(Collection::class, $export->collection());
        $this->assertCount(1, $export->collection());
    }

    #[Test]
    public function coa_export_maps_row_with_indonesian_labels()
    {
        $export = new CoaExport([]);
        $mapped = $export->map((object) [
            'code'       => '1-1000',
            'name'       => 'Kas di Tangan',
            'type'       => 'asset',
            'type_saldo' => 'debit',
            'is_header'  => 1,
            'is_active'  => 1,
        ]);

        $this->assertSame(['1-1000', 'Kas di Tangan', 'asset', 'debit', 'Ya', 'Aktif'], $mapped);
    }

    #[Test]
    public function coa_export_maps_null_fields_and_inactive_status()
    {
        $export = new CoaExport([]);
        $mapped = $export->map((object) []);

        $this->assertSame(['', '', '', '', 'Tidak', 'Nonaktif'], $mapped);
    }

    #[Test]
    public function coa_export_has_six_columns()
    {
        $export = new CoaExport([]);

        $this->assertSame(['Kode', 'Nama Akun', 'Tipe', 'Tipe Saldo', 'Header', 'Status'], $export->headings());
    }

    #[Test]
    public function coa_template_contains_example_row_and_headings()
    {
        $export = new CoaTemplateExport();

        $this->assertSame(['Kode', 'Nama Akun', 'Tipe', 'Tipe Saldo', 'Header', 'Status'], $export->headings());
        $this->assertCount(1, $export->collection());
        $this->assertSame('CONTOH AKUN - HAPUS BARIS INI', $export->collection()->first()['name']);
    }

    #[Test]
    public function asset_export_accepts_array_and_converts_to_collection()
    {
        $export = new AssetExport([
            ['code' => 'A-001', 'name' => 'Gedung', 'asset_value' => 1000000, 'life_year' => 20, 'yearly_depreciation' => 50000],
        ]);

        $this->assertInstanceOf(Collection::class, $export->collection());
        $this->assertCount(1, $export->collection());
    }

    #[Test]
    public function asset_export_maps_row_with_numeric_casts()
    {
        $export = new AssetExport([]);
        $mapped = $export->map((object) [
            'code'                => 'A-002',
            'name'                => 'Kendaraan',
            'asset_value'         => '250000000',
            'life_year'           => '8',
            'yearly_depreciation' => '31250000',
        ]);

        $this->assertSame(['A-002', 'Kendaraan', 250000000.0, 8, 31250000.0], $mapped);
    }

    #[Test]
    public function asset_export_maps_missing_fields_to_zero()
    {
        $export = new AssetExport([]);
        $mapped = $export->map((object) ['code' => 'A-003', 'name' => 'Tanah']);

        $this->assertSame(['A-003', 'Tanah', 0.0, 0, 0.0], $mapped);
    }

    #[Test]
    public function asset_export_has_five_columns()
    {
        $export = new AssetExport([]);

        $this->assertSame(['Kode', 'Nama Aset', 'Nilai Aset', 'Umur (Tahun)', 'Penyusutan Per Tahun'], $export->headings());
    }

    #[Test]
    public function asset_template_has_only_headings_and_empty_rows()
    {
        $export = new AssetTemplateExport();

        $this->assertSame(['Kode', 'Nama Aset', 'Nilai Aset', 'Umur (Tahun)', 'Penyusutan Per Tahun'], $export->headings());
        $this->assertCount(0, $export->collection());
    }
}
