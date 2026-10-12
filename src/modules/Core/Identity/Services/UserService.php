<?php

namespace App\Modules\Core\Identity\Services;

use App\Http\Resources\ApiDataResource;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use App\Shared\Support\ExceptionLogger;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Modules\Core\Identity\Exports\ManagementUsersExport;
use Illuminate\Http\UploadedFile;
use App\Modules\Core\Identity\Entities\UserEntity;
use App\Modules\Core\Identity\Exports\UserImportTemplateExport;
use App\Modules\Core\Identity\Imports\UserImport;
use App\Modules\Core\Identity\Repositories\UserRepositoryInterface;
use App\Http\Resources\Settings\UserResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * Get paginated users for listing.
     */
    public function getPaginated(UserEntity $entity): array
    {
        $paginator = $this->userRepository->paginate($entity);
        $resource = UserResource::collection($paginator);
        
        return [
            'users' => [
                'data' => $resource->resolve(), // This returns the flat array of transformed users
                'links' => $paginator->linkCollection()->toArray(),
                'total' => $paginator->total(),
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'meta' => [
                    'total' => $paginator->total(),
                    'current_page' => $paginator->currentPage(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                    'per_page' => $paginator->perPage(),
                    'last_page' => $paginator->lastPage(),
                ]
            ],
            'roles' => $this->userRepository->getAllRoles(),
            'summary' => $this->userRepository->getSummaryData($entity->company_uuid),
            'filters' => [
                'search' => $entity->search,
                'search_column' => $entity->search_column,
                'role' => $entity->role_filter,
                'sort_by' => $entity->sort_by,
                'sort_direction' => $entity->sort_direction,
                'page' => $entity->page,
                'per_page' => $entity->per_page,
            ]
        ];
    }

    /**
     * Create or Update user.
     */
    public function save(UserEntity $entity): UserEntity
    {
        $this->validate($entity);
        return $this->userRepository->save($entity);
    }

    /**
     * Delete user.
     */
    public function findByLoginIdentifier(string $identifier): ?UserEntity
    {
        return $this->userRepository->findByLoginIdentifier($identifier);
    }

    public function uuidExists(string $uuid): bool
    {
        return $this->userRepository->uuidExists($uuid);
    }

    public function updatePassword(string $uuid, string $password): void
    {
        $this->userRepository->updatePassword($uuid, $password);
    }

    public function delete(UserEntity $entity): void
    {
        $this->userRepository->delete($entity);
    }

    /**
     * Export users to Excel.
     */
    public function export(UserEntity $entity)
    {
        $users = $this->userRepository->getForExport($entity);
        return (new ManagementUsersExport($users))->download('management-users-' . now()->format('Y-m-d') . '.xlsx');
    }

    /**
     * Import users from Excel.
     */
    public function import(UploadedFile $file, string $actorUuid, string $branchUuid, string $companyUuid): void
    {
        try {
            Excel::import(new UserImport($actorUuid, $branchUuid, $companyUuid), $file);
        } catch (\Throwable $e) {
            ExceptionLogger::log($e, self::class);
            \Illuminate\Support\Facades\Log::error('User Import Error: ' . $e->getMessage());
            
            throw \Illuminate\Validation\ValidationException::withMessages([
                'file' => ['Format file tidak valid atau rusak. Pastikan mengunggah file Excel (.xlsx) yang benar.'],
            ]);
        }
    }

    /**
     * Download import template for users.
     */
    public function downloadImportTemplate()
    {
        return Excel::download(
            new UserImportTemplateExport(),
            'template-import-user.xlsx'
        );
    }

    /**
     * Validate user entity.
     */
    private function validate(UserEntity $entity): void
    {
        Validator::make($entity->toArray(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'password'   => [$entity->uuid ? 'nullable' : 'required', 'string', 'min:6'],
            'role_ids'   => ['required', 'array', 'min:1'],
        ])->validate();

        $errors = [];

        if ($entity->email && $this->userRepository->emailExistsInCompany($entity->email, $entity->company_uuid, $entity->uuid)) {
            $errors['email'] = ['The email has already been taken.'];
        }

        if (!$this->userRepository->roleUuidsExist($entity->role_ids ?? [])) {
            $errors['role_ids'] = ['The selected role ids are invalid.'];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
