import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import apiService from '@modules/Core/shared/core/api_service';
import { SELECT2_INITIAL_STATE } from './select2.state';
import type { Select2Config, Select2Option, Select2State } from './select2.types';

interface Select2StoreDefaults {
    label?: string;
    valueKey?: string;
}

export function createSelect2VMStore(
    storeId: string,
    defaults: Select2StoreDefaults = {},
) {
    return defineStore(storeId, () => {
        const labelKey = defaults.label ?? 'label';
        const valueKey = defaults.valueKey ?? 'id';
        const initialState: Select2State = { ...SELECT2_INITIAL_STATE };

        const search = ref(initialState.search);
        const loadedOptions = ref<Select2Option[]>([...initialState.options]);
        const isLoading = ref(initialState.isLoading);
        const config = ref<Select2Config>({});

        let requestSequence = 0;
        let searchTimer: ReturnType<typeof setTimeout> | undefined;

        const mode = computed(() => config.value.mode
            ?? (config.value.endpoint ? 'server' : 'client'));
        const isServerSide = computed(() => mode.value === 'server');
        const inputOptions = computed(() => config.value.options ?? []);

        const visibleOptions = computed<Select2Option[]>(() => {
            if (isServerSide.value) {
                return loadedOptions.value;
            }

            const query = search.value.trim().toLowerCase();

            if (!query) {
                return inputOptions.value;
            }

            return inputOptions.value.filter((option) => getOptionLabel(option)
                .toLowerCase()
                .includes(query));
        });

        function getOptionLabel(option: Select2Option): string {
            if (option === null || option === undefined) {
                return '';
            }

            if (typeof option !== 'object') {
                return String(option);
            }

            const optionLabel = option[labelKey];

            if (optionLabel !== null && optionLabel !== undefined && optionLabel !== '') {
                return String(optionLabel);
            }

            return String(option.label || option.name || '');
        }

        function reduceOption(option: Select2Option): any {
            if (option === null || option === undefined || typeof option !== 'object') {
                return option;
            }

            return option[valueKey];
        }

        function findOption(value: any): Select2Option | undefined {
            return visibleOptions.value.find((option) => reduceOption(option) === value);
        }

        function getSelectedName(value: any): string {
            const selectedOption = findOption(value);

            return selectedOption ? getOptionLabel(selectedOption) : '';
        }

        function mergeOptions(options: Select2Option[]): Select2Option[] {
            const merged = new Map<any, Select2Option>();

            [...config.value.initialOptions ?? [], ...options].forEach((option) => {
                merged.set(reduceOption(option), option);
            });

            return [...merged.values()];
        }

        function syncConfig(nextConfig: Select2Config): void {
            const previousEndpoint = config.value.endpoint;

            config.value = { ...nextConfig };
            search.value = '';

            if (nextConfig.endpoint !== previousEndpoint) {
                loadedOptions.value = [...nextConfig.initialOptions ?? []];
            }
        }

        async function loadOptions(query = ''): Promise<void> {
            if (!isServerSide.value || !config.value.endpoint) {
                return;
            }

            const sequence = ++requestSequence;
            isLoading.value = true;

            try {
                const response = await apiService.get(config.value.endpoint, {
                    params: {
                        ...config.value.params,
                        search: query,
                        selected: config.value.selectedValue,
                    },
                });

                if (sequence !== requestSequence) {
                    return;
                }

                const rows = response.data?.data ?? response.data ?? [];
                loadedOptions.value = mergeOptions(Array.isArray(rows) ? rows : []);
            } catch (error) {
                if (sequence === requestSequence) {
                    loadedOptions.value = mergeOptions([]);
                    console.error('Gagal memuat opsi select', error);
                }
            } finally {
                if (sequence === requestSequence) {
                    isLoading.value = false;
                }
            }
        }

        function onSearch(query: string): void {
            if (searchTimer) {
                clearTimeout(searchTimer);
            }

            search.value = query;

            if (!isServerSide.value) {
                return;
            }

            searchTimer = setTimeout(() => {
                void loadOptions(query);
            }, 250);
        }

        function onOpen(): void {
            void loadOptions('');
        }

        async function ensureSelectedOption(value: any): Promise<void> {
            if (!isServerSide.value || value === null || value === undefined || value === '') {
                return;
            }

            if (!findOption(value)) {
                await loadOptions('');
            }
        }

        function reset(): void {
            if (searchTimer) {
                clearTimeout(searchTimer);
            }

            requestSequence += 1;
            search.value = '';
            loadedOptions.value = [...config.value.initialOptions ?? []];
            isLoading.value = false;
        }

        return {
            search,
            loadedOptions,
            isLoading,
            mode,
            isServerSide,
            visibleOptions,
            getOptionLabel,
            reduceOption,
            findOption,
            getSelectedName,
            syncConfig,
            loadOptions,
            onSearch,
            onOpen,
            ensureSelectedOption,
            reset,
        };
    });
}
