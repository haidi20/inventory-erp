<?php

namespace App\Modules\Core\Identity\Http\Controllers;

use App\Http\Resources\ApiDataResource;
use App\Modules\Finance\Ledger\Services\AuditLogService;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

abstract class Controller extends BaseController
{
    public function __construct() {}

    protected function audit(string $event, $model = null, ?array $before = null, ?array $after = null): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $entityType = $this->resolveAuditEntityType($event, $model);
        $entityUuid = $this->resolveAuditEntityUuid($model, $before, $after);

        app(AuditLogService::class)->record([
            'branch_uuid' => current_branch_uuid(),
            'user_uuid' => $user->uuid,
            'event' => $event,
            'entity_type' => $entityType,
            'entity_uuid' => $entityUuid,
            'before' => $before,
            'after' => $after,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    protected function apiResource(mixed $data): JsonResource|AnonymousResourceCollection
    {
        if (
            $data instanceof Collection
            || $data instanceof LengthAwarePaginator
            || $data instanceof Paginator
            || $data instanceof CursorPaginator
        ) {
            return ApiDataResource::collection($data);
        }

        return ApiDataResource::make($data);
    }

    protected function formatApiPayload(mixed $data): mixed
    {
        return $this->apiResource($data)->resolve(request());
    }

    protected function resolveAuditEntityUuid($model = null, ?array $before = null, ?array $after = null): ?string
    {
        if (is_object($model) && isset($model->uuid)) {
            return (string) $model->uuid;
        }

        foreach ([$after, $before] as $payload) {
            if (!is_array($payload)) {
                continue;
            }

            $uuid = $payload['uuid'] ?? $payload['entity_uuid'] ?? $payload['id'] ?? null;
            if ($uuid !== null && $uuid !== '') {
                return (string) $uuid;
            }
        }

        return null;
    }

    protected function resolveAuditEntityType(string $event, $model = null): ?string
    {
        if (is_object($model)) {
            return get_class($model);
        }

        $resource = Str::before($event, '.');
        if ($resource === '') {
            return null;
        }

        $classBase = Str::studly($resource);
        foreach ([
            'App\\Models\\Master\\',
            'App\\Models\\Accounting\\',
            'App\\Models\\Operational\\',
            'App\\Models\\Inventory\\',
            'App\\Modules\\Clinical\\Registration\\Models\\',
            'App\\Modules\\Operational\\Master\\Models\\',
            'App\\Modules\\Finance\\Ledger\\Models\\',
            'App\\Modules\\Finance\\Journal\\Models\\',
            'App\\Modules\\Core\\Organization\\Models\\',
            'App\\Modules\\Core\\Identity\\Models\\',
        ] as $namespace) {
            $candidate = $namespace . $classBase;
            if (class_exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
