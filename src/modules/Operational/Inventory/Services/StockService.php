<?php

namespace App\Modules\Operational\Inventory\Services;

use App\Modules\Operational\Inventory\Entities\StockFilterEntity;
use App\Modules\Operational\Inventory\Repositories\StockRepository;

class StockService
{
    public function __construct(private StockRepository $stockRepository)
    {
    }

    public function valueList(StockFilterEntity $filterEntity): array
    {
        $search = $this->search($filterEntity);
        $items = $this->stockRepository->getValueList(StockFilterEntity::fromArray(['search' => $search]));

        return $this->buildPage(
            'daftar-nilai',
            'Daftar Nilai',
            'Valuasi persediaan berdasarkan stok aktif dan harga beli terakhir.',
            $search,
            [
                ['label' => 'Total Item', 'value' => $this->number($items->count()), 'hint' => 'SKU aktif yang terpantau'],
                ['label' => 'Nilai Stok', 'value' => $this->currency($items->sum('stock_value')), 'hint' => 'Akumulasi stok x harga beli'],
                ['label' => 'Qty Tersedia', 'value' => $this->number($items->sum('stock')), 'hint' => 'Total unit barang tersedia'],
            ],
            ['Kode', 'Nama Item', 'Kategori', 'Satuan', 'Stok', 'Harga Beli', 'Nilai Stok'],
            $items->map(fn (array $item): array => [
                $item['code'] ?: '-',
                $item['name'],
                $item['category'],
                $item['unit'],
                $this->number($item['stock']),
                $this->currency($item['price_buy']),
                $this->currency($item['stock_value']),
            ])->all(),
            'Belum ada data valuasi stok.'
        );
    }

    public function itemsAndServices(StockFilterEntity $filterEntity): array
    {
        $search = $this->search($filterEntity);
        $items = $this->stockRepository->getItemsAndServices(StockFilterEntity::fromArray(['search' => $search]));

        return $this->buildPage(
            'barang-dan-jasa',
            'Barang dan Jasa',
            'Daftar item persediaan dan jasa yang digunakan dalam operasional klinik.',
            $search,
            [
                ['label' => 'Total Barang', 'value' => $this->number($items->where('type', 'Barang')->count()), 'hint' => 'Item produk/farmasi'],
                ['label' => 'Total Jasa', 'value' => $this->number($items->where('type', 'Jasa')->count()), 'hint' => 'Layanan/tindakan'],
                ['label' => 'Item Aktif', 'value' => $this->number($items->where('status', 'Aktif')->count()), 'hint' => 'Tersedia untuk transaksi'],
            ],
            ['Tipe', 'Kode', 'Nama', 'Kategori', 'Satuan', 'Harga', 'Status'],
            $items->map(fn (array $item): array => [
                $item['type'],
                $item['code'] ?: '-',
                $item['name'],
                $item['category'],
                $item['unit'],
                $this->currency($item['price']),
                $item['status'],
            ])->all(),
            'Belum ada data barang maupun jasa.'
        );
    }

    public function formulaBom(StockFilterEntity $filterEntity): array
    {
        $search = $this->search($filterEntity);
        $items = $this->stockRepository->getFormulaBom(StockFilterEntity::fromArray(['search' => $search]));

        return $this->buildPage(
            'formula-bom',
            'Formula (BOM)',
            'Ringkasan paket/formula yang memanfaatkan komponen barang atau jasa.',
            $search,
            [
                ['label' => 'Total Formula', 'value' => $this->number($items->count()), 'hint' => 'Paket atau formula yang terdaftar'],
                ['label' => 'Total Komponen', 'value' => $this->number($items->sum('items_count')), 'hint' => 'Jumlah komponen seluruh formula'],
                ['label' => 'Formula Aktif', 'value' => $this->number($items->where('status', 'Aktif')->count()), 'hint' => 'Siap digunakan'],
            ],
            ['Kode', 'Nama Formula', 'Jumlah Komponen', 'Total Qty', 'Ringkasan Komponen', 'Status'],
            $items->map(fn (array $item): array => [
                $item['code'] ?: '-',
                $item['name'],
                $this->number($item['items_count']),
                $this->number($item['total_qty']),
                $item['component_summary'],
                $item['status'],
            ])->all(),
            'Belum ada formula/BOM yang terdaftar.'
        );
    }

    public function units(StockFilterEntity $filterEntity): array
    {
        $search = $this->search($filterEntity);
        $items = $this->stockRepository->getUnits(StockFilterEntity::fromArray(['search' => $search]));

        return $this->buildPage(
            'satuan',
            'Satuan',
            'Rekap satuan item yang sedang digunakan di master produk.',
            $search,
            [
                ['label' => 'Jenis Satuan', 'value' => $this->number($items->count()), 'hint' => 'Unit unik yang aktif'],
                ['label' => 'Total Produk', 'value' => $this->number($items->sum('products_count')), 'hint' => 'Produk yang sudah punya satuan'],
                ['label' => 'Nilai Stok', 'value' => $this->currency($items->sum('stock_value')), 'hint' => 'Akumulasi nilai per satuan'],
            ],
            ['Satuan', 'Jumlah Produk', 'Total Stok', 'Nilai Stok'],
            $items->map(fn (array $item): array => [
                $item['unit'],
                $this->number($item['products_count']),
                $this->number($item['total_stock']),
                $this->currency($item['stock_value']),
            ])->all(),
            'Belum ada satuan yang terdeteksi pada master produk.'
        );
    }

