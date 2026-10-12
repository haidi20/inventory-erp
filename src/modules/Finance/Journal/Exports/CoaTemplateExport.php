<?php

namespace App\Modules\Finance\Journal\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Template Excel untuk import COA: satu baris contoh + header kolom.
 */
class CoaTemplateExport implements FromCollection, WithHeadings
{
    use Exportable;

    public function collection(): Collection
    {
        return collect([
            [
                'code' => '1-0000',
                'name' => 'CONTOH AKUN - HAPUS BARIS INI',
                'type' => 'asset',
                'type_saldo' => 'debit',
                'is_header' => 0,
                'is_active' => 1,
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            'Kode',
            'Nama Akun',
            'Tipe',
            'Tipe Saldo',
            'Header',
            'Status',
        ];
    }
}