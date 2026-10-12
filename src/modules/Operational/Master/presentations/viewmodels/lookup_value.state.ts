import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import type { LookupValueFilters, LookupValueFormData, LookupValueModel } from '@modules/Operational/Master/domain/models/lookup_value.model';

export type { LookupValueFilters, LookupValueFormData, LookupValueModel };

export interface LookupValueMeta {
    total: number;
    current_page: number;
    per_page: number;
    from?: number;
    to?: number;
    last_page?: number;
    links?: { url: string | null; label: string; active: boolean }[];
}

export const LOOKUP_VALUE_SEARCH_COLUMNS = [
    { id: 'all',   text: 'Semua Kolom' },
    { id: 'code',  text: 'Kode' },
    { id: 'value', text: 'Nilai' },
] as const;

export const LOOKUP_VALUE_PER_PAGE_OPTIONS = [
    { id: 10,  text: '10' },
    { id: 15,  text: '15' },
    { id: 25,  text: '25' },
    { id: 50,  text: '50' },
    { id: 100, text: '100' },
] as const;

export const LOOKUP_VALUE_STATUS_OPTIONS = [
    { id: 'Y', text: 'Aktif' },
    { id: 'N', text: 'Non-Aktif' },
] as const;

export const LOOKUP_VALUE_PRIMARY_OPTIONS = [
    { id: 'N', text: 'Tidak' },
    { id: 'Y', text: 'Ya (Default)' },
] as const;

export const LOOKUP_VALUE_DEFAULT_FILTERS: Required<LookupValueFilters> = {
    search: '',
    sort_by: 'seq',
    sort_direction: 'asc',
    per_page: 5,
    page: 1,
};

export function createLookupValueState() {
    const isLoading = ref(false);
    const isSaving = ref(false);
    const isExporting = ref(false);
    const isImporting = ref(false);
    const showImportMenu = ref(false);
    const showForm = ref(false);
    const lookupset_uuid = ref('');

    const lookupValues = ref<LookupValueModel[]>([]);
    const meta = ref<LookupValueMeta | null>(null);
    const summary = ref({ total: 0 });

    const filters = ref<LookupValueFilters>({
        search: '',
        page: 1,
        per_page: 5,
    });

    const formData = ref<LookupValueFormData>({
        display_name: '',
        program_code: '',
        description: '',
        seq: 0,
        active_flag: 'Y',
        primary_flag: 'N',
        start_date: null,
        end_date: null,
        max_stock: null,
    });

    const page = usePage<{
        flash?: { success?: string | null; error?: string | null };
        errors?: Record<string, string | undefined>;
    }>();

    const flash = computed(() => ({
        success: page.props.flash?.success ?? null,
        error: page.props.flash?.error ?? page.props.errors?.file ?? null,
    }));

    const fetchSequence = { current: 0 };

    return {
        isLoading,
        isSaving,
        isExporting,
        isImporting,
        showImportMenu,
        showForm,
        lookupset_uuid,
        lookupValues,
        meta,
        summary,
        filters,
        formData,
        flash,
        fetchSequence,
    };
}
