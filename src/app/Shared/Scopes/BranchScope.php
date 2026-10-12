<?php

namespace App\Shared\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global Scope untuk memfilter data berdasarkan branch_uuid user yang sedang login.
 *
 * CATATAN: BranchScope mengasumsikan CompanyScope sudah berjalan.
 * BranchScope hanya memfilter branch_uuid, bukan company_uuid.
 *
 * Superadmin (role superadmin): bypass total, optional filter via ?branch_uuid=xxx
 */
class BranchScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }

        // Superadmin bypass total; optional filter via query param
        if ($user->isSuperAdmin()) {
            if ($branchUuid = request('branch_uuid')) {
                $column = method_exists($model, 'getBranchColumn')
                    ? $model->getBranchColumn()
                    : 'branch_uuid';
                $builder->where($model->getTable() . '.' . $column, $branchUuid);
            }
            return;
        }

        // current_branch_uuid() sudah memuat fallback pivot; hindari query duplikat.
        $branchUuid = current_branch_uuid();
        if (!$branchUuid) {
            $builder->whereRaw('1 = 0');
            return;
        }

        $column = method_exists($model, 'getBranchColumn')
            ? $model->getBranchColumn()
            : 'branch_uuid';

        $builder->where($model->getTable() . '.' . $column, $branchUuid);
    }

    public function extend(Builder $builder): void
    {
        $builder->macro('withoutBranchScope', function (Builder $builder): Builder {
            return $builder->withoutGlobalScope(self::class);
        });

        $builder->macro('whereBranch', function (Builder $builder, ?string $branchUuid): Builder {
            if (!$branchUuid) {
                return $builder;
            }
            $model = $builder->getModel();
            $column = method_exists($model, 'getBranchColumn')
                ? $model->getBranchColumn()
                : 'branch_uuid';
            return $builder->where($model->getTable() . '.' . $column, $branchUuid);
        });
    }
}
