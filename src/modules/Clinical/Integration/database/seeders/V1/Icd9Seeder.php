<?php

namespace Database\Seeders\V1;

use App\Modules\Clinical\Classification\Models\Icd9Code;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

class Icd9Seeder extends Seeder
{
    private const ICD9_FILE = '[PUBLIC] ICD-9CM e-klaim.xlsx';

    public function run(): void
    {
        $existingCount = Icd9Code::query()->count();

        if ($existingCount > 0) {
            $this->command->info("ICD-9 already seeded ({$existingCount} rows). Skipping.");

            return;
        }

        $dataPath = database_path('data/'.self::ICD9_FILE);

        if (! file_exists($dataPath)) {
            $this->command->warn("ICD-9 data file not found: {$dataPath}");

            return;
        }

        $rows = $this->readEklaimRows($dataPath);

        $now = now();
        $total = 0;

        Icd9Code::query()->getConnection()->transaction(function () use ($rows, $now, &$total) {
            foreach (array_chunk($rows, 500) as $chunk) {
                $insert = [];

                foreach ($chunk as $row) {
                    $code = trim((string) ($row['code'] ?? ''));
                    $name = trim((string) ($row['name_uuid'] ?? $row['name'] ?? ''));

                    if ($code === '' || $name === '') {
                        continue;
                    }

                    $insert[] = [
                        'uuid' => (string) Str::uuid(),
                        'code' => $code,
                        'name_uuid' => $name,
                        'name_en' => $row['name_en'] ?? null,
                        'category' => $row['category'] ?? null,
                        'deleted_at' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if ($insert !== []) {
                    Icd9Code::insertOrIgnore($insert);
                    $total += count($insert);
                }
            }
        });

        $this->command->info("ICD-9 seeded: {$total} rows inserted.");
    }

    private function readEklaimRows(string $path): array
    {
        $zip = new ZipArchive;

        if ($zip->open($path) !== true) {
            throw new RuntimeException("File master ICD-9 tidak bisa dibuka: {$path}");
        }

        try {
            $sharedStrings = $this->readSharedStrings($zip);
            $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');

            if ($sheetXml === false) {
                throw new RuntimeException("Sheet pertama tidak ditemukan: {$path}");
            }

            $sheet = simplexml_load_string($sheetXml);

            if (! $sheet instanceof SimpleXMLElement) {
                throw new RuntimeException("Sheet pertama tidak valid: {$path}");
            }

            $rows = [];
            $isHeader = true;

            foreach ($sheet->sheetData->row as $row) {
                if ($isHeader) {
                    $isHeader = false;

                    continue;
                }

                $values = $this->readRowValues($row, $sharedStrings);
                $code = strtoupper(trim((string) ($values[0] ?? '')));
                $display = trim((string) ($values[1] ?? ''));
                $version = trim((string) ($values[2] ?? ''));

                if ($code === '' || $display === '') {
                    continue;
                }

                $rows[$code] = [
                    'code' => substr($code, 0, 10),
                    'name_uuid' => $display,
                    'name_en' => $display,
                    'category' => $version !== '' ? $version : null,
                ];
            }

            return array_values($rows);
        } finally {
            $zip->close();
        }
    }

    private function readSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');

        if ($xml === false) {
            return [];
        }

        $sharedStrings = simplexml_load_string($xml);

        if (! $sharedStrings instanceof SimpleXMLElement) {
            return [];
        }

        $strings = [];

        foreach ($sharedStrings->si as $item) {
            $parts = [];

            if (isset($item->t)) {
                $parts[] = (string) $item->t;
            }

            foreach ($item->r ?? [] as $run) {
                $parts[] = (string) $run->t;
            }

            $strings[] = implode('', $parts);
        }

        return $strings;
    }

    private function readRowValues(SimpleXMLElement $row, array $sharedStrings): array
    {
        $values = [];

        foreach ($row->c as $cell) {
            $reference = (string) $cell['r'];
            $columnIndex = $this->columnIndex($reference);
            $type = (string) $cell['t'];
            $value = (string) ($cell->v ?? '');

            if ($type === 's') {
                $value = $sharedStrings[(int) $value] ?? '';
            } elseif ($type === 'inlineStr') {
                $value = (string) ($cell->is->t ?? '');
            }

            $values[$columnIndex] = $value;
        }

        ksort($values);

        return $values;
    }

    private function columnIndex(string $reference): int
    {
        preg_match('/^([A-Z]+)/i', $reference, $matches);
        $letters = strtoupper($matches[1] ?? 'A');
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return $index - 1;
    }
}
