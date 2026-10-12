<?php

namespace App\Modules\Finance\Journal\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Export daftar aset tetap ke Excel.
 * Data berupa array AccountEntity hasil AccountingRepository::getAllAssets.
 */
class AssetExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(private Collection|array $data)
    {
        $this->data = is_array($data) ? collect($data) : $data;
    }

    public function collection(): Collection
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Kode',
            'Nama Aset',
            'Nilai Aset',
            'Umur (Tahun)',
            'Penyusutan Per Tahun',
        ];
    }

    /**
     * @param object $asset AccountEntity/asset row
     */
    public function map($asset): array
    {
        return [
            $asset->code ?? '',
            $asset->name ?? '',
            (float) ($asset->asset_value ?? 0),
            (int) ($asset->life_year ?? 0),
            (float) ($asset->yearly_depreciation ?? 0),
        ];
    }
}