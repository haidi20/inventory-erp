<?php

namespace App\Shared\Traits;

use App\Shared\Scopes\BranchScope;
use App\Modules\Core\Organization\Models\Branch;

/**
 * Trait untuk menerapkan multi-branch scoping pada model.
 * Secara otomatis: 1) filter query by branch column, 2) set branch column saat creating.
 * 
 * NOTE: Trait ini hanya menangani branch scope. Untuk full multi-org (company + branch),
 * gunakan trait HasMultiOrgScope sebagai gantinya.
 */
trait HasBranchScope
{
    /**
     * Mendapatkan nama kolom branch (default: branch_uuid)
     */
    public function getBranchColumn(): string
    {
        return property_exists($this, 'branchColumn') ? $this->branchColumn : 'branch_uuid';
    }

    protected static function bootHasBranchScope(): void
    {
        static::addGlobalScope(new BranchScope);

        static::creating(function ($model): void {
            $column = $model->getBranchColumn();
            if (empty($model->{$column}) && auth()->check()) {
                // Gunakan active_branch_uuid untuk mendukung multi-org
                $model->{$column} = auth()->user()?->active_branch_uuid;
            }

            $companyColumn = 'company_uuid';
            if (empty($model->{$companyColumn}) && !empty($model->{$column})) {
                $model->{$companyColumn} = Branch::query()
                    ->withoutGlobalScopes()
                    ->whereKey($model->{$column})
                    ->value('company_uuid');
            }
        });
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, $this->getBranchColumn(), 'uuid');
    }
}
