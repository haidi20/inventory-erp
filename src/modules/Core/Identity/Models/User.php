<?php

namespace App\Modules\Core\Identity\Models;

use App\Modules\Core\Organization\Models\Branch;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Operational\Master\Models\Employee;
use App\Shared\Support\CompanyContext;
use App\Shared\Support\LegacyScopeFlusher;
use App\Shared\Traits\Auditable;
use App\Shared\Traits\Blameable;
use App\Shared\Traits\HasSoftDeleteUnique;
use Database\Factories\Master\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

/**
 * Model User
 *
 * Data akun pengguna sistem yang menyimpan kredensial login dan profil dasar akses.
 *
 * Multi-org support:
 * - Pivot user_companies: 1 user bisa akses multiple companies
 * - Pivot user_branches: 1 user bisa akses multiple branches (per company)
 * - Helper method allCompanies(), allBranches(), activeCompany(), activeBranch()
 */
class User extends Authenticatable implements AuditableContract, JWTSubject
{
    use Auditable, Blameable, HasFactory, HasRoles, HasSoftDeleteUnique, HasUuids, Notifiable;

    protected $dateFormat = 'U';

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'uuid',
        'id',
        'name',
        'email',
        'username',
        'password',
        'guard_name',
        'is_active',
        'created_by',
        'updated_by',
        // Legacy: ditangkap mutator, diterjemahkan ke pivot (tidak tersimpan
        // sebagai kolom users).
        'branch_uuid',
        'company_uuid',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'user_uuid', 'uuid');
    }

    // --- Multi-org pivot relations ---

    /**
     * Semua companies yang bisa diakses user ini.
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(
            Company::class,
            'user_companies',
            'user_uuid',
            'company_uuid'
        )->withPivot('is_default')->withTimestamps(false);
    }

    /**
     * Semua branches yang bisa diakses user ini (per company).
     */
    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(
            Branch::class,
            'user_branches',
            'user_uuid',
            'branch_uuid'
        )->withPivot('company_uuid', 'is_default')->withTimestamps(false);
    }

    /**
     * Cek apakah user ini adalah member dari company tertentu.
     */
    public function belongsToCompany(string $companyUuid): bool
    {
        return $this->companies()->where('companies.uuid', $companyUuid)->exists();
    }

    /**
     * Cek apakah user ini adalah member dari branch tertentu.
     */
    public function belongsToBranch(string $branchUuid): bool
    {
        return $this->branches()->where('branches.uuid', $branchUuid)->exists();
    }

    // --- Active context helpers ---

    /**
     * Ambil UUID company yang sedang aktif.
     * Prioritas: request context (middleware ResolveCompanyContext, tervalidasi) -> pivot -> legacy.
     * Header TIDAK dibaca di sini -- validasi header ada di middleware.
     */
    public function getActiveCompanyUuidAttribute(): ?string
    {
        $context = request()->attributes->get('company_context');
        if ($context instanceof CompanyContext) {
            return $context->companyUuid;
        }

        if ($this->activeCompanyResolved) {
            return $this->resolvedActiveCompanyUuid;
        }

        // Non-HTTP (artisan/queue): pivot default -> kolom legacy
        $this->resolvedActiveCompanyUuid = UserCompany::where('user_uuid', $this->uuid)
            ->where('is_default', true)
            ->value('company_uuid');
        $this->activeCompanyResolved = true;

        return $this->resolvedActiveCompanyUuid;
    }

    /**
     * Ambil UUID branch yang sedang aktif.
     * Prioritas: request context (middleware tervalidasi) -> pivot per company -> legacy.
     * Header TIDAK dibaca di sini -- validasi header ada di middleware.
     */
    public function getActiveBranchUuidAttribute(): ?string
    {
        $context = request()->attributes->get('company_context');
        if ($context instanceof CompanyContext) {
            return $context->branchUuid;
        }

        if ($this->activeBranchResolved) {
            return $this->resolvedActiveBranchUuid;
        }

        $companyUuid = $this->active_company_uuid;

        if ($companyUuid === null) {
            // company_uuid di pivot NOT NULL, jadi branch tanpa company mustahil ada.
            $this->resolvedActiveBranchUuid = null;
            $this->activeBranchResolved = true;

            return null;
        }

        // Non-HTTP (artisan/queue): pivot per company -> kolom legacy
        $this->resolvedActiveBranchUuid = UserBranch::where('user_uuid', $this->uuid)
            ->where('company_uuid', $companyUuid)
            ->where('is_default', true)
            ->value('branch_uuid');
        $this->activeBranchResolved = true;

        return $this->resolvedActiveBranchUuid;
    }

    /**
     * Semua UUID companies yang bisa diakses user ini.
     */
    public function allCompanyUuids(): array
    {
        return UserCompany::where('user_uuid', $this->uuid)
            ->pluck('company_uuid')
            ->toArray();
    }

    /**
     * Semua UUID branches yang bisa diakses user ini (opsional filter per company).
     */
    public function allBranchUuids(?string $companyUuid = null): array
    {
        $query = UserBranch::where('user_uuid', $this->uuid);
        if ($companyUuid) {
            $query->where('company_uuid', $companyUuid);
        }

        return $query->pluck('branch_uuid')->toArray();
    }

    /**
     * Cek apakah user adalah superadmin (bypass semua scope).
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('superadmin');
    }

    // --- JWT implementation ---

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'active_company_uuid' => $this->active_company_uuid,
            'active_branch_uuid' => $this->active_branch_uuid,
        ];
    }

    public function getBranchIdAttribute(): ?string
    {
        return $this->branch_uuid;
    }

    // --- Kompatibilitas kolom legacy branch_uuid/company_uuid ---

    /** @var array{branch_uuid?: string, company_uuid?: string} */
    private array $pendingLegacyScope = [];

    /** Memo query pivot aktif (per instance) agar global scope tidak mengulang query. */
    private ?string $resolvedActiveCompanyUuid = null;

    private bool $activeCompanyResolved = false;

    private ?string $resolvedActiveBranchUuid = null;

    private bool $activeBranchResolved = false;

    /**
     * Baca kompatibilitas: kolom legacy sudah di-drop, bacaan proxy ke
     * context aktif (pivot user_companies/user_branches).
     */
    public function getBranchUuidAttribute(): ?string
    {
        return $this->active_branch_uuid;
    }

    public function getCompanyUuidAttribute(): ?string
    {
        return $this->active_company_uuid;
    }

    /**
     * Tulis kompatibilitas: assignment branch_uuid/company_uuid tidak lagi
     * menyentuh tabel users, tapi mengantri pivot (is_default=true) yang
     * di-flush sekali pada event saved.
     */
    public function setBranchUuidAttribute(mixed $value): void
    {
        $this->queueLegacyScope('branch_uuid', $value);
    }

    public function setCompanyUuidAttribute(mixed $value): void
    {
        $this->queueLegacyScope('company_uuid', $value);
    }

    private function queueLegacyScope(string $key, mixed $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $this->pendingLegacyScope[$key] = (string) $value;
    }

    /**
     * Terjemahkan scope legacy yang tertunda menjadi pivot.
     */
    public function flushLegacyScope(): void
    {
        if ($this->pendingLegacyScope === []) {
            return;
        }

        app(LegacyScopeFlusher::class)->flush(
            $this,
            $this->pendingLegacyScope['branch_uuid'] ?? null,
            $this->pendingLegacyScope['company_uuid'] ?? null
        );

        $this->pendingLegacyScope = [];
        $this->resetActiveContextCache();
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saved(function (User $user): void {
            $user->flushLegacyScope();
        });
    }

    private function resetActiveContextCache(): void
    {
        $this->resolvedActiveCompanyUuid = null;
        $this->activeCompanyResolved = false;
        $this->resolvedActiveBranchUuid = null;
        $this->activeBranchResolved = false;
    }

    protected static function newFactory(): Factory
    {
        return UserFactory::new();
    }

    public function guardName(): string
    {
        return config('auth.defaults.guard', 'web');
    }
}
