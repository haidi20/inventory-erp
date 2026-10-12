<?php

namespace App\Shared\Traits;

use App\Shared\Scopes\CompanyScope;
use App\Modules\Core\Organization\Models\Company;

/**
 * Trait untuk menerapkan multi-company scoping pada model.
 * Secara otomatis: 1) filter query by company column, 2) set company column saat creating
 * 
 * Gunakan trait ini bersamaan dengan HasBranchScope untuk full multi-org support.
 */
trait HasCompanyScope
{
    /**
     * Mendapatkan nama kolom company (default: company_uuid)
     */
    public function getCompanyColumn(): string
    {
        return property_exists($this, 'companyColumn') ? $this->companyColumn : 'company_uuid';
    }

    protected static function bootHasCompanyScope(): void
    {
        static::addGlobalScope(new CompanyScope);

        static::creating(function ($model): void {
            $column = $model->getCompanyColumn();
            if (empty($model->{$column}) && auth()->check()) {
                $model->{$column} = auth()->user()?->active_company_uuid;
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class, $this->getCompanyColumn(), 'uuid');
    }
}
