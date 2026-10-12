<?php

namespace App\Modules\Operational\Master\Http\Controllers;

use App\Modules\Core\Identity\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Modules\Operational\Master\Entities\LookupImportEntity;
use App\Modules\Operational\Master\Services\LookupService;
use App\Modules\Operational\Master\Entities\LookupSetEntity;
use App\Modules\Operational\Master\Exports\LookupSetExport;
use App\Modules\Operational\Master\Exports\LookupSetImportTemplateExport;
use App\Modules\Operational\Master\Services\LookupSetService;
use App\Modules\Operational\Master\Entities\LookupSetValueEntity;
use App\Modules\Operational\Master\Exports\LookupSetValueExport;
use App\Modules\Operational\Master\Exports\LookupSetValueImportTemplateExport;
use App\Modules\Operational\Master\Entities\LookupTypeEntity;
use App\Modules\Operational\Master\Entities\LookupValueEntity;
use Illuminate\Http\Request;
use App\Shared\Traits\ResolvesKlinikContext;

class LookUpController extends Controller
{
    use ResolvesKlinikContext;

    public function __construct(
        private LookupService $lookupService,
        private LookupSetService $lookupSetService,
    ) {
    }

    public function typeIndex(Request $request)
    {
        return response()->json($this->lookupService->getTypesPage(LookupTypeEntity::fromArray([
            ...$request->all(),
            'company_uuid' => $this->resolveCompanyUuid($request),
        ])));
    }

    public function typeStore(Request $request)
    {
        $type = $this->lookupService->storeType(LookupTypeEntity::fromArray([
            ...$request->all(),
            'actor_uuid' => auth()->id(),
            'company_uuid' => $this->resolveCompanyUuid($request),
        ]));

        return response()->json($type);
    }

    public function typeShow(string $code)
    {
        $type = $this->lookupService->getTypeByCode(LookupTypeEntity::fromArray([
            'code' => $code,
            'company_uuid' => $this->resolveCompanyUuid(),
        ]));

        abort_if($type === null, 404, 'Tipe lookup tidak ditemukan.');

        return response()->json($type);
    }

    public function typeValues(Request $request, string $code)
    {
        $type = $this->lookupService->getTypeByCode(LookupTypeEntity::fromArray([
            'code' => $code,
            'company_uuid' => $this->resolveCompanyUuid($request),
        ]));

        abort_if($type === null, 404, 'Tipe lookup tidak ditemukan.');

        return response()->json($this->lookupService->getValuesPage(LookupValueEntity::fromArray([
            ...$request->all(),
            'lookup_type_uuid' => $type->uuid,
            'lookup_type_uuid' => $type->uuid,
            'company_uuid' => $this->resolveCompanyUuid($request),
        ])));
    }

    public function typeValueStore(Request $request, string $code)
    {
        $type = $this->lookupService->getTypeByCode(LookupTypeEntity::fromArray([
            'code' => $code,
            'company_uuid' => $this->resolveCompanyUuid($request),
        ]));

        abort_if($type === null, 404, 'Tipe lookup tidak ditemukan.');

        $value = $this->lookupService->storeValue(LookupValueEntity::fromArray([
            ...$request->all(),
            'lookup_type_uuid' => $type->uuid,
            'lookup_type_uuid' => $type->uuid,
            'actor_uuid' => auth()->id(),
            'company_uuid' => $this->resolveCompanyUuid($request),
        ]));

        return response()->json($value);
    }

    public function typeValueUpdate(Request $request, string $uuid)
    {
        $value = $this->lookupService->updateValue(LookupValueEntity::fromArray([
            ...$request->all(),
            'uuid' => $uuid,
            'actor_uuid' => auth()->id(),
            'company_uuid' => $this->resolveCompanyUuid($request),
        ]));

        return response()->json($value);
    }

    public function typeDestroy(string $uuid)
    {
        $this->lookupService->deleteType($uuid);
        return response()->noContent();
    }

    public function typeValueDestroy(string $uuid)
    {
        $this->lookupService->deleteValue(LookupValueEntity::fromArray([
            'uuid' => $uuid,
            'company_uuid' => $this->resolveCompanyUuid(),
        ]));

        return response()->noContent();
    }

