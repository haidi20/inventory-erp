<?php

namespace App\Modules\Finance\Journal\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Template Excel untuk import aset tetap: hanya header kolom.
 */
class AssetTemplateExport implements FromCollection, WithHeadings
{
    use Exportable;

    public function collection()
    {
        return collect([]);
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
}