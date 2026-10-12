<?php

namespace App\Shared\Traits;

use App\Shared\Scopes\CompanyScope;
use App\Shared\Scopes\BranchScope;
use App\Modules\Core\Organization\Models\Company;
use App\Modules\Core\Organization\Models\Branch;

/**
 * Trait untuk menerapkan full multi-org scoping (Company + Branch) pada model.
 * Kombinasi dari HasCompanyScope dan HasBranchScope.
 * 
 * Model yang pakai trait ini akan:
 * - Di-filter berdasarkan company_uuid user aktif (CompanyScope)
 * - Di-filter berdasarkan branch_uuid user aktif (BranchScope)
 * - Otomatis set company_uuid & branch_uuid saat creating
 * 
 * PENGUNAAN:
 *   class Purchase extends Model {
 *       use HasMultiOrgScope;
 *   }
 * 
 * CATATAN: Scope dijalankan dalam urutan Company -> Branch.
 * BranchScope mengasumsikan CompanyScope sudah berjalan.
 */
trait HasMultiOrgScope
{
    /**
     * Mendapatkan nama kolom company (default: company_uuid)
     */
    public function getCompanyColumn(): string
    {
        return property_exists($this, 'companyColumn') ? $this->companyColumn : 'company_uuid';
    }

    /**
     * Mendapatkan nama kolom branch (default: branch_uuid)
     */
    public function getBranchColumn(): string
    {
        return property_exists($this, 'branchColumn') ? $this->branchColumn : 'branch_uuid';
    }

    protected static function bootHasMultiOrgScope(): void
    {
        // Register scopes: CompanyScope harus sebelum BranchScope
        static::addGlobalScope(new CompanyScope);
        static::addGlobalScope(new BranchScope);

        // Auto-set company_uuid & branch_uuid saat creating
        static::creating(function ($model): void {
            $user = auth()->user();
            if (!$user) {
                return;
            }

            // Set company_uuid dari user aktif
            $companyColumn = $model->getCompanyColumn();
            if (empty($model->{$companyColumn})) {
                $model->{$companyColumn} = $user->active_company_uuid;
            }

            // Set branch_uuid dari user aktif
            $branchColumn = $model->getBranchColumn();
            if (empty($model->{$branchColumn})) {
                $model->{$branchColumn} = $user->active_branch_uuid;
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class, $this->getCompanyColumn(), 'uuid');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, $this->getBranchColumn(), 'uuid');
    }
}
