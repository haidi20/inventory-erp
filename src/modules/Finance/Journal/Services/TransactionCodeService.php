<?php

namespace App\Modules\Finance\Journal\Services;

use App\Shared\Support\ExceptionLogger;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Core\Organization\Services\BranchService;
use App\Modules\Finance\Journal\Repositories\TransactionSequenceRepository;
use Throwable;
use Exception;

/**
 * Service untuk generate kode transaksi unik per perusahaan
 * Format: {SHORT_NAME}/{SOURCE}/{YYYYMM}/{INDEX}
 * Contoh: MDK/SAL/202603/0001
 *
 * Index di-reset setiap bulan per perusahaan untuk kerapihan administratif
 */
class TransactionCodeService
{
    public function __construct(
        protected BranchService $branchRepository,
        protected TransactionSequenceRepository $sequenceRepository
    ) {}

    public function generate(string $branchUuid, ?JournalReferenceType $sourceType = null): string
    {
        try {
            $company = $this->branchRepository->findByUuid($branchUuid);
            if (!$company) {
                throw new Exception("Branch with UUID {$branchUuid} not found.");
            }
            $branchCode = strtoupper($company->short_name);

            // Tentukan kode sumber (e.g., JR, BKM, BKK, OB)
            $typeCode = $this->resolveSourceCode($sourceType);

            // Format bulan: YYMM
            $yearMonth = now()->format('y') . now()->format('m');
            $period = now()->format('Ym');

            // GUNAKAN PESSIMISTIC LOCKING UNTUK MENCEGAH DUPLIKAT (#24)
            $index = str_pad((string) $this->sequenceRepository->nextIndex($branchUuid, $typeCode, $period), 4, '0', STR_PAD_LEFT);
            $code = "{$typeCode}/{$branchCode}/{$yearMonth}/{$index}";

            return $code;
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Konversi JournalReferenceType ke kode pendek untuk transaction_code
     */
    protected function resolveSourceCode(?JournalReferenceType $type): string
    {
        try {
            return match ($type) {
                JournalReferenceType::SALE, 
                JournalReferenceType::POS_SALE,
                JournalReferenceType::RECEIVABLE_COLLECTION => 'BKM', // Bukti Kas Masuk
                
                JournalReferenceType::PURCHASE, 
                JournalReferenceType::EXPENSE,
                JournalReferenceType::DEBT_PAYMENT => 'BKK', // Bukti Kas Keluar
                
                JournalReferenceType::OPENING_BALANCE => 'OB', // Opening Balance
                
                default => 'JR', // Jurnal Umum / General Journal
            };
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }
}