    public function setIndex(Request $request)
    {
        $page = max(1, $request->integer('page', 1));
        $perPage = max(1, $request->integer('per_page', 10));

        $result = $this->lookupSetService->indexSet(LookupSetEntity::fromArray([
            'search' => (string) $request->string('search')->trim(),
            'search_column' => (string) $request->string('search_column', 'all')->trim(),
            'sort_by' => (string) $request->string('sort_by', 'name')->trim(),
            'sort_direction' => (string) $request->string('sort_direction', 'asc')->trim(),
            'page' => $page,
            'per_page' => $perPage,
            'company_uuid' => $this->resolveCompanyUuid($request),
            'filter_active' => $request->has('filter_active') ? filter_var($request->input('filter_active'), FILTER_VALIDATE_BOOLEAN) : null,
        ]));

        $total = $result->filtered;
        $lastPage = max(1, (int) ceil(max($total, 1) / $perPage));
        $from = $total === 0 ? null : (($page - 1) * $perPage) + 1;
        $to = $total === 0 ? null : min($page * $perPage, $total);

        return response()->json([
            'data' => array_map(fn (LookupSetEntity $item) => [
                'uuid' => $item->uuid,
                'id' => $item->id,
                'name' => $item->name,
                'program_code' => $item->program_code,
                'description' => $item->description,
                'user_can_edit_flag' => $item->user_can_edit_flag ?? 'Y',
                'is_active' => (bool) ($item->is_active ?? true),
            ], $result->items),
            'total' => $total,
            'current_page' => $page,
            'per_page' => $perPage,
            'from' => $from,
            'to' => $to,
            'links' => $this->buildPaginationLinks($page, $lastPage, $request),
            'meta' => [
                'total' => $total,
                'current_page' => $page,
                'from' => $from,
                'to' => $to,
                'per_page' => $perPage,
                'last_page' => $lastPage,
            ],
        ]);
    }

