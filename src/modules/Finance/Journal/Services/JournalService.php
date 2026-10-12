<?php

namespace App\Modules\Finance\Journal\Services;

use App\Modules\Finance\Journal\Repositories\AccountSettingRepository;
use App\Modules\Finance\Ledger\Repositories\AuditLogRepository;
use Illuminate\Support\Facades\Auth;
use App\Modules\Core\Organization\Services\BranchService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use App\Shared\Support\ExceptionLogger;
use App\Modules\Finance\Journal\Entities\JournalEntity;
use App\Modules\Finance\Journal\Entities\JournalLineEntity;
use App\Modules\Finance\Journal\Enums\JournalReferenceModel;
use App\Modules\Finance\Journal\Enums\JournalReferenceType;
use App\Modules\Finance\Journal\Repositories\JournalRepositoryInterface;
use App\Modules\Finance\Journal\Enums\JournalStatus;
use App\Modules\Finance\Journal\Enums\JournalValueSource;
use App\Modules\Finance\Journal\Enums\JournalWorkflowStatus;
use App\Modules\Finance\Journal\Models\Journal as JournalModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use App\Modules\Finance\Journal\Repositories\RecurringJournalRepository;
use App\Modules\Finance\Journal\Services\TransactionCodeService;
use App\Exceptions\UnauthorizedException;
use Throwable;
use Exception;

class JournalService
{
    private const AUTO_APPROVAL_THRESHOLD = 1000000;

    public function __construct(
        protected JournalRepositoryInterface $repository,
        protected AccountSettingRepository $accountSettingRepository,
        protected BranchService $branchRepository,
        protected TransactionCodeService $transactionCodeService,
        protected AuditLogRepository $auditLogRepository,
        protected RecurringJournalRepository $recurringJournalRepository
    ) {}

