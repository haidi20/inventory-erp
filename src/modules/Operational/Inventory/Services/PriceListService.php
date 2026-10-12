<?php

namespace App\Modules\Operational\Inventory\Services;

use App\Modules\Operational\Inventory\Entities\PriceListFilterEntity;
use App\Modules\Operational\Inventory\Repositories\PriceListRepository;
use Illuminate\Support\Facades\Validator;

class PriceListService
{
    public function __construct(private PriceListRepository $priceListRepository)
    {
    }

    public function index(PriceListFilterEntity $filterEntity): array
    {
        $data = Validator::make([
            'search' => $filterEntity->search,
            'tab' => $filterEntity->tab,
            'company_uuid' => $filterEntity->company_uuid,
            'branch_uuid' => $filterEntity->branch_uuid,
        ], [
            'search' => ['nullable', 'string', 'max:100'],
            'tab' => ['nullable', 'in:products,services,packages'],
            'company_uuid' => ['nullable', 'string', 'max:36'],
            'branch_uuid' => ['nullable', 'string', 'max:36'],
        ])->validate();

        $validatedEntity = new PriceListFilterEntity(
            search: $data['search'] ?? null,
            tab: $data['tab'] ?? null,
            company_uuid: $filterEntity->company_uuid,
            branch_uuid: $filterEntity->branch_uuid,
        );
        $search = trim((string) ($validatedEntity->search ?? ''));
        $currentTab = (string) ($validatedEntity->tab ?? 'products');

        $rows = match ($currentTab) {
            'services' => $this->priceListRepository->getServices($validatedEntity),
            'packages' => $this->priceListRepository->getPackages($validatedEntity),
            default => $this->priceListRepository->getProducts($validatedEntity),
        };

        $summary = $this->buildSummary($currentTab, $rows);

        return [
            'currentTab' => $currentTab,
            'pageTitle' => 'Daftar Harga',
            'pageSubtitle' => 'Ringkasan harga jual untuk produk, layanan, dan paket klinik.',
            'search' => $search,
            'stats' => $summary['stats'],
            'columns' => $summary['columns'],
            'rows' => $summary['rows'],
            'emptyMessage' => 'Belum ada data harga untuk tab yang dipilih.',
            'note' => 'Harga pada halaman ini bersifat referensi operasional. Perubahan harga dilakukan dari master data masing-masing.',
            'tabs' => [
                ['key' => 'products', 'label' => 'Obat/Alkes', 'url' => '/daftar-harga?tab=products'],
                ['key' => 'services', 'label' => 'Layanan', 'url' => '/daftar-harga?tab=services'],
                ['key' => 'packages', 'label' => 'Paket', 'url' => '/daftar-harga?tab=packages'],
            ],
        ];
    }

    private function buildSummary(string $currentTab, $rows): array
    {
        if ($currentTab === 'services') {
            return [
                'stats' => [
                    ['label' => 'Total Layanan', 'value' => $this->number($rows->count()), 'hint' => 'Jenis tindakan atau layanan aktif'],
                    ['label' => 'Rata-rata Tarif', 'value' => $this->currency($rows->avg('price') ?? 0), 'hint' => 'Tarif rata-rata layanan'],
                    ['label' => 'Fee Dokter', 'value' => $this->currency($rows->sum('doctor_fee')), 'hint' => 'Akumulasi fee default dokter'],
                ],
                'columns' => ['Kode', 'Nama Layanan', 'Durasi', 'Tarif', 'Fee Dokter', 'Status'],
                'rows' => $rows->map(fn (array $item): array => [
                    $item['code'],
                    $item['name'],
                    $item['duration'],
                    $this->currency($item['price']),
                    $this->currency($item['doctor_fee']),
                    $item['status'],
                ])->all(),
            ];
        }

        if ($currentTab === 'packages') {
            return [
                'stats' => [
                    ['label' => 'Total Paket', 'value' => $this->number($rows->count()), 'hint' => 'Paket layanan siap jual'],
                    ['label' => 'Rata-rata Harga', 'value' => $this->currency($rows->avg('price') ?? 0), 'hint' => 'Harga rata-rata paket'],
                    ['label' => 'Paket Aktif', 'value' => $this->number($rows->where('status', 'Aktif')->count()), 'hint' => 'Masih aktif di sistem'],
                ],
                'columns' => ['Kode', 'Nama Paket', 'Jumlah Komponen', 'Harga', 'Status'],
                'rows' => $rows->map(fn (array $item): array => [
                    $item['code'],
                    $item['name'],
                    $this->number($item['items_count']),
                    $this->currency($item['price']),
                    $item['status'],
                ])->all(),
            ];
        }

        return [
            'stats' => [
                ['label' => 'Total Item', 'value' => $this->number($rows->count()), 'hint' => 'Produk dengan harga jual'],
                ['label' => 'Rata-rata Harga', 'value' => $this->currency($rows->avg('price_sell') ?? 0), 'hint' => 'Harga jual rata-rata'],
                ['label' => 'Margin Rata-rata', 'value' => $this->currency($rows->avg('margin') ?? 0), 'hint' => 'Selisih harga jual dan beli'],
            ],
            'columns' => ['Kode', 'Nama Item', 'Kategori', 'Satuan', 'Harga Beli', 'Harga Jual', 'Margin', 'Status'],
            'rows' => $rows->map(fn (array $item): array => [
                $item['code'],
                $item['name'],
                $item['category'],
                $item['unit'],
                $this->currency($item['price_buy']),
                $this->currency($item['price_sell']),
                $this->currency($item['margin']),
                $item['status'],
            ])->all(),
        ];
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