    public function setStore(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'program_code' => ['nullable', 'string', 'max:100'],
        ]);

        $set = $this->lookupSetService->storeSet(LookupSetEntity::fromArray([
            ...$request->all(),
            'actor_uuid' => auth()->id(),
            'company_uuid' => $this->resolveCompanyUuid($request),
        ]));

        return response()->json($set);
    }

    public function setShow(string $uuid)
    {
        $set = $this->lookupSetService->showSet(LookupSetEntity::fromArray([
            'uuid' => $uuid,
            'company_uuid' => $this->resolveCompanyUuid(),
        ]));

        abort_if($set === null, 404, 'Kategori lookup tidak ditemukan.');

        return response()->json($set);
    }

    public function setValues(Request $request, string $uuid)
    {
        $page    = max(1, $request->integer('page', 1));
        $perPage = max(1, $request->integer('per_page', 10));

        $result = $this->lookupSetService->indexValue(LookupSetValueEntity::fromArray([
            'lookup_set_uuid' => $uuid,
            'company_uuid'    => $this->resolveCompanyUuid($request),
            'search'          => (string) $request->string('search')->trim(),
            'sort_by'         => (string) $request->string('sort_by', 'seq')->trim(),
            'sort_direction'  => (string) $request->string('sort_direction', 'asc')->trim(),
            'start'           => ($page - 1) * $perPage,
            'length'          => $perPage,
        ]));

        $total    = $result->filtered;
        $lastPage = max(1, (int) ceil(max($total, 1) / $perPage));
        $from     = $total === 0 ? null : (($page - 1) * $perPage) + 1;
        $to       = $total === 0 ? null : min($page * $perPage, $total);

        return response()->json([
            'data' => array_map(fn ($item) => [
                'uuid'         => $item->uuid,
                'seq'          => $item->seq,
                'display_name' => $item->display_name,
                'program_code' => $item->program_code1,
                'description'  => $item->description,
                'active_flag'  => $item->active_flag,
                'primary_flag' => $item->primary_flag ?? null,
                'start_date'   => $item->start_date ?? null,
                'end_date'     => $item->end_date ?? null,
                'max_stock'    => $item->max_stock ?? null,
            ], $result->items),
            'total'        => $total,
            'current_page' => $page,
            'per_page'     => $perPage,
            'last_page'    => $lastPage,
            'from'         => $from,
            'to'           => $to,
            'meta'         => [
                'total'        => $total,
                'current_page' => $page,
                'last_page'    => $lastPage,
                'from'         => $from,
                'to'           => $to,
                'per_page'     => $perPage,
            ],
        ]);
    }
    public function setUpdate(Request $request, string $uuid)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'program_code' => ['nullable', 'string', 'max:100'],
        ]);

        $set = $this->lookupSetService->updateSet(LookupSetEntity::fromArray([
            ...$request->all(),
            'uuid' => $uuid,
            'actor_uuid' => auth()->id(),
            'company_uuid' => $this->resolveCompanyUuid($request),
        ]));

        return response()->json($set);
    }

    public function setDestroy(string $uuid)
    {
        $this->lookupSetService->destroySet($uuid, $this->resolveCompanyUuid());

        return response()->noContent();
    }

    public function setExport(Request $request)
    {
        $document = $this->lookupSetService->exportSet(LookupSetEntity::fromArray([
            'search' => (string) $request->string('search')->trim(),
            'search_column' => (string) $request->string('search_column', 'all')->trim(),
            'sort_by' => (string) $request->string('sort_by', 'name')->trim(),
            'sort_direction' => (string) $request->string('sort_direction', 'asc')->trim(),
            'page' => $request->integer('page', 1),
            'per_page' => $request->integer('per_page', 10),
            'company_uuid' => $this->resolveCompanyUuid($request),
        ]));

        return Excel::download(
            new LookupSetExport($document),
            'lookup-sets-export.xlsx'
        );
    }

    public function setImport(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ]);

        $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
        $rows = $spreadsheet->getActiveSheet()->toArray();

        $this->lookupSetService->importSet(new LookupImportEntity(
            rows: $rows,
            company_uuid: $this->resolveCompanyUuid($request),
        ));

        return response()->json(['success' => true]);
    }

    public function downloadSetTemplate()
    {
        return Excel::download(
            new LookupSetImportTemplateExport($this->lookupSetService->getSetImportTemplate()),
            'lookup-set-import-template.xlsx'
        );
    }

    public function setValueStore(Request $request, string $setId)
    {
        $value = $this->lookupSetService->storeValue(LookupSetValueEntity::fromArray([
            ...$request->all(),
            'lookup_set_uuid' => $setId,
            'actor_uuid' => auth()->id(),
            'company_uuid' => $this->resolveCompanyUuid($request),
        ]));

        return response()->json($value);
    }

    public function setValueShow(string $uuid)
    {
        $value = $this->lookupSetService->showValue(LookupSetValueEntity::fromArray([
            'uuid' => $uuid,
            'company_uuid' => $this->resolveCompanyUuid(),
        ]));

        abort_if($value === null, 404, 'Nilai lookup tidak ditemukan.');

        return response()->json($value);
    }

    public function setValueUpdate(Request $request, string $setId, string $uuid)
    {
        $value = $this->lookupSetService->updateValue(LookupSetValueEntity::fromArray([
            ...$request->all(),
            'uuid' => $uuid,
            'actor_uuid' => auth()->id(),
            'company_uuid' => $this->resolveCompanyUuid($request),
        ]));

        return response()->json($value);
    }

    public function setValueDestroy(string $uuid)
    {
        $this->lookupSetService->destroyValue($uuid, $this->resolveCompanyUuid());

        return response()->noContent();
    }

    public function setValueExport(Request $request, string $setId)
    {
        $document = $this->lookupSetService->exportValue(LookupSetValueEntity::fromArray([
            ...$request->all(),
            'lookup_set_uuid' => $setId,
            'company_uuid' => $this->resolveCompanyUuid($request),
        ]));

        return Excel::download(
            new LookupSetValueExport($document),
            $document->filename
        );
    }

    public function setValueImport(Request $request, string $setId)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ]);

        $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
        $rows = $spreadsheet->getActiveSheet()->toArray();

        $this->lookupSetService->importValue(new LookupImportEntity(
            lookup_set_uuid: $setId,
            rows: $rows,
            company_uuid: $this->resolveCompanyUuid($request),
        ));

        return response()->json(['success' => true]);
    }

    public function downloadValueTemplate()
    {
        return Excel::download(
            new LookupSetValueImportTemplateExport($this->lookupSetService->getImportTemplate()),
            'template-import-lookup-values.xlsx'
        );
    }

    public function valuesBySetName(string $name)
    {
        return response()->json($this->lookupSetService->getValuesBySetName(LookupSetEntity::fromArray([
            'name' => $name,
            'company_uuid' => $this->resolveCompanyUuid(),
        ])));
    }

    private function buildPaginationLinks(int $page, int $lastPage, Request $request): array
    {
        $query = array_filter([
            'search' => (string) $request->string('search')->trim(),
            'search_column' => (string) $request->string('search_column', 'all')->trim(),
            'sort_by' => (string) $request->string('sort_by', 'name')->trim(),
            'sort_direction' => (string) $request->string('sort_direction', 'asc')->trim(),
            'per_page' => $request->integer('per_page', 10),
        ], fn ($value) => $value !== null && $value !== '');

        $buildUrl = function (int $targetPage) use ($query): string {
            return '/masters/lookups?' . http_build_query([...$query, 'page' => $targetPage]);
        };

        $links = [[
            'url' => $page > 1 ? $buildUrl($page - 1) : null,
            'label' => '&laquo; Previous',
            'active' => false,
        ]];

        for ($cursor = 1; $cursor <= $lastPage; $cursor++) {
            $links[] = [
                'url' => $buildUrl($cursor),
                'label' => (string) $cursor,
                'active' => $cursor === $page,
            ];
        }

        $links[] = [
            'url' => $page < $lastPage ? $buildUrl($page + 1) : null,
            'label' => 'Next &raquo;',
            'active' => false,
        ];

        return $links;
    }
}