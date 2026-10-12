<?php

namespace App\Modules\Operational\Master\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UnitImportTemplateExport implements WithMultipleSheets
{
    public function __construct(private readonly array $template)
    {
    }

    public function sheets(): array
    {
        return [
            new UnitImportTemplateInputSheet($this->template),
            new UnitImportTemplateGuideSheet($this->template),
        ];
    }
}

class UnitImportTemplateInputSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    public function __construct(private readonly array $template)
    {
    }

    public function collection()
    {
        return collect([$this->template['sample_row'] ?? []]);
    }

    public function title(): string
    {
        return (string) ($this->template['sheet'] ?? 'Data Satuan');
    }

    public function headings(): array
    {
        return $this->template['headers'] ?? [];
    }
}

class UnitImportTemplateGuideSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithStyles
{
    public function __construct(private readonly array $template)
    {
    }

    public function collection()
    {
        return collect($this->template['guide'] ?? [])->map(fn (string $guide) => [$guide]);
    }

    public function title(): string
    {
        return 'Panduan';
    }

    public function headings(): array
    {
        return ['Panduan Import'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
