<?php

namespace App\Modules\Operational\Master\Exports;

use App\Modules\Operational\Master\Entities\LookupDocumentEntity;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LookupSetImportTemplateExport implements WithMultipleSheets
{
    public function __construct(private readonly LookupDocumentEntity $document)
    {
    }

    public function sheets(): array
    {
        return [
            new LookupSetImportTemplateInputSheet($this->document),
            new LookupSetImportTemplateGuideSheet($this->document),
        ];
    }
}

class LookupSetImportTemplateInputSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    public function __construct(private readonly LookupDocumentEntity $document)
    {
    }

    public function collection()
    {
        return collect($this->document->sample_row !== [] ? [$this->document->sample_row] : []);
    }

    public function title(): string
    {
        return $this->document->sheet;
    }

    public function headings(): array
    {
        return $this->document->headers;
    }
}

class LookupSetImportTemplateGuideSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithStyles
{
    public function __construct(private readonly LookupDocumentEntity $document)
    {
    }

    public function collection()
    {
        return collect($this->document->guide);
    }

    public function title(): string
    {
        return 'Panduan';
    }

    public function headings(): array
    {
        return ['Kolom', 'Status', 'Keterangan'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
