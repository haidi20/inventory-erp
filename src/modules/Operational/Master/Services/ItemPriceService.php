<?php

namespace App\Modules\Operational\Master\Services;

use App\Modules\Operational\Master\Entities\ProductPriceEntity;
use App\Modules\Operational\Master\Repositories\ItemPriceRepositoryInterface;

/**
 * ItemPriceService
 *
 * Port dari BE: item_price_service.go. Mengelola export/template/import massal
 * harga barang — mendukung kolom dinamis berdasar daftar PriceLevel aktif.
 * Kontrak array rows: [0] = header, kolom:
 *   A: KODE ITEM (kunci update)
 *   B: NAMA ITEM (referensi visual)
 *   C: HARGA BELI (BASE)
 *   D: HARGA JUAL (BASE)
 *   E+: LEVEL: <nama level> — satu kolom per PriceLevel aktif
 */
class ItemPriceService
{
    public function __construct(protected ItemPriceRepositoryInterface $itemPriceRepository) {}

    /**
     * Dataset export harga — headers + rows siap dirender ke sheet "Data Harga".
     */
    public function exportPrices(array $filter): array
    {
        $items = method_exists($this->itemPriceRepository, 'listForExport')
            ? $this->itemPriceRepository->listForExport($filter)
            : [];
        $levels = method_exists($this->itemPriceRepository, 'getActivePriceLevels')
            ? $this->itemPriceRepository->getActivePriceLevels()
            : [];

        $headers = ['KODE ITEM', 'NAMA ITEM', 'HARGA BELI (BASE)', 'HARGA JUAL (BASE)'];
        foreach ($levels as $lvl) {
            $headers[] = 'LEVEL: '.strtoupper((string) ($lvl['name'] ?? $lvl->name ?? ''));
        }

        $rows = [];
        foreach ($items as $item) {
            $priceMap = [];
            foreach ((array) ($item['prices'] ?? $item->prices ?? []) as $p) {
                $levelUuid = is_array($p) ? ($p['price_level_uuid'] ?? '') : ($p->price_level_uuid ?? '');
                $price = is_array($p) ? ($p['price'] ?? 0) : ($p->price ?? 0);
                $priceMap[$levelUuid] = (float) $price;
            }

            $row = [
                (string) ($item['item_code'] ?? $item->item_code ?? ''),
                (string) ($item['item_name'] ?? $item->item_name ?? ''),
                (float) ($item['item_base_price_buy'] ?? $item->item_base_price_buy ?? 0),
                (float) ($item['item_base_price_sell'] ?? $item->item_base_price_sell ?? 0),
            ];
            foreach ($levels as $lvl) {
                $uuid = (string) ($lvl['uuid'] ?? $lvl->uuid ?? '');
                $row[] = $priceMap[$uuid] ?? 0;
            }
            $rows[] = $row;
        }

        return [
            'sheet' => 'Data Harga',
            'headers' => $headers,
            'rows' => $rows,
            'guide' => [
                '1. Kolom KODE ITEM: JANGAN DIUBAH (sebagai kunci update).',
                '2. Kolom NAMA ITEM: Hanya sebagai referensi visual, tidak akan mengupdate nama barang.',
                '3. HARGA BELI (BASE) & HARGA JUAL (BASE): Harga dasar di master barang.',
                '4. Kolom LEVEL: XXX: Harga spesifik untuk level terkait (misal: Umum, BPJS, Resep).',
                '5. Pastikan format angka benar (tidak ada karakter non-angka).',
            ],
        ];
    }

    /**
     * Template import = export seluruh item aktif tanpa filter (siap diedit ulang).
     */
    public function getImportTemplate(): array
    {
        return $this->exportPrices([]);
    }

    /**
     * Import rows dari Excel yang sudah di-parse menjadi array.
     *
     * @param  array<int, array<int, string|int|float>>  $rows
     */
    public function importExcel(array $rows, string $actorUuid): array
    {
        if (empty($rows) || count($rows) < 2) {
            throw new \InvalidArgumentException('Tidak ada data untuk diimport.');
        }

        $levels = method_exists($this->itemPriceRepository, 'getActivePriceLevels')
            ? $this->itemPriceRepository->getActivePriceLevels()
            : [];

        // Mapping kolom header "LEVEL: <name>" → price_level_uuid.
        $header = $rows[0];
        $levelColMap = [];
        foreach ($header as $colIdx => $h) {
            $text = strtoupper(trim((string) $h));
            if (! str_starts_with($text, 'LEVEL: ')) {
                continue;
            }
            $lvlName = trim(substr($text, 7));
            foreach ($levels as $lvl) {
                $lvlCmp = strtoupper(trim((string) ($lvl['name'] ?? $lvl->name ?? '')));
                if ($lvlCmp === $lvlName) {
                    $levelColMap[$colIdx] = (string) ($lvl['uuid'] ?? $lvl->uuid ?? '');
                    break;
                }
            }
        }

        $success = 0;
        $errors = [];

        foreach ($rows as $i => $row) {
            if ($i === 0) {
                continue;
            }
            $code = trim((string) ($row[0] ?? ''));
            if ($code === '') {
                continue;
            }

            try {
                $item = method_exists($this->itemPriceRepository, 'getByItemCode')
                    ? $this->itemPriceRepository->getByItemCode($code)
                    : null;
                if ($item === null) {
                    $errors[] = sprintf('Baris %d: Item dengan kode %s tidak ditemukan', $i + 1, $code);

                    continue;
                }
                $itemUuid = is_array($item) ? ($item['uuid'] ?? '') : ($item->uuid ?? '');

                $buyBase = (float) ($row[2] ?? 0);
                $sellBase = (float) ($row[3] ?? 0);

                if (method_exists($this->itemPriceRepository, 'updateBasePrices')) {
                    $this->itemPriceRepository->updateBasePrices($itemUuid, $buyBase, $sellBase, $actorUuid);
                } else {
                    $this->itemPriceRepository->updateBasePrice($itemUuid, $buyBase);
                }

                foreach ($levelColMap as $colIdx => $lvlUuid) {
                    $entity = ProductPriceEntity::fromArray([
                        'item_uuid' => $itemUuid,
                        'price_level_uuid' => $lvlUuid,
                        'price' => (float) ($row[$colIdx] ?? 0),
                        'is_active' => true,
                        'actor_uuid' => $actorUuid,
                    ]);
                    $this->itemPriceRepository->upsertPrice($entity);
                }
                $success++;
            } catch (\Throwable $e) {
                $errors[] = sprintf('Baris %d: %s', $i + 1, $e->getMessage());
            }
        }

        return [
            'success_count' => $success,
            'error_count' => count($errors),
            'errors' => $errors,
        ];
    }
}
