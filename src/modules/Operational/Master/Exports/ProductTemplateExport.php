<?php

namespace App\Modules\Operational\Master\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new ProductInputSheet(),
            new ProductGuideSheet(),
        ];
    }
}

class ProductInputSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    public function collection()
    {
        return collect([]); // Sheet input kosong
    }

    public function title(): string
    {
        return 'Input Data';
    }

    public function headings(): array
    {
        return ['code', 'name', 'category', 'unit', 'price_buy', 'price_sell', 'min_stock'];
    }
}

class ProductGuideSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        return collect([
            ['code', 'Wajib', 'String', 'Kode unik produk (misal: OBT-001)'],
            ['name', 'Wajib', 'String', 'Nama lengkap produk'],
            ['category', 'Opsional', 'String', 'Kategori produk (misal: Obat Bebas)'],
            ['unit', 'Opsional', 'String', 'Satuan (misal: Botol, Pcs)'],
            ['price_buy', 'Opsional', 'Numeric', 'Harga beli (angka tanpa titik/koma)'],
            ['price_sell', 'Opsional', 'Numeric', 'Harga jual (angka tanpa titik/koma)'],
            ['min_stock', 'Opsional', 'Numeric', 'Stok minimum peringatan'],
            ['', '', '', ''],
            ['CONTOH DATA:', '', '', ''],
            ['code', 'name', 'category', 'unit', 'price_buy', 'price_sell', 'min_stock'],
            ['OBT-010', 'Paracetamol 500mg', 'Obat Demam', 'Strip', '5000', '7500', '20'],
            ['ALKES-02', 'Masker Bedah', 'Alat Kesehatan', 'Box', '25000', '35000', '10'],
        ]);
    }

    public function title(): string
    {
        return 'Panduan & Contoh';
    }

    public function headings(): array
    {
        return ['Kolom', 'Status', 'Tipe Data', 'Keterangan / Contoh'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            9 => ['font' => ['bold' => true, 'color' => ['rgb' => '1D4ED8']]],
            10 => ['font' => ['bold' => true]],
        ];
    }
}
