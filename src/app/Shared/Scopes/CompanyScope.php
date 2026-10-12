<?php

namespace App\Shared\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global Scope untuk memfilter data berdasarkan company_uuid user yang sedang login.
 *
 * Prioritas scope:
 * 1. Superadmin (role superadmin): bypass total, optional filter via ?company_uuid=xxx
 * 2. Header X-Klinik-Company-Uuid
 * 3. user->active_company_uuid (pivot default atau legacy kolom)
 * 4. Tidak ada user/login (artisan/seed): skip scope
 */
class CompanyScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }

        // Superadmin bypass total; optional filter via query param
        if ($user->isSuperAdmin()) {
            if ($companyUuid = request('company_uuid')) {
                $column = method_exists($model, 'getCompanyColumn')
                    ? $model->getCompanyColumn()
                    : 'company_uuid';
                $builder->where($model->getTable() . '.' . $column, $companyUuid);
            }
            return;
        }

        // current_company_uuid() sudah memuat fallback pivot; hindari query duplikat.
        $companyUuid = current_company_uuid();
        if (!$companyUuid) {
            $builder->whereRaw('1 = 0');
            return;
        }

        $column = method_exists($model, 'getCompanyColumn')
            ? $model->getCompanyColumn()
            : 'company_uuid';

        $builder->where($model->getTable() . '.' . $column, $companyUuid);
    }

    public function extend(Builder $builder): void
    {
        $builder->macro('withoutCompanyScope', function (Builder $builder): Builder {
            return $builder->withoutGlobalScope(self::class);
        });

        $builder->macro('whereCompany', function (Builder $builder, ?string $companyUuid): Builder {
            if (!$companyUuid) {
                return $builder;
            }
            $model = $builder->getModel();
            $column = method_exists($model, 'getCompanyColumn')
                ? $model->getCompanyColumn()
                : 'company_uuid';
            return $builder->where($model->getTable() . '.' . $column, $companyUuid);
        });
    }
}
