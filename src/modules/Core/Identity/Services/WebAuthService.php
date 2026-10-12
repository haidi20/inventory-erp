<?php

namespace App\Modules\Core\Identity\Services;

use App\Modules\Core\Identity\Repositories\OwnerRegistrationRepositoryInterface;
use App\Modules\Core\Identity\Repositories\WebAuthRepositoryInterface;
use App\Modules\Core\Identity\Entities\UserEntity;
use App\Modules\Core\Organization\Entities\BranchEntity;
use App\Modules\Core\Organization\Entities\CompanyEntity;
use App\Modules\Core\Organization\Services\BranchService;
use App\Modules\Core\Organization\Services\CompanyService;
use Illuminate\Support\Facades\DB;

class WebAuthService
{
    public function __construct(
        private WebAuthRepositoryInterface $webAuthRepository,
        private OwnerRegistrationRepositoryInterface $registrationRepository,
        private CompanyService $companyService,
        private BranchService $branchService,
        private UserService $userService
    ) {}

    public function getFirstActiveCompany(): ?CompanyEntity
    {
        return $this->companyService->getFirstActive();
    }

    public function getTimezones(): array
    {
        return \DateTimeZone::listIdentifiers();
    }

    public function findUser(string $identifier): ?UserEntity
    {
        if (trim($identifier) === '') {
            return null;
        }

        return $this->userService->findByLoginIdentifier(trim($identifier));
    }

    public function validateCredentials(UserEntity $user, string $password): bool
    {
        return $this->webAuthRepository->validate($user->email, $password);
    }

    public function attemptLogin(UserEntity $user, string $password, bool $remember): ?UserEntity
    {
        return $this->webAuthRepository->attempt($user->email, $password, $remember);
    }

    public function register(array $data): UserEntity
    {
        return DB::transaction(function () use ($data) {
            $role = $this->registrationRepository->ensureOwnerRole();

            $company = $this->companyService->store(new CompanyEntity(
                name: $data['company_name'],
                timezone: $data['timezone'],
            ));

            $branch = $this->branchService->save(new BranchEntity(
                company_uuid: $company->uuid,
                name: $company->name,
                short_name: $this->registrationRepository->generateUniqueBranchShortName($company->name),
                description: 'Cabang utama',
            ));

            $user = $this->userService->save(new UserEntity(
                name: $data['name'],
                email: $data['email'],
                username: $data['username'],
                password: $data['password'],
                branch_uuid: $branch->uuid,
                company_uuid: $company->uuid,
                role_ids: [$role->uuid],
            ));

            $this->registrationRepository->assignDefaultCompany($user->uuid, $company->uuid);
            $this->registrationRepository->assignDefaultBranch($user->uuid, $company->uuid, $branch->uuid);

            return $user;
        });
    }

    public function loginRegisteredUser(string $userUuid): ?UserEntity
    {
        return $this->webAuthRepository->loginUsingUuid($userUuid);
    }

    public function issueApiToken(UserEntity $user): ?string
    {
        return $this->webAuthRepository->issueApiToken($user);
    }

    public function logout(): void
    {
        $this->webAuthRepository->logout();
    }

    public function updatePassword(string $userUuid, string $password): void
    {
        $this->userService->updatePassword($userUuid, $password);
    }

    public function resolveLandingUrl($user): string
    {
        $roleMap = [
            'superadmin' => '/',
            'admin'      => '/',
            'owner'      => '/',
            'manajer_keuangan' => '/reports/cash-flow',
            'pendaftaran'      => '/bpjs/registration',
            'dokter'          => '/transactions/doctor-tasks',
            'perawat'         => '/transactions/examinations',
            'apoteker'        => '/stock/items-and-services',
            'kasir'           => '/transactions/sales',
            'lab'             => '/master/patients',
        ];

        foreach ($roleMap as $role => $url) {
            if ($user->hasRole($role) && $this->canAccessUrl($user, $url)) {
                return $url;
            }
        }

        $fallbackMap = [
            'himbauan.view'               => '/announcements',
            'bpjs.pendaftaran.view'       => '/bpjs/registration',
            'bpjs.verifikasi.view'        => '/bpjs/verification',
            'satusehat.sync.manage'       => '/satusehat/sync',
            'stok.daftar_nilai.view'      => '/stock/value-list',
            'stok.barang_jasa.view'       => '/stock/items-and-services',
            'transaksi.pemeriksaan.view'  => '/transactions/examinations',
            'transaksi.tugas_dokter.view' => '/transactions/doctor-tasks',
            'transaksi.penjualan.view'    => '/transactions/sales',
            'transaksi.pembelian.view'    => '/transactions/purchases',
            'master.pasien.view'          => '/master/patients',
            'master.produk.view'          => '/master/products',
            'akuntansi.coa.view'          => '/accounting/coa',
            'laporan.arus_kas.view'       => '/reports/cash-flow',
            'analitik.overview.view'      => '/analytics',
            'settings.user.view'          => '/settings/users',
            'settings.view'               => '/settings',
        ];

        foreach ($fallbackMap as $permission => $url) {
            if ($user->can($permission) && $this->canAccessUrl($user, $url)) {
                return $url;
            }
        }

        return '/';
    }

    private function canAccessUrl($user, string $url): bool
    {
        return match ($url) {
            '/'                        => $user->can('dashboard.view'),
            '/announcements'           => $user->can('himbauan.view'),
            '/bpjs/registration'       => $user->can('bpjs.pendaftaran.view'),
            '/bpjs/verification'       => $user->can('bpjs.verifikasi.view'),
            '/satusehat/sync'          => $user->can('satusehat.sync.manage'),
            '/stock/value-list'        => $user->can('stok.daftar_nilai.view'),
            '/stock/items-and-services'=> $user->can('stok.barang_jasa.view'),
            '/transactions/examinations'  => $user->can('transaksi.pemeriksaan.view'),
            '/transactions/doctor-tasks'  => $user->can('transaksi.tugas_dokter.view'),
            '/transactions/sales'         => $user->can('transaksi.penjualan.view'),
            '/transactions/purchases'     => $user->can('transaksi.pembelian.view'),
            '/master/patients'            => $user->can('master.pasien.view'),
            '/master/products'            => $user->can('master.produk.view'),
            '/accounting/coa'             => $user->can('akuntansi.coa.view'),
            '/reports/cash-flow'          => $user->can('laporan.arus_kas.view'),
            '/analytics'                  => $user->can('analitik.overview.view'),
            '/settings/users'             => $user->can('settings.user.view'),
            '/settings'                   => $user->can('settings.view'),
            default                       => false,
        };
    }
}
