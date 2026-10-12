<?php

namespace App\Modules\Operational\Master\Services;

use App\Modules\Operational\Master\Entities\PaymentTermEntity;
use App\Modules\Operational\Master\Repositories\PaymentTermRepositoryInterface;
use Illuminate\Support\Str;

/**
 * PaymentTermService
 * 
 * Port dari BE: payment_term_service.go. Mengelola Master Termin Pembayaran.
 */
class PaymentTermService
{
    public function __construct(protected PaymentTermRepositoryInterface $paymentTermRepository)
    {
    }

    /**
     * Daftar Termin Pembayaran dengan pagination dan filter.
     */
    public function index(PaymentTermEntity $entity): array
    {
        return $this->paymentTermRepository->getPageData($entity);
    }

    /**
     * Ambil detail termin berdasarkan UUID.
     */
    public function show(PaymentTermEntity $entity): ?PaymentTermEntity
    {
        if (empty($entity->uuid)) {
            return null;
        }
        return $this->paymentTermRepository->findByUuid($entity);
    }

    /**
     * Simpan data termin pembayaran.
     */
    public function save(PaymentTermEntity $entity): PaymentTermEntity
    {
        if (empty($entity->name)) {
            throw new \InvalidArgumentException('nama termin wajib diisi');
        }

        return $this->paymentTermRepository->save($entity);
    }

    /**
     * Hapus termin pembayaran.
     */
    public function destroy(PaymentTermEntity $entity): array
    {
        if (empty($entity->uuid)) {
            throw new \InvalidArgumentException('id termin tidak valid');
        }

        $this->paymentTermRepository->delete($entity);

        return ['message' => 'Termin berhasil dihapus'];
    }

    /**
     * Export data ke array (untuk diolah layer Controller/Excel).
     */
    public function export(PaymentTermEntity $entity): array
    {
        return $this->paymentTermRepository->listForExport($entity);
    }

    /**
     * Template Excel untuk import termin.
     */
    public function getImportTemplate(): array
    {
        return [
            'sheet' => 'Data Termin',
            'headers' => ['Nama Termin', 'Deskripsi', 'Jumlah Hari', 'Aktif', 'Utama'],
            'sample_row' => ['T7', 'Termin 7 Hari', 7, 'aktif', 'tidak'],
            'guide' => [
                '1. Nama Termin wajib diisi (misal: T30).',
                '2. Jumlah Hari wajib angka positif.',
                '3. Kolom Aktif/Utama diisi: aktif/tidak atau ya/tidak atau true/false.',
            ],
        ];
    }

    /**
     * Import data dari array hasil parsing Excel.
     *
     * @param array  $rows
     * @param string $branchUuid   UUID cabang dari auth user
     * @param string $companyUuid  UUID perusahaan dari auth user — wajib agar upsert scoped by company
     */
    public function importRows(array $rows, string $branchUuid = '', string $companyUuid = ''): array
    {
        if (count($rows) === 0) {
            throw new \InvalidArgumentException('file tidak boleh kosong');
        }

        $summary = [
            'total' => 0,
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($rows as $i => $row) {
            if ($i === 0 || count($row) < 1) {
                continue;
            }

            $name = trim((string) ($row[0] ?? ''));
            if ($name === '') {
                continue;
            }

            $summary['total']++;

            try {
                $desc = trim((string) ($row[1] ?? ''));
                $days = (int) ($row[2] ?? 0);

                $active = true;
                if (isset($row[3])) {
                    $v = strtolower((string) $row[3]);
                    $active = $v === 'aktif' || $v === 'ya' || $v === 'true' || $v === '1';
                }

                $primary = false;
                if (isset($row[4])) {
                    $v = strtolower((string) $row[4]);
                    $primary = $v === 'aktif' || $v === 'ya' || $v === 'true' || $v === '1';
                }

                $this->save(new PaymentTermEntity(
                    name: $name,
                    description: $desc,
                    due_days: $days,
                    is_active: $active,
                    is_primary: $primary,
                    branch_uuid: $branchUuid,
                    company_uuid: $companyUuid,
                ));
                $summary['success']++;
            } catch (\Throwable $e) {
                $summary['failed']++;
                $summary['errors'][] = "Baris " . ($i + 1) . ": " . $e->getMessage();
            }
        }

        return $summary;
    }

    public function getPageData(PaymentTermEntity $entity): array
    {
        return $this->paymentTermRepository->getPageData($entity);
    }

    public function findByUuid(PaymentTermEntity $entity): ?PaymentTermEntity
    {
        return $this->paymentTermRepository->findByUuid($entity);
    }

    public function delete(PaymentTermEntity $entity): void
    {
        $this->paymentTermRepository->delete($entity);
    }
}
