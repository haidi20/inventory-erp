<?php

namespace App\Modules\Operational\Sales\Services;

use App\Modules\Operational\Sales\Entities\ExaminationEntity;
use App\Modules\Operational\Sales\Entities\SaleFilterEntity;
use App\Modules\Operational\Sales\Entities\SaleTransactionEntity;
use App\Modules\Operational\Sales\Repositories\TransactionRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    public function __construct(private TransactionRepository $transactionRepository)
    {
    }

    public function purchaseList(mixed $filter = null): array
    {
        return $this->transactionRepository->getPurchasePageData($filter);
    }

    public function payableList(mixed $filter = null): array
    {
        return $this->transactionRepository->getPayablePageData($filter);
    }

    public function examinationList(mixed $filter = null): array
    {
        return $this->transactionRepository->getExaminationPageData($filter);
    }

    public function examinationForm(string $registrationUuid): array
    {
        return $this->transactionRepository->getExaminationFormData($registrationUuid);
    }

    public function storeExamination(ExaminationEntity $entity): void
    {
        $this->transactionRepository->storeExamination($entity);
    }

    public function salesList(mixed $filter = null): array
    {
        return $this->transactionRepository->getSalesPageData();
    }

    public function salesForm(mixed $filter = null): array
    {
        return $this->transactionRepository->getSalesFormData($filter);
    }

    public function storeSale(SaleTransactionEntity $saleTransactionEntity): string
    {
        Validator::make($saleTransactionEntity->toArray(), [
            'patient_uuid'           => ['nullable', 'string', 'uuid'],
            'cashier_uuid'           => ['nullable', 'string', 'uuid'],
            'paid'                   => ['required', 'numeric', 'min:0'],
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.product_uuid'   => ['required', 'string', 'distinct', 'uuid'],
            'items.*.qty'            => ['required', 'integer', 'min:1'],
        ])->validate();

        if ($saleTransactionEntity->patient_uuid && !$this->transactionRepository->patientExists($saleTransactionEntity->patient_uuid)) {
            throw ValidationException::withMessages(['patient_uuid' => ['The selected patient uuid is invalid.']]);
        }

        if ($saleTransactionEntity->cashier_uuid && !$this->transactionRepository->employeeExists($saleTransactionEntity->cashier_uuid)) {
            throw ValidationException::withMessages(['cashier_uuid' => ['The selected cashier uuid is invalid.']]);
        }

        $productUuids = array_map(fn ($item) => $item->product_uuid, $saleTransactionEntity->items);
        if (!$this->transactionRepository->productsExist($productUuids)) {
            throw ValidationException::withMessages(['items.*.product_uuid' => ['The selected product uuid is invalid.']]);
        }

        return $this->transactionRepository->createSale($saleTransactionEntity);
    }

    public function saleDetail(mixed $filter = null): array
    {
        $uuid = is_string($filter)
            ? $filter
            : (is_array($filter) ? ($filter['uuid'] ?? '') : ($filter instanceof SaleFilterEntity ? ($filter->uuid ?? '') : ''));

        return [
            'sale' => $this->transactionRepository->getSaleDetail($uuid),
        ];
    }

    public function receivableList(mixed $filter = null): array
    {
        $search = is_array($filter) ? ($filter['search'] ?? null) : null;
        $status = is_array($filter) ? ($filter['status'] ?? null) : null;

        return $this->transactionRepository->getReceivablePageData($search, $status);
    }
}

