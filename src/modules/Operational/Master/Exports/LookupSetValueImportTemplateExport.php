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

class LookupSetValueImportTemplateExport implements WithMultipleSheets
{
    public function __construct(private readonly LookupDocumentEntity $document)
    {
    }

    public function sheets(): array
    {
        return [
            new LookupSetValueImportTemplateInputSheet($this->document),
            new LookupSetValueImportTemplateGuideSheet($this->document),
        ];
    }
}

class LookupSetValueImportTemplateInputSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
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

class LookupSetValueImportTemplateGuideSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithStyles
{
    public function __construct(private readonly LookupDocumentEntity $document)
    {
    }

    public function collection()
    {
        return collect(array_slice($this->document->guide, 1));
    }

    public function title(): string
    {
        return 'Panduan';
    }

    public function headings(): array
    {
        return $this->document->guide[0] ?? ['Kolom', 'Keterangan', 'Contoh'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