    public function openingBalance(StockFilterEntity $filterEntity): array
    {
        $search = $this->search($filterEntity);
        $items = $this->stockRepository->getOpeningBalance(StockFilterEntity::fromArray(['search' => $search]));

        return $this->buildPage(
            'saldo-awal',
            'Saldo Awal',
            'Baseline saldo awal persediaan, sementara dibaca dari stok dan harga beli master produk.',
            $search,
            [
                ['label' => 'Item Bersaldo', 'value' => $this->number($items->count()), 'hint' => 'Produk dengan stok atau harga beli'],
                ['label' => 'Qty Awal', 'value' => $this->number($items->sum('opening_qty')), 'hint' => 'Total unit yang tercatat'],
                ['label' => 'Nilai Awal', 'value' => $this->currency($items->sum('opening_value')), 'hint' => 'Estimasi saldo awal persediaan'],
            ],
            ['Kode', 'Nama Item', 'Satuan', 'Qty Awal', 'Harga Awal', 'Nilai Awal'],
            $items->map(fn (array $item): array => [
                $item['code'] ?: '-',
                $item['name'],
                $item['unit'],
                $this->number($item['opening_qty']),
                $this->currency($item['opening_price']),
                $this->currency($item['opening_value']),
            ])->all(),
            'Belum ada saldo awal persediaan yang dapat ditampilkan.',
            'Jika nanti dibuat tabel saldo awal stok tersendiri, halaman ini bisa dihubungkan langsung ke sumber data tersebut.'
        );
    }

    public function itemConversion(StockFilterEntity $filterEntity): array
    {
        $search = $this->search($filterEntity);
        $items = $this->stockRepository->getItemConversions(StockFilterEntity::fromArray(['search' => $search]));

        return $this->buildPage(
            'konversi-item',
            'Konversi Item',
            'Persiapan konfigurasi multi-unit untuk item stok.',
            $search,
            [
                ['label' => 'Master Konversi', 'value' => '0', 'hint' => 'Belum ada tabel konversi khusus'],
                ['label' => 'Satuan Terdeteksi', 'value' => $this->number($items->count()), 'hint' => 'Unit yang bisa dijadikan acuan'],
                ['label' => 'Status', 'value' => 'Draft', 'hint' => 'Halaman siap dihubungkan ke master konversi'],
            ],
            ['Item', 'Dari Satuan', 'Ke Satuan', 'Rasio', 'Catatan'],
            $items->map(fn (array $item): array => [
                $item['item'],
                $item['from_unit'],
                $item['to_unit'],
                $item['ratio'],
                $item['note'],
            ])->all(),
            'Belum ada master konversi item.',
            'Saat ini schema belum memiliki tabel konversi item. Controller, service, dan repository sudah disiapkan agar nanti tinggal disambungkan ke tabel baru.'
        );
    }

    public function warehouses(StockFilterEntity $filterEntity): array
    {
        $search = $this->search($filterEntity);
        $items = $this->stockRepository->getWarehouses(StockFilterEntity::fromArray(['search' => $search]));

        return $this->buildPage(
            'gudang',
            'Gudang',
            'Ringkasan stok per cabang/gudang berdasarkan struktur branch yang aktif.',
            $search,
            [
                ['label' => 'Total Gudang', 'value' => $this->number($items->count()), 'hint' => 'Cabang/gudang terdaftar'],
                ['label' => 'Total SKU', 'value' => $this->number($items->sum('products_count')), 'hint' => 'Produk yang tersimpan'],
                ['label' => 'Nilai Gudang', 'value' => $this->currency($items->sum('stock_value')), 'hint' => 'Akumulasi nilai stok per gudang'],
            ],
            ['Gudang', 'Kode', 'Jumlah SKU', 'Total Stok', 'Stok Menipis', 'Nilai Stok'],
            $items->map(fn (array $item): array => [
                $item['warehouse'],
                $item['code'],
                $this->number($item['products_count']),
                $this->number($item['total_stock']),
                $this->number($item['low_stock']),
                $this->currency($item['stock_value']),
            ])->all(),
            'Belum ada data gudang/cabang yang dapat ditampilkan.',
            'Saat ini gudang dipetakan dari data cabang. Jika nanti ada tabel gudang terpisah, halaman ini bisa dipisahkan lebih detail.'
        );
    }

    private function buildPage(
        string $key,
        string $title,
        string $subtitle,
        string $search,
        array $stats,
        array $columns,
        array $rows,
        string $emptyMessage,
        ?string $note = null
    ): array {
        return [
            'currentKey' => $key,
            'pageTitle' => $title,
            'pageSubtitle' => $subtitle,
            'search' => $search,
            'stats' => $stats,
            'columns' => $columns,
            'rows' => $rows,
            'emptyMessage' => $emptyMessage,
            'note' => $note,
            'tabs' => [
                ['key' => 'daftar-nilai', 'label' => 'Daftar Nilai', 'url' => '/stok/daftar-nilai'],
                ['key' => 'barang-dan-jasa', 'label' => 'Barang dan Jasa', 'url' => '/stok/barang-dan-jasa'],
                ['key' => 'formula-bom', 'label' => 'Formula (BOM)', 'url' => '/stok/formula-bom'],
                ['key' => 'satuan', 'label' => 'Satuan', 'url' => '/stok/satuan'],
                ['key' => 'saldo-awal', 'label' => 'Saldo Awal', 'url' => '/stok/saldo-awal'],
                ['key' => 'konversi-item', 'label' => 'Konversi Item', 'url' => '/stok/konversi-item'],
                ['key' => 'gudang', 'label' => 'Gudang', 'url' => '/stok/gudang'],
            ],
        ];
    }

    private function search(StockFilterEntity $filterEntity): string
    {
        return trim((string) ($filterEntity->search ?? ''));
    }

    private function currency(float|int $value): string
    {
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }

    private function number(float|int $value): string
    {
        return number_format((float) $value, 0, ',', '.');
    }
}