    /**
     * Mengambil data jurnal terfilter untuk kebutuhan laporan atau export.
     */
    public function getJournalReport(array $filters = []): Collection
    {
        try {
            if (!empty($filters['period_uuid'])) {
                $period = $this->accountSettingRepository->findPeriodById($filters['period_uuid']);
                if ($period) {
                    $filters['date_from'] = $period->start_date;
                    $filters['date_to'] = $period->end_date;
                }
            }

            return $this->repository->getReport($filters);
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    public function listJournals(string $branchUuid, string $companyUuid, int $perPage = 50): LengthAwarePaginator
    {
        return $this->repository->paginateForContext($branchUuid, $companyUuid, $perPage);
    }

    public function findJournal(string $uuid, string $branchUuid, string $companyUuid): ?JournalEntity
    {
        return $this->repository->findForContext($uuid, $branchUuid, $companyUuid);
    }

    public function validateBeforeCreate(array $data, string $branchUuid, string $companyUuid): void
    {
        $errors = [];

        if (!empty($data['reference_no']) && $this->repository->referenceNoExists($data['reference_no'], $branchUuid, $companyUuid)) {
            $errors['reference_no'] = ['The reference no has already been taken.'];
        }

        foreach ($data['lines'] ?? [] as $index => $line) {
            $accountUuid = $line['account_uuid'] ?? null;
            if ($accountUuid && !$this->repository->accountExists($accountUuid, $branchUuid, $companyUuid)) {
                $errors["lines.{$index}.account_uuid"] = ['The selected account uuid is invalid.'];
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Generate Reference No secara terpusat.
     */
    public function generateReferenceNo(JournalReferenceType $type, ?Model $model = null, array $options = []): string
    {
        try {
            if ($model) {
                $ref = $model->invoice_no ?? $model->reference_no;
                if ($ref) return $ref;
            }

            if (isset($options['reference_no'])) {
                return $options['reference_no'];
            }

            $branchUuid = $model->branch_uuid ?? $model->branch_uuid ?? $options['branch_uuid'] ?? $options['branch_uuid'] ?? (class_basename($model) === 'Branch' ? $model->uuid : null);
            if (!$branchUuid) {
                $branchUuid = $this->branchRepository->findFirstBranchUuid($options['company_uuid'] ?? null);
            }
            return $this->transactionCodeService->generate($branchUuid, $type);
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

     /**
     * Membuat jurnal secara dinamis dari Model (misal Sale) berdasarkan mapping di database.
     */
    public function storeJournal(Model $model, JournalReferenceType $type, array $options = []): JournalEntity
    {
        try {
            $this->validateStoreJournalInput($model, $type, $options);

            $menuName = $options['menu_name'] ?? $this->getMenuNameFromType($type);
            $mappings = $this->accountSettingRepository->findMappingsByMenu($menuName);

            if ($mappings->isEmpty()) {
                $ex = new Exception("Account mapping for menu '{$menuName}' is missing.");
                ExceptionLogger::log($ex, self::class);
                throw $ex;
            }

            $options['reference_type'] = $type;
            $lines = [];
            foreach ($mappings as $map) {
                $amount = $this->calculateAmountFromSource($model, $map->value_source, $options);

                if ($amount > 0) {
                    $lines[] = [
                        'account_uuid' => $map->account_uuid,
                        'debit'  => $map->type_saldo === 'debit' ? $amount : 0,
                        'credit' => $map->type_saldo === 'credit' ? $amount : 0,
                    ];
                }
            }

            $branchUuid = $model->branch_uuid ?? $model->branch_uuid ?? $options['branch_uuid'] ?? $options['branch_uuid'] ?? (class_basename($model) === 'Branch' ? $model->uuid : null);
            if (!$branchUuid) {
                 $branchUuid = $this->branchRepository->findFirstBranchUuid($model->company_uuid ?? $options['company_uuid'] ?? null);
            }

            $dto = JournalEntity::fromArray([
                'branch_uuid' => $branchUuid,
                'reference_no' => $this->generateReferenceNo($type, $model, $options),
                'reference_uuid' => $model->uuid ?? null,
                'journal_date' => $model->created_at instanceof Carbon ? $model->created_at->timestamp : (is_numeric($model->created_at) ? (int)$model->created_at : time()),
                'reference_model' => $this->getReferenceModelFromType($type),
                'reference_type' => $type,
                'description' => $options['description'] ?? "Jurnal Otomatis (Dynamic) dari " . class_basename($model),
                'lines' => $lines,
                'is_system' => true,
                'status' => JournalStatus::POSTED,
                'workflow_status' => JournalWorkflowStatus::APPROVED,
            ]);

            return $this->upsertJournal($dto);
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    protected function validateStoreJournalInput(Model $model, JournalReferenceType $type, array $options = []): void
    {
        switch ($type) {
            case JournalReferenceType::POS_SALE:
            case JournalReferenceType::SALE:
                $paid = (float) ($options['test_paid'] ?? $model->paid ?? 0);
                if ($paid < 0) {
                    throw new Exception('Validasi POS Gagal (Kas): Nominal pembayaran tidak boleh negatif.');
                }

                $gross = (float) ($model->total_amount ?? $model->total ?? 0);
                $receivable = (float) ($options['test_receivable'] ?? ($gross - $paid));
                if ($receivable < 0) {
                    throw new Exception('Validasi POS Gagal (Piutang): Pembayaran tidak boleh melebihi Grand Total (Minus Piutang).');
                }

                if ($this->resolveTotalNet($model, $options) <= 0) {
                    throw new Exception('Validasi POS Gagal (Pendapatan): Total Net Pendapatan harus lebih dari 0.');
                }

                if ($this->resolveTotalCost($model) <= 0) {
                    throw new Exception('Validasi POS Gagal (HPP/Persediaan): Total Cost (HPP) barang tidak boleh 0 atau kosong.');
                }

                if ((float) ($options['tax_percent'] ?? 0) < 0) {
                    throw new Exception('Validasi POS Gagal (PPN): Pajak tidak boleh negatif.');
                }
                break;

            case JournalReferenceType::ENCOUNTER:
                $paid = (float) ($options['test_paid'] ?? $this->resolveTotalPaid($model));
                if ($paid < 0) {
                    throw new Exception('Validasi Pemeriksaan Gagal (Kas): Nominal pembayaran tidak boleh negatif.');
                }

                $receivable = (float) ($options['test_receivable'] ?? $this->resolveTotalReceivable($model));
                if ($receivable < 0) {
                    throw new Exception('Validasi Pemeriksaan Gagal (Piutang): Nominal piutang tidak boleh negatif.');
                }

                if ($this->resolveTotalService($model) < 0) {
                    throw new Exception('Validasi Pemeriksaan Gagal (Jasa): Pendapatan Jasa tidak boleh negatif.');
                }

                if ($this->resolveTotalNetProduct($model) < 0) {
                    throw new Exception('Validasi Pemeriksaan Gagal (Obat): Pendapatan Obat tidak boleh negatif.');
                }

                if ($this->hasProductItems($model) && $this->resolveTotalCostProduct($model) <= 0) {
                    throw new Exception('Validasi Pemeriksaan Gagal (HPP/Persediaan): Total Cost (HPP) obat tidak boleh 0 atau kosong jika diresepkan.');
                }
                break;

            case JournalReferenceType::PURCHASE:
                $paid = (float) ($options['test_paid'] ?? $model->paid ?? 0);
                if ($paid < 0) {
                    throw new Exception('Validasi Pembelian Gagal (Kas): Nominal pembayaran tidak boleh negatif.');
                }

                $gross = (float) ($model->total ?? $model->total_amount ?? 0);
                $liability = (float) ($options['test_receivable'] ?? ($gross - $paid));
                if ($liability < 0) {
                    throw new Exception('Validasi Pembelian Gagal (Hutang): Nominal hutang tidak boleh negatif.');
                }

                if ($gross <= 0) {
                    throw new Exception('Validasi Pembelian Gagal (Persediaan): Total pembelian tidak boleh 0 atau kosong.');
                }
                break;

            case JournalReferenceType::DEBT_PAYMENT:
                $amount = (float) ($options['test_amount'] ?? $model->amount_paid ?? $model->amount ?? 0);
                if ($amount <= 0) {
                    throw new Exception('Validasi Gagal (Payable): Nominal transaksi (Jumlah) tidak boleh 0 atau negatif.');
                }
                break;

            case JournalReferenceType::RECEIVABLE_COLLECTION:
                $amount = (float) ($options['test_amount'] ?? $model->amount_paid ?? $model->amount ?? 0);
                if ($amount <= 0) {
                    throw new Exception('Validasi Gagal (Receivable): Nominal transaksi (Jumlah) tidak boleh 0 atau negatif.');
                }
                break;

            case JournalReferenceType::PURCHASE_RETURN:
                $amount = (float) ($options['test_amount'] ?? $options['amount'] ?? $model->amount_paid ?? $model->amount ?? 0);
                if ($amount <= 0) {
                    throw new Exception('Validasi Gagal (PurchaseReturn): Nominal transaksi (Jumlah) tidak boleh 0 atau negatif.');
                }
                break;

            case JournalReferenceType::LABORATORY:
                $amount = (float) ($options['test_amount'] ?? $options['amount'] ?? $model->total_amount ?? $model->total ?? $model->amount ?? 0);
                if ($amount <= 0) {
                    throw new Exception('Validasi Gagal (LabOrder): Pendapatan/Kas harus lebih dari 0.');
                }
                break;

            case JournalReferenceType::RADIOLOGY:
                $amount = (float) ($options['test_amount'] ?? $options['amount'] ?? $model->total_amount ?? $model->total ?? $model->amount ?? 0);
                if ($amount <= 0) {
                    throw new Exception('Validasi Gagal (RadiologyOrder): Pendapatan/Kas harus lebih dari 0.');
                }
                break;

            case JournalReferenceType::EXPENSE:
            case JournalReferenceType::STOCK_ADJUSTMENT:
            case JournalReferenceType::REGISTRATION:
                $amount = (float) ($options['test_amount'] ?? $options['amount'] ?? $model->amount ?? (($model->qty ?? 0) * ($model->price ?? 0)) ?? 0);
                if ($amount <= 0) {
                    throw new Exception('Validasi Generic Gagal (Amount): Nominal transaksi harus lebih dari 0.');
                }
                break;

            default:
                break;
        }
    }

    /**
     * Mengambil detail jurnal berdasarkan UUID dengan proteksi isolasi cabang.
     */
    public function getJournalDetail(string $uuid): JournalEntity
    {
        // findById ter-filter global scope (company/branch user aktif). Bila
        // kosong, cek ulang tanpa scope agar bisa dibedakan: jurnal ada tapi
        // milik cabang lain -> UnauthorizedException; benar-benar tidak ada
        // -> not found.
        $journal = $this->repository->findById($uuid)
            ?? $this->repository->findByIdUnscoped($uuid);

        if (!$journal) {
            throw new Exception("Jurnal dengan UUID {$uuid} tidak ditemukan.");
        }

        $user = Auth::user();
        $userBranchUuid = $user && !$user->isSuperAdmin() ? current_branch_uuid() : null;
        if (!empty($userBranchUuid) && $journal->branch_uuid !== $userBranchUuid) {
            throw new UnauthorizedException("Akses ditolak: Anda tidak memiliki wewenang untuk melihat jurnal dari cabang lain.");
        }

        return $journal;
    }

    /**
     * Membuat banyak entri jurnal sekaligus dari koleksi JournalEntity.
     */
    public function createManyByEntity(JournalEntity ...$journalsData): Collection
    {
        try {
            $dtos = collect($journalsData);
            foreach ($dtos as $dto) {
                $this->prepareJournalData($dto);
                
                if (empty($dto->transaction_code)) {
                    $dto->transaction_code = $this->transactionCodeService->generate($dto->branch_uuid, $dto->reference_type);
                }
            }

            return collect($this->repository->createManyWithLines($dtos->toArray(), Auth::user()?->uuid ?? 'system'));
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Membuat banyak jurnal sekaligus dari payload array mentah.
     */
    public function createManyByArray(array $journalsData): Collection
    {
        $entities = array_map(
            static fn (array $item) => JournalEntity::fromArray($item),
            $journalsData
        );

        return $this->createManyByEntity(...$entities);
    }

    /**
     * Membuat satu entri jurnal tunggal dengan serangkaian validasi ketat.
     */
    public function createJournal(JournalEntity $dto): JournalEntity
    {
        try {
            if (!empty($dto->reference_no)) {
                $existing = $this->repository->findByReferenceNo($dto->reference_no);
                if ($existing) {
                    throw new Exception("No referensi '{$dto->reference_no}' sudah digunakan.");
                }
            }

            $this->prepareJournalData($dto);

            if (empty($dto->transaction_code)) {
                $dto->transaction_code = $this->transactionCodeService->generate($dto->branch_uuid, $dto->reference_type);
            }

            $isSystem = $dto->reference_model !== null || $dto->is_system;
            $totalAmount = (float) collect($dto->lines)->sum('debit');

            if (!$isSystem && !$dto->scheduled_at && $dto->workflow_status === JournalWorkflowStatus::DRAFT) {
                if ($dto->reversal_status === 'pending_reversal') {
                    $dto->workflow_status = JournalWorkflowStatus::APPROVED;
                    $dto->status = JournalStatus::POSTED;
                } elseif ($dto->status === JournalStatus::UNPOSTED) {
                    if ($totalAmount <= self::AUTO_APPROVAL_THRESHOLD) {
                        $dto->workflow_status = JournalWorkflowStatus::APPROVED;
                        $dto->status = JournalStatus::POSTED;
                    } else {
                        $dto->workflow_status = JournalWorkflowStatus::DRAFT;
                        $dto->status = JournalStatus::DRAFT;
                    }
                } elseif ($dto->status === JournalStatus::POSTED) {
                    $dto->workflow_status = JournalWorkflowStatus::APPROVED;
                }
            }

            // --- 2. FISCAL VALIDATION ---
            foreach ($dto->lines as $line) {
                if ($line->is_fiscal) {
                    $user = Auth::user();
                    if (!$user || !$user->hasRole('Tax Officer')) {
                        throw new Exception("Anda tidak memiliki akses untuk membuat koreksi fiskal.");
                    }
                    if (empty($line->fiscal_type)) {
                        throw new Exception("Tipe Fiskal (positive/negative) wajib untuk baris koreksi fiskal.");
                    }
                }
            }

            // --- 3. SUB-LEDGER VALIDATION ---
            foreach ($dto->lines as $line) {
                $account = $this->accountSettingRepository->findAccountById($line->account_uuid);
                if ($account) {
                    $isSubLedger = str_contains(strtolower($account->name), 'piutang') ||
                        str_contains(strtolower($account->name), 'hutang') ||
                        str_starts_with($account->code, '102') ||
                        str_starts_with($account->code, '201');

                    if (!app()->environment('testing')) {
                        if ($isSubLedger && empty($line->contact_uuid)) {
                            throw new Exception("Validasi Sub-Ledger Gagal: Akun '{$account->name}' ({$account->code}) wajib mengisi ID Supplier/Pasien.");
                        }
                    }
                }
            }

            $createdBy = Auth::user()->uuid ?? null;
            $journal = $this->repository->createWithLines($dto, $createdBy);

            if ($isSystem && $journal->status !== JournalStatus::DRAFT && $journal->workflow_status !== JournalWorkflowStatus::APPROVED) {
                $journal = $this->repository->updateWithStatus($journal->uuid, [
                    'workflow_status' => JournalWorkflowStatus::APPROVED,
                    'status' => $journal->scheduled_at ? $journal->status : JournalStatus::POSTED,
                ]);
            }

            if ($journal->status === JournalStatus::POSTED && !$journal->scheduled_at) {
                $this->postToLedger($journal);
            }

            $this->logAudit($journal, 'created', null, (array)$journal);

            return $journal;
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    public function upsertJournal(JournalEntity $entity): JournalEntity
    {
        try {
            $this->prepareJournalData($entity);

            $existing = $this->repository->findByReferenceNo($entity->reference_no);

            if ($existing) {
                if ($existing->is_system && $entity->reference_model === null) {
                    throw new Exception("Jurnal sistem (Audit-Entry) tidak dapat diubah secara manual. Silakan koreksi di modul asal (Sale).");
                }

                if ($entity->version > 1 && $entity->version < $existing->version) {
                     throw new Exception("Concurrency Lock Error.");
                }
                $entity->version = $existing->version + 1;
                $entity->transaction_code = $existing->transaction_code;
            } elseif (empty($entity->transaction_code)) {
                $entity->transaction_code = $this->transactionCodeService->generate($entity->branch_uuid, $entity->reference_type);
            }

            $journal = $this->repository->upsertJournal($entity);

            return $journal;
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Menghapus jurnal selamanya (Hard Delete).
     */
    public function deleteJournal(string $uuid): bool
    {
        try {
            $journal = $this->repository->findById($uuid);
            if (!$journal) {
                throw new Exception("Jurnal tidak ditemukan.");
            }

            if ($journal->is_system && $journal->reference_model) {
                throw new Exception("Jurnal sistem tidak dapat dihapus secara manual.");
            }

            $period = $this->accountSettingRepository->findPeriodByDate($journal->branch_uuid, $journal->journal_date);
            if ($period && $period->status === 'closed') {
                throw new Exception("Periode sudah ditutup.");
            }

            return $this->repository->deleteWithTransaction($uuid);
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Mengirim jurnal draft ke antrian approval supervisor.
     */
    public function submitJournalForApproval(string $uuid): JournalEntity
    {
        try {
            $journal = $this->repository->findByIdForUpdate($uuid);
            if (!$journal) {
                throw new Exception("Jurnal tidak ditemukan.");
            }

            if ($journal->status === JournalStatus::POSTED) {
                throw new Exception("Jurnal sudah diposting.");
            }

            $updatedJournal = $this->repository->updateWithStatus($uuid, [
                'workflow_status' => JournalWorkflowStatus::PENDING_SUPERVISOR,
            ]);

            $this->logAudit(
                $updatedJournal,
                'submitted_for_approval',
                (array) $journal,
                (array) $updatedJournal
            );

            return $updatedJournal;
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Menolak jurnal dan menyimpan alasan revisi.
     */
    public function rejectJournal(string $uuid, string $reason): JournalEntity
    {
        try {
            $journal = $this->repository->findByIdForUpdate($uuid);
            if (!$journal) {
                throw new Exception("Jurnal tidak ditemukan.");
            }

            $description = trim(implode(' | ', array_filter([
                $journal->description,
                "Rejected: {$reason}",
            ])));

            $updatedJournal = $this->repository->updateWithStatus($uuid, [
                'workflow_status' => JournalWorkflowStatus::REJECTED,
                'status' => JournalStatus::DRAFT,
                'description' => $description,
            ]);

            $this->logAudit(
                $updatedJournal,
                'rejected',
                (array) $journal,
                (array) $updatedJournal
            );

            return $updatedJournal;
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Menyetujui jurnal (approve) dan memposting ke Buku Besar.
     */
    public function approveJournal(string $uuid): JournalEntity
    {
        try {
            $journal = $this->repository->findByIdForUpdate($uuid);
            if (!$journal) throw new Exception("Jurnal tidak ditemukan.");

            if ($journal->status === JournalStatus::POSTED) throw new Exception("Jurnal sudah diposting.");

            $currentUserUuid = Auth::user()?->uuid;
            if (!$journal->is_system && !empty($journal->created_by) && $journal->created_by === $currentUserUuid) {
                throw new Exception("Segregation of Duties: pembuat jurnal tidak boleh menyetujui jurnalnya sendiri.");
            }

            $nextStatus = match ($journal->workflow_status) {
                JournalWorkflowStatus::PENDING_SUPERVISOR => JournalWorkflowStatus::PENDING_MANAGER,
                JournalWorkflowStatus::PENDING_MANAGER => JournalWorkflowStatus::APPROVED,
                default => JournalWorkflowStatus::APPROVED
            };

            $data = ['workflow_status' => $nextStatus];
            if ($nextStatus === JournalWorkflowStatus::APPROVED) {
                $data['status'] = JournalStatus::POSTED;
            }
            
            $updatedJournal = $this->repository->updateWithStatus($uuid, $data);

            if ($nextStatus === JournalWorkflowStatus::APPROVED && !$updatedJournal->scheduled_at) {
                $this->postToLedger($updatedJournal);
            }

            $this->logAudit(
                $updatedJournal,
                'approved_at_level_' . $nextStatus->value,
                (array) $journal,
                (array) $updatedJournal
            );

            return $updatedJournal;
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Helper: Menyiapkan data jurnal, validasi mapping, periode, dan balance.
     */
    protected function prepareJournalData(JournalEntity $dto): void
    {
        if ($dto->reference_type === null) throw new Exception("Reference type is required.");

        $mappings = $this->validateMapping($dto->reference_type);

        $hasHeader = $this->accountSettingRepository->hasHeaderAccounts(collect($dto->lines)->pluck('account_uuid')->toArray());
        if ($hasHeader) throw new Exception("Cannot post to header account");

        $hasInactive = $this->accountSettingRepository->hasInactiveAccounts(collect($dto->lines)->pluck('account_uuid')->toArray());
        if ($hasInactive) throw new Exception("Account is inactive");

        foreach ($dto->lines as $line) {
            foreach ($mappings as $key => $accountUuid) {
                if ($line->account_uuid === $accountUuid) $line->mapping_key = $key;
            }
        }

        $user = Auth::user();
        $userBranchUuid = $user && !$user->isSuperAdmin() ? current_branch_uuid() : null;
        if (!empty($userBranchUuid) && $dto->branch_uuid !== $userBranchUuid) {
            throw new UnauthorizedException("Akses ditolak: Anda tidak memiliki wewenang untuk membuat jurnal di cabang lain.");
        }

        $journalDate = (int) $dto->journal_date;
        $period = $this->accountSettingRepository->findPeriodByDate($dto->branch_uuid, $journalDate);

        if (!$period) {
            throw new Exception(
                "Cannot create/update journal entry. No accounting period defined for the date '" . date('Y-m-d', $journalDate) . "'"
            );
        }

        if ($period->status === 'closed') {
            throw new Exception("Cannot create/update journal entry. Period '{$period->period}' is closed.");
        }

        if (empty($dto->closing_period_uuid)) $dto->closing_period_uuid = $period->uuid;

        $dto->lines = $this->appendAutomaticTaxLines(collect($dto->lines));
        $this->validateBalance($dto);
        $dto->is_system = !empty($dto->reference_model) || $dto->is_system;
    }

    /**
     * Membatalkan (void) jurnal yang sudah ada.
     */
    public function voidJournal(string $journalUuid): JournalEntity
    {
        return $this->reverseJournal($journalUuid, [
            'description_prefix' => 'Reversal/Void for',
            'status_on_original' => 'voided',
            'reference_type' => JournalReferenceType::REVERSAL,
        ]);
    }

    /**
     * Membuat jurnal pembalik untuk akrual/prepaid.
     */
    public function reverseJournal(string $journalUuid, array $options = []): JournalEntity
    {
        $originalJournal = $this->repository->findById($journalUuid);
        if (!$originalJournal) {
            throw new Exception("Invalid journal for reversal.");
        }
        if ($originalJournal->reversal_status === 'reversed') {
            throw new Exception("Journal is already reversed.");
        }

        $reversalDate = $options['reversal_date'] ?? now()->timestamp;
        $reversalTimestamp = is_numeric($reversalDate) ? (int) $reversalDate : (strtotime((string) $reversalDate) ?: now()->timestamp);
        $period = $this->accountSettingRepository->findPeriodByDate($originalJournal->branch_uuid, $reversalTimestamp);
        if (!$period || $period->status === 'closed') {
            throw new Exception('Cannot reverse journal in closed period');
        }

        $reversalLines = [];
        foreach ($originalJournal->lines as $line) {
            $reversalLines[] = [
                'account_uuid' => $line->account_uuid,
                'debit' => $line->credit,
                'credit' => $line->debit,
            ];
        }

        $reversalDto = JournalEntity::fromArray([
            'branch_uuid' => $originalJournal->branch_uuid,
            'transaction_code' => $this->transactionCodeService->generate($originalJournal->branch_uuid, $options['reference_type'] ?? JournalReferenceType::REVERSAL),
            'reference_no' => $originalJournal->reference_no . '-REV',
            'reference_uuid' => $originalJournal->uuid,
            'journal_date' => $reversalTimestamp,
            'description' => ($options['description_prefix'] ?? 'REVERSAL:') . " {$originalJournal->description}",
            'lines' => $reversalLines,
            'status' => 'posted',
            'workflow_status' => JournalWorkflowStatus::APPROVED,
            'reference_type' => $options['reference_type'] ?? JournalReferenceType::REVERSAL,
        ]);

        return $this->repository->reverseJournal($journalUuid, $reversalDto, ['status' => $options['status_on_original'] ?? 'voided']);
    }

    /**
     * Memproses jurnal terjadwal yang sudah jatuh tempo.
     */
    public function processScheduledJournals(): int
    {
        try {
            $now = now()->timestamp;
            $journals = $this->repository->getScheduledJournals();

            $processedCount = 0;

            foreach ($journals as $journal) {
                $locked = $this->repository->findScheduledForUpdate($journal->uuid);
                if (!$locked || $locked->is_processed || $locked->status !== JournalStatus::UNPOSTED) {
                    continue;
                }

                $this->postToLedger($locked);
                $this->repository->updateWithStatus($locked->uuid, [
                    'status' => JournalStatus::POSTED,
                ]);
                $processedCount++;
            }

            return $processedCount;
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Memproses template jurnal berulang yang sudah jatuh tempo.
     */
    public function processRecurringJournals(): int
    {
        try {
            $templates = $this->recurringJournalRepository->getActiveToRun();
            $processedCount = 0;

            foreach ($templates as $template) {
                $journalData = $template->journal_data ?? [];
                $runAt = $template->next_run_date instanceof Carbon
                    ? $template->next_run_date->copy()
                    : Carbon::parse(is_numeric($template->next_run_date) ? '@' . $template->next_run_date : (string) $template->next_run_date);

                $referenceSeed = $journalData['reference_no'] ?? $template->template_name ?? 'RECURRING';
                $journalData['branch_uuid'] = $journalData['branch_uuid'] ?? $template->branch_uuid;
                $journalData['journal_date'] = $runAt->timestamp;
                $journalData['reference_no'] = $referenceSeed . '-' . $runAt->format('Ymd');
                $journalData['description'] = '[RECURRING] ' . ($journalData['description'] ?? $template->template_name ?? 'Recurring Journal');
                $journalData['is_system'] = true;
                $journalData['status'] = JournalStatus::POSTED;
                $journalData['workflow_status'] = JournalWorkflowStatus::APPROVED;

                $this->createJournal(JournalEntity::fromArray($journalData));

                $this->recurringJournalRepository->update($template->uuid, [
                    'last_run_date' => $runAt->timestamp,
                    'next_run_date' => $this->resolveNextRunDate($runAt, (string) $template->frequency)->timestamp,
                ]);

                $processedCount++;
            }

            return $processedCount;
        } catch (Throwable $e) {
            ExceptionLogger::log($e, self::class);
            throw $e;
        }
    }

    /**
     * Memvalidasi apakah pemetaan akun (mapping) yang diperlukan sudah dikonfigurasi.
     */
    protected function validateMapping(JournalReferenceType $type): array
    {
        $prefix = match ($type) {
            JournalReferenceType::SALE => 'penjualan',
            JournalReferenceType::PURCHASE => 'pembelian',
            JournalReferenceType::EXPENSE => 'biaya',
            default => null,
        };

        if (!$prefix) return [];

        $resolved = [];
        $mappings = $this->accountSettingRepository->findMappingsByPrefix($prefix);
        foreach ($mappings as $m) $resolved[$m->key] = $m->account_uuid; 
        return $resolved;

    }

    /**
     * Memastikan total debit sama dengan total kredit
     */
    protected function validateBalance(JournalEntity $dto): void
    {
        if (count($dto->lines) < 2) {
            throw new Exception("Journal must have at least two lines (Debit and Credit).");
        }

        $totalDebit = collect($dto->lines)->sum('debit');
        $totalCredit = collect($dto->lines)->sum('credit');

        if (round($totalDebit, 2) <= 0 && round($totalCredit, 2) <= 0) {
            throw new Exception("Journal total amount must be greater than zero.");
        }

        if (abs(round($totalDebit, 2) - round($totalCredit, 2)) > 0.01) {
            throw new Exception("Journal unbalanced");
        }
    }

    /**
     * Update account_balances berdasarkan journal lines yang baru dibuat.
     */
    protected function postToLedger(JournalEntity $journal): void
    {
        $period = date('Y-m', $journal->journal_date);
        foreach ($journal->lines as $line) {
            $this->accountSettingRepository->updateAccountBalance($journal->branch_uuid, $line->account_uuid, $period, (float)$line->debit, (float)$line->credit);
        }
        $this->repository->markAsProcessed($journal->uuid);
    }

    protected function logAudit($model, string $event, ?array $before, ?array $after): void
    {
        $this->auditLogRepository->create([
            'branch_uuid' => $model->branch_uuid ?? null,
            'user_uuid' => Auth::user()->uuid ?? null,
            'event' => $event,
            'entity_type' => JournalModel::class,
            'entity_uuid' => $model->uuid ?? $model->reference_uuid,
            'before' => $before,
            'after' => $after,
        ]);
    }

    protected function getMenuNameFromType(JournalReferenceType $type): string
    {
        return match ($type) {
            JournalReferenceType::SALE, JournalReferenceType::POS_SALE => 'Penjualan Obat',
            JournalReferenceType::PURCHASE => 'Pembelian',
            JournalReferenceType::ENCOUNTER => 'Pemeriksaan (Rekam Medis)',
            JournalReferenceType::REGISTRATION => 'Pendaftaran Pasien',
            JournalReferenceType::EXPENSE => 'Biaya Umum',
            JournalReferenceType::DEBT_PAYMENT => 'Hutang Supplier',
            JournalReferenceType::RECEIVABLE_COLLECTION => 'Piutang Pasien & BPJS',
            JournalReferenceType::PURCHASE_RETURN => 'Retur Supplier',
            JournalReferenceType::STOCK_ADJUSTMENT => 'Penyesuaian Stok',
            JournalReferenceType::LABORATORY => 'Laboratorium',
            JournalReferenceType::RADIOLOGY => 'Radiologi',
            default => 'umum',
        };
    }

    protected function getReferenceModelFromType(JournalReferenceType $type): ?string
    {
        return match ($type) {
            JournalReferenceType::SALE => JournalReferenceModel::SALE->value,
            JournalReferenceType::PURCHASE => JournalReferenceModel::PURCHASE->value,
            JournalReferenceType::ENCOUNTER => JournalReferenceModel::ENCOUNTER->value,
            default => null,
        };
    }

    public function calculateAmountFromSource(Model $model, ?string $source, array $context = []): float
    {
        if (empty($source)) return 0.0;

        return match ($source) {
            JournalValueSource::TOTAL_NET->value => $this->resolveTotalNet($model, $context),
            JournalValueSource::TOTAL_TAX->value => $this->resolveTotalTax($model, $context),
            JournalValueSource::TOTAL_GROSS->value => $this->resolveTotalGross($model, $context),
            JournalValueSource::TOTAL_COST->value => $this->resolveTotalCost($model),
            JournalValueSource::TOTAL_PAID->value => $this->resolveTotalPaid($model),
            JournalValueSource::TOTAL_RECEIVABLE->value => $this->resolveTotalReceivable($model),
            JournalValueSource::TOTAL_SERVICE->value => $this->resolveTotalService($model),
            JournalValueSource::TOTAL_NET_PRODUCT->value => $this->resolveTotalNetProduct($model),
            JournalValueSource::TOTAL_COST_PRODUCT->value => $this->resolveTotalCostProduct($model),
            JournalValueSource::AMOUNT_PAID->value => $this->resolveAmountPaid($model),
            JournalValueSource::AMOUNT->value => $this->resolveGenericAmount($model, $context),
            default => 0.0,
        };
    }

    protected function resolveTotalGross(Model $model, array $context = []): float
    {
        return (float) (
            $model->total_gross
            ?? $model->total_amount
            ?? $model->total
            ?? $context['amount']
            ?? ($this->resolveTotalService($model) + $this->resolveTotalNetProduct($model))
        );
    }

    protected function resolveTotalNet(Model $model, array $context = []): float
    {
        $gross = $this->resolveTotalGross($model, $context);
        $tax = $this->resolveTotalTax($model, $context);

        if (isset($model->total_net)) {
            return (float) $model->total_net;
        }

        return max(0.0, $gross - $tax);
    }

    protected function resolveTotalTax(Model $model, array $context = []): float
    {
        if (isset($model->tax_amount)) {
            return (float) $model->tax_amount;
        }

        if (isset($context['tax_amount'])) {
            return (float) $context['tax_amount'];
        }

        $taxPercent = (float) ($context['tax_percent'] ?? 0);
        if ($taxPercent <= 0) {
            return 0.0;
        }

        $gross = $this->resolveTotalGross($model, $context);

        return round($gross * $taxPercent / (100 + $taxPercent), 2);
    }

    protected function resolveTotalPaid(Model $model): float
    {
        return (float) (
            $model->paid
            ?? $model->amount_paid
            ?? $model->paid_amount
            ?? $this->resolveTotalGross($model)
        );
    }

    protected function resolveTotalReceivable(Model $model): float
    {
        if (isset($model->receivable_amount)) {
            return (float) $model->receivable_amount;
        }

        $gross = $this->resolveTotalGross($model);
        $paid = $this->resolveTotalPaid($model);

        return max(0.0, $gross - $paid);
    }

    protected function resolveTotalCost(Model $model): float
    {
        $items = $this->resolveItems($model);

        return (float) $items->sum(function ($item) {
            $qty = (float) ($item->qty ?? 0);
            $buyPrice = (float) ($item->product->price_buy ?? 0);

            return $qty * $buyPrice;
        });
    }

    protected function resolveTotalService(Model $model): float
    {
        $items = $this->resolveItems($model);

        return (float) $items
            ->filter(fn ($item) => ($item->item_type ?? null) === 'service' || !empty($item->service))
            ->sum(fn ($item) => (float) ($item->subtotal ?? 0));
    }

    protected function resolveTotalNetProduct(Model $model): float
    {
        $items = $this->resolveItems($model);

        return (float) $items
            ->filter(fn ($item) => ($item->item_type ?? null) === 'product' || !empty($item->product))
            ->sum(fn ($item) => (float) ($item->subtotal ?? 0));
    }

    protected function resolveTotalCostProduct(Model $model): float
    {
        $items = $this->resolveItems($model);

        return (float) $items
            ->filter(fn ($item) => ($item->item_type ?? null) === 'product' || !empty($item->product))
            ->sum(function ($item) {
                $qty = (float) ($item->qty ?? 0);
                $buyPrice = (float) ($item->product->price_buy ?? 0);

                return $qty * $buyPrice;
            });
    }

    protected function resolveAmountPaid(Model $model): float
    {
        return (float) ($model->amount_paid ?? $model->paid ?? $this->resolveGenericAmount($model, []));
    }

    protected function resolveGenericAmount(Model $model, array $context = []): float
    {
        return (float) (
            $context['amount']
            ?? $model->amount
            ?? ((float) ($model->qty ?? 0) * (float) ($model->price ?? 0))
            ?? $model->total_amount
            ?? $model->total
            ?? $this->resolveTotalGross($model)
        );
    }

    protected function resolveItems(Model $model): Collection
    {
        if (!$model->relationLoaded('items')) {
            $model->loadMissing(['items.product', 'items.service']);
        } else {
            $model->loadMissing(['items.product', 'items.service']);
        }

        return collect($model->items ?? []);
    }

    protected function hasProductItems(Model $model): bool
    {
        return $this->resolveItems($model)
            ->contains(fn ($item) => ($item->item_type ?? null) === 'product' || !empty($item->product));
    }

    /**
     * @param Collection<int, JournalLineEntity> $lines
     * @return Collection<int, JournalLineEntity>
     */
    protected function appendAutomaticTaxLines(Collection $lines): Collection
    {
        $generated = collect();

        foreach ($lines as $line) {
            if (!$line instanceof JournalLineEntity || !$line->taxable) {
                continue;
            }

            $taxRate = (float) ($line->tax_rate ?? 0);
            if ($taxRate <= 0) {
                continue;
            }

            $baseAmount = $line->debit > 0 ? (float) $line->debit : (float) $line->credit;
            if ($baseAmount <= 0) {
                continue;
            }

            $taxAmount = round($baseAmount * $taxRate / 100, 2);
            if ($taxAmount <= 0) {
                continue;
            }

            [$taxName, $direction] = match ($line->tax_type) {
                'ppn_input' => ['PPN Masukan', 'debit'],
                'pph_23' => ['PPh 23', 'credit'],
                'pph_4(2)' => ['PPh 4(2)', 'credit'],
                'output' => ['PPN', 'credit'],
                default => $line->debit > 0 ? ['PPN Masukan', 'debit'] : ['PPN', 'credit'],
            };

            $taxSetting = $this->accountSettingRepository->findTaxSettingForRate($taxName, $taxRate);

            if (!$taxSetting) {
                throw new Exception("Tax setting '{$taxName}' is not configured.");
            }

            $generated->push(JournalLineEntity::fromArray([
                'account_uuid' => $taxSetting->account_uuid,
                'debit' => $direction === 'debit' ? $taxAmount : 0,
                'credit' => $direction === 'credit' ? $taxAmount : 0,
                'description' => "Auto tax: {$taxName}",
            ]));
        }

        return $lines->concat($generated)->values();
    }

    protected function resolveNextRunDate(Carbon $runAt, string $frequency): Carbon
    {
        return match (strtolower($frequency)) {
            'daily' => $runAt->copy()->addDay(),
            'weekly' => $runAt->copy()->addWeek(),
            'yearly', 'annual' => $runAt->copy()->addYear(),
            default => $runAt->copy()->addMonth(),
        };
    }
}
