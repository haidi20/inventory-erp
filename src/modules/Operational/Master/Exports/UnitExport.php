<?php

namespace App\Modules\Operational\Master\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UnitExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(private readonly Collection $items)
    {
    }

    public function collection(): Collection
    {
        return $this->items->map(fn ($item) => [
            'kode' => $item->code,
            'nama' => $item->name,
            'simbol' => $item->symbol,
            'keterangan' => $item->description,
            'aktif' => $item->is_active ? 'Aktif' : 'Nonaktif',
        ]);
    }

    public function headings(): array
    {
        return ['Kode Satuan', 'Nama Satuan', 'Simbol', 'Keterangan', 'Aktif'];
    }
}
