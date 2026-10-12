<?php

namespace App\Modules\Operational\Master\Services;

use App\Modules\Operational\Master\Entities\ProductEntity;
use App\Modules\Operational\Master\Repositories\ProductRepositoryInterface;
use App\Modules\Operational\Master\Repositories\UnitRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductImportService
{
    private const HEADERS = [
        'code', 'name', 'barcode', 'category', 'unit', 'price_buy',
        'price_sell', 'min_stock', 'max_stock', 'max_order', 'is_active',
    ];

    public function __construct(
        private readonly ProductService $service,
        private readonly ProductRepositoryInterface $products,
        private readonly UnitRepositoryInterface $units,
    ) {}

    public function template(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $output = fopen('php://output', 'wb');
            fputcsv($output, self::HEADERS);
            fclose($output);
        }, 'products-import-template.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(UploadedFile $file, string $branchUuid, string $companyUuid): array
    {
        $path = $this->normalizedCsvPath($file->getContent());
        $handle = fopen($path, 'rb');

        try {
            $headers = $handle ? fgetcsv($handle) : false;
            if (! $handle || ! $headers) {
                return $this->result(errors: ['File CSV kosong atau tidak valid.']);
            }

            $headers = array_map(static fn (mixed $header): string => strtolower(trim((string) $header)), $headers);
            if (array_diff(['code', 'name'], $headers)) {
                return $this->result(errors: ['Header CSV wajib memuat code dan name.']);
            }

            $units = $this->units->query()
                ->where('company_uuid', $companyUuid)
                ->pluck('uuid', 'name')
                ->mapWithKeys(fn (string $uuid, string $name): array => [strtolower(trim($name)) => $uuid])
                ->all();

            $rowNumber = 1;
            $errors = [];
            $seenCodes = [];
            $seenBarcodes = [];
            $imported = 0;
            $updated = 0;

            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;
                if (count(array_filter($row, static fn (mixed $value): bool => trim((string) $value) !== '')) === 0) {
                    continue;
                }

                $data = array_combine($headers, array_pad($row, count($headers), null));
                $code = trim((string) ($data['code'] ?? ''));
                $name = trim((string) ($data['name'] ?? ''));
                $barcode = trim((string) ($data['barcode'] ?? '')) ?: null;

                if ($code === '' || $name === '') {
                    $errors[] = "Baris {$rowNumber}: code dan name wajib diisi.";

                    continue;
                }

                if (isset($seenCodes[$code])) {
                    $errors[] = "Baris {$rowNumber}: Kode produk sudah digunakan.";

                    continue;
                }
                $seenCodes[$code] = true;

                if ($barcode !== null) {
                    if (isset($seenBarcodes[$barcode])) {
                        $errors[] = "Baris {$rowNumber}: Barcode sudah digunakan.";

                        continue;
                    }
                    $seenBarcodes[$barcode] = true;
                }

                $unitLabel = trim((string) ($data['unit'] ?? ''));
                $unitName = strtolower($unitLabel);
                if ($unitName !== '' && ! isset($units[$unitName])) {
                    $errors[] = "Baris {$rowNumber}: Unit tidak ditemukan.";

                    continue;
                }

                $existing = $this->products->query()
                    ->where('branch_uuid', $branchUuid)
                    ->where('company_uuid', $companyUuid)
                    ->where('code', $code)
                    ->first();
                $duplicateBarcode = $barcode !== null && $this->products->query()
                    ->where('branch_uuid', $branchUuid)
                    ->where('company_uuid', $companyUuid)
                    ->where('barcode', $barcode)
                    ->where('uuid', '!=', $existing?->uuid ?? '')
                    ->exists();

                if ($duplicateBarcode) {
                    $errors[] = "Baris {$rowNumber}: Barcode sudah digunakan.";

                    continue;
                }

                $payload = [
                    'uuid' => $existing?->uuid,
                    'branch_uuid' => $branchUuid,
                    'company_uuid' => $companyUuid,
                    'code' => $code,
                    'name' => $name,
                    'barcode' => $barcode,
                    'category' => trim((string) ($data['category'] ?? '')) ?: null,
                    'category_name' => trim((string) ($data['category'] ?? '')) ?: null,
                    'unit' => $unitName === '' ? null : $units[$unitName],
                    'unit_name' => $unitName === '' ? null : $unitLabel,
                    'price_buy' => is_numeric($data['price_buy'] ?? null) ? (float) $data['price_buy'] : 0,
                    'price_sell' => is_numeric($data['price_sell'] ?? null) ? (float) $data['price_sell'] : 0,
                    'min_stock' => is_numeric($data['min_stock'] ?? null) ? (float) $data['min_stock'] : 0,
                    'max_stock' => is_numeric($data['max_stock'] ?? null) ? (float) $data['max_stock'] : 0,
                    'max_order' => is_numeric($data['max_order'] ?? null) ? (float) $data['max_order'] : 0,
                    'is_active' => ! in_array(strtolower(trim((string) ($data['is_active'] ?? '1'))), ['0', 'false', 'no'], true),
                ];

                try {
                    $this->service->save(ProductEntity::fromArray($payload));
                    $existing ? $updated++ : $imported++;
                } catch (\Throwable $exception) {
                    $errors[] = "Baris {$rowNumber}: {$exception->getMessage()}";
                }
            }

            return $this->result($imported, $updated, $errors);
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    private function normalizedCsvPath(string $contents): string
    {
        if (str_starts_with($contents, "\xEF\xBB\xBF")) {
            $contents = substr($contents, 3);
        } elseif (str_starts_with($contents, "\xFF\xFE")) {
            $contents = mb_convert_encoding(substr($contents, 2), 'UTF-8', 'UTF-16LE');
        } elseif (str_starts_with($contents, "\xFE\xFF")) {
            $contents = mb_convert_encoding(substr($contents, 2), 'UTF-8', 'UTF-16BE');
        } elseif (! mb_check_encoding($contents, 'UTF-8')) {
            $contents = mb_convert_encoding($contents, 'UTF-8', 'Windows-1252');
        }

        $path = tempnam(sys_get_temp_dir(), 'product-import-');
        file_put_contents($path, $contents);

        return $path;
    }

    private function result(int $imported = 0, int $updated = 0, array $errors = []): array
    {
        return [
            'message' => $errors === [] ? 'Import produk selesai.' : 'Import produk selesai dengan error.',
            'imported' => $imported,
            'updated' => $updated,
            'errors' => $errors,
        ];
    }
}
