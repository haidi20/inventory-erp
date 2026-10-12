<?php

namespace App\Modules\Finance\Journal\Repositories;

use App\Modules\Finance\Journal\Entities\JournalEntity;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * JournalRepositoryInterface
 * 
 * Kontrak untuk repository yang mengelola Jurnal Akuntansi dan Buku Besar.
 */
interface JournalRepositoryInterface
{
    public function findById(string $uuid): ?JournalEntity;
    public function findByIdUnscoped(string $uuid): ?JournalEntity;
    public function findByIdForUpdate(string $uuid): ?JournalEntity;
    public function findByReference(string $branchUuid, string $refType, string $refUuid): ?JournalEntity;
    public function findByReferenceNo(string $refNo): ?JournalEntity;
    public function findJournalByUuid(string $uuid): ?JournalEntity;
    public function getReport(array $filters = []): Collection;
    public function getScheduledJournals(): array;
    public function findScheduledForUpdate(string $uuid): ?JournalEntity;
    public function paginateForContext(string $branchUuid, string $companyUuid, int $perPage = 50): LengthAwarePaginator;
    public function findForContext(string $uuid, string $branchUuid, string $companyUuid): ?JournalEntity;
    public function referenceNoExists(string $referenceNo, string $branchUuid, string $companyUuid, ?string $ignoreUuid = null): bool;
    public function accountExists(string $accountUuid, string $branchUuid, string $companyUuid): bool;
    
    public function createWithLines(JournalEntity $dto, ?string $actorUuid): JournalEntity;
    public function createManyWithLines(array $dtos, string $actorUuid): array;
    public function upsertJournal(JournalEntity $dto): JournalEntity;
    
    public function updateWithStatus(string $uuid, array $data): JournalEntity;
    public function deleteWithTransaction(string $uuid): bool;
    public function reverseJournal(string $uuid, JournalEntity $reversalDto, array $updateOriginalData): JournalEntity;
    
    public function bulkCreateLedgerEntries(array $entries): void;
    public function getJournalLines(string $journalUuid): array;
    public function updateStatus(string $uuid, string $status, string $actorUuid): void;
    public function delete(string $uuid, string $actorUuid): void;
    public function getNextSequence(string $branchUuid, string $companyUuid, string $prefix, string $period, string $actorUuid): int;
    public function markAsProcessed(string $uuid): void;
}
