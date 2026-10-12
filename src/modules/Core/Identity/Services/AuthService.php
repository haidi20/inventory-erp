<?php

namespace App\Modules\Core\Identity\Services;

use App\Modules\Core\Identity\Entities\LoginEntity;
use App\Modules\Core\Identity\Repositories\AuthRepositoryInterface;
use Illuminate\Validation\ValidationException;

class AuthService
{
    private AuthRepositoryInterface $authRepository;

    public function __construct(AuthRepositoryInterface $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    /**
     * @param LoginEntity $entity
     * @return array
     * @throws ValidationException
     */
    public function login(LoginEntity $entity): array
    {
        $token = $this->authRepository->attemptLogin($entity);

        if (!$token) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang diberikan tidak cocok dengan data kami.'],
            ]);
        }

        return $this->respondWithToken($token);
    }

    public function logout(): void
    {
        $this->authRepository->logout();
    }

    public function refresh(): array
    {
        $token = $this->authRepository->refresh();
        if (!$token) {
            throw ValidationException::withMessages([
                'token' => ['Gagal memperbarui token.'],
            ]);
        }
        return $this->respondWithToken($token);
    }

    public function me(): array
    {
        $user = $this->authRepository->getUser();
        if (!$user) {
            throw ValidationException::withMessages([
                'token' => ['Token tidak valid atau pengguna tidak ditemukan.'],
            ]);
        }

        return $user->toArray();
    }

    private function respondWithToken(string $token): array
    {
        $user = $this->authRepository->getUser();
        return [
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => $this->authRepository->getTokenTTL() * 60,
            'user'         => $user,
            'landing_url'  => $user ? $this->resolveLandingUrl($user) : '/',
        ];
    }

    /**
     * Tentukan halaman landing setelah login berdasarkan prioritas role, lalu
     * fallback berdasarkan permission. Port dari BE: authService.resolveLandingURL.
     */
    public function resolveLandingUrl($user): string
    {
        $roleMap = [
            'admin'            => '/',
            'owner'            => '/',
            'manajer_keuangan' => '/laporan/arus-kas',
            'pendaftaran'      => '/bpjs/pendaftaran',
            'dokter'           => '/transaksi/tugas-dokter',
            'perawat'          => '/transaksi/pemeriksaan',
            'apoteker'         => '/stok/barang-dan-jasa',
            'kasir'            => '/transaksi/penjualan',
            'lab'              => '/master/patients',
        ];

        foreach ($roleMap as $role => $url) {
            if ($this->hasRole($user, $role) && $this->canAccessUrl($user, $url)) {
                return $url;
            }
        }

        $permissionFallback = [
            'himbauan.view'               => '/himbauan',
            'bpjs.pendaftaran.view'       => '/bpjs/pendaftaran',
            'bpjs.verifikasi.view'        => '/bpjs/verifikasi',
            'satusehat.sync.manage'       => '/satusehat/sync',
            'stok.daftar_nilai.view'      => '/stok/daftar-nilai',
            'stok.barang_jasa.view'       => '/stok/barang-dan-jasa',
            'transaksi.pemeriksaan.view'  => '/transaksi/pemeriksaan',
            'transaksi.tugas_dokter.view' => '/transaksi/tugas-dokter',
            'transaksi.penjualan.view'    => '/transaksi/penjualan',
            'transaksi.pembelian.view'    => '/transaksi/pembelian',
            'master.pasien.view'          => '/master/patients',
            'master.produk.view'          => '/master/products',
            'akuntansi.coa.view'          => '/accounting/coa',
            'laporan.arus_kas.view'       => '/laporan/arus-kas',
            'analitik.overview.view'      => '/analytics/overview',
            'settings.user.view'          => '/management/users',
            'settings.view'               => '/settings',
        ];

        foreach ($permissionFallback as $permission => $url) {
            if ($this->hasPermission($user, $permission) && $this->canAccessUrl($user, $url)) {
                return $url;
            }
        }

        return '/';
    }

    private function canAccessUrl($user, string $url): bool
    {
        $urlPermissionMap = [
            '/'                       => 'dashboard.view',
            '/himbauan'               => 'himbauan.view',
            '/bpjs/pendaftaran'       => 'bpjs.pendaftaran.view',
            '/bpjs/verifikasi'        => 'bpjs.verifikasi.view',
            '/satusehat/sync'         => 'satusehat.sync.manage',
            '/stok/daftar-nilai'      => 'stok.daftar_nilai.view',
            '/stok/barang-dan-jasa'   => 'stok.barang_jasa.view',
            '/transaksi/pemeriksaan'  => 'transaksi.pemeriksaan.view',
            '/transaksi/tugas-dokter' => 'transaksi.tugas_dokter.view',
            '/transaksi/penjualan'    => 'transaksi.penjualan.view',
            '/transaksi/pembelian'    => 'transaksi.pembelian.view',
            '/master/patients'        => 'master.pasien.view',
            '/master/products'        => 'master.produk.view',
            '/accounting/coa'         => 'akuntansi.coa.view',
            '/laporan/arus-kas'       => 'laporan.arus_kas.view',
            '/analytics/overview'     => 'analitik.overview.view',
            '/management/users'       => 'settings.user.view',
            '/settings'               => 'settings.view',
        ];

        $required = $urlPermissionMap[$url] ?? null;
        return $required !== null && $this->hasPermission($user, $required);
    }

    private function hasRole($user, string $role): bool
    {
        if (method_exists($user, 'hasRole')) {
            return (bool) $user->hasRole($role);
        }
        return false;
    }

    private function hasPermission($user, string $permission): bool
    {
        if (method_exists($user, 'hasPermissionTo')) {
            try {
                return (bool) $user->hasPermissionTo($permission);
            } catch (\Throwable $e) {
                return false;
            }
        }
        if (method_exists($user, 'can')) {
            return (bool) $user->can($permission);
        }
        return false;
    }
}
