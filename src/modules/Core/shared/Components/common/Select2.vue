<template>
    <div class="shared-select2-container">
        <VueSelect
            :id="fieldId"
            v-model="internalValue"
            :options="vm.visibleOptions"
            :placeholder="placeholder"
            :reduce="resolveValue"
            :label="label"
            :clearable="!disabled && clearable"
            :disabled="disabled"
            :searchable="true"
            :loading="effectiveLoading"
            :append-to-body="appendToBody"
            class="shared-select2"
            @open="onOpen"
            @close="emit('close')"
            @search="onSearch"
        >
            <template #open-indicator="{ attributes }">
                <div class="shared-select2-indicator" v-bind="attributes">
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </template>

            <template #deselect>
                <i class="fa-solid fa-xmark shared-select2-clear"></i>
            </template>

            <template #selected-option="option">
                <div class="shared-select2-selected">
                    <span>{{ getOptionLabel(option) }}</span>
                </div>
            </template>

            <template #spinner="{ loading: isLoading }">
                <div v-if="isLoading" class="shared-select2-spinner">
                    <i class="fa-solid fa-circle-notch fa-spin"></i>
                </div>
            </template>

            <template #no-options="{ search, searching }">
                <div class="shared-select2-no-results">
                    <template v-if="effectiveLoading">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        <span>Memuat data...</span>
                    </template>
                    <template v-else-if="searching && search">
                        <span>Tidak ada hasil untuk <strong>{{ search }}</strong></span>
                    </template>
                    <template v-else>
                        <span>Silakan pilih data yang tersedia.</span>
                    </template>
                </div>
            </template>

            <template #option="option">
                <div class="shared-select2-option">
                    <span>{{ getOptionLabel(option) }}</span>
                </div>
            </template>
        </VueSelect>
    </div>
</template>

<script setup lang="ts">
import { computed, useId, watch } from 'vue';
import VueSelectOriginal from 'vue-select';
import { createSelect2VMStore } from './select2.vm';
import type { Select2Option, Select2Props } from './select2.types';
import 'vue-select/dist/vue-select.css';

const VueSelect = VueSelectOriginal as any;

const props = withDefaults(defineProps<Select2Props>(), {
    modelValue: null,
    options: () => [],
    getData: () => [],
    initialOptions: () => [],
    endpoint: undefined,
    mode: undefined,
    params: () => ({}),
    label: 'label',
    valueKey: 'id',
    reduce: undefined,
    fieldId: undefined,
    fieldName: undefined,
    nameVModel: undefined,
    placeholder: '',
    clearable: true,
    disabled: false,
    loading: false,
    appendToBody: true,
    storeId: undefined,
});

const emit = defineEmits<{
    (event: 'update:modelValue', value: any): void;
    (event: 'change', value: any): void;
    (event: 'update:nameVModel', value: string): void;
    (event: 'open'): void;
    (event: 'close'): void;
    (event: 'search', query: string): void;
}>();

const useVM = createSelect2VMStore(props.storeId ?? `select2-${useId()}`, {
    label: props.label,
    valueKey: props.valueKey,
});
const vm = useVM();

const vmConfig = computed(() => ({
    mode: props.mode ?? (props.endpoint ? 'server' : 'client'),
    options: props.options.length > 0 ? props.options : props.getData,
    initialOptions: props.initialOptions,
    endpoint: props.endpoint,
    params: props.params,
    selectedValue: props.modelValue,
}));

const effectiveLoading = computed(() => props.loading || vm.isLoading);

const internalValue = computed({
    get: () => props.modelValue,
    set: (value: any) => {
        emit('update:modelValue', value);
        emit('update:nameVModel', vm.getSelectedName(value));
        emit('change', value);
    },
});

function resolveValue(option: Select2Option): any {
    return props.reduce ? props.reduce(option) : vm.reduceOption(option);
}

function getOptionLabel(option: Select2Option): string {
    return vm.getOptionLabel(option);
}

function onOpen(): void {
    vm.onOpen();
    emit('open');
}

function onSearch(query: string): void {
    vm.onSearch(query);
    emit('search', query);
}

watch(vmConfig, vm.syncConfig, { deep: true, immediate: true });
watch(() => props.modelValue, (value) => {
    void vm.ensureSelectedOption(value);
}, { immediate: true });
</script>

<style scoped>
.shared-select2-container {
    position: relative;
    width: 100%;
}

.shared-select2-indicator {
    align-items: center;
    color: var(--muted, #64748b);
    display: flex;
    font-size: 11px;
    justify-content: center;
    padding: 0 10px;
    transition: all 0.2s ease;
}

.vs--open .shared-select2-indicator {
    color: var(--primary, #6366f1);
    transform: rotate(180deg);
}

.shared-select2-clear {
    color: var(--muted, #64748b);
    font-size: 12px;
    opacity: 0.6;
    transition: all 0.2s ease;
}

.shared-select2-clear:hover {
    color: var(--danger, #ef4444);
    opacity: 1;
}

.shared-select2-spinner {
    color: var(--primary, #6366f1);
    font-size: 12px;
    margin-right: 8px;
}

.shared-select2-no-results {
    align-items: center;
    background: var(--surface, #f8fafc);
    color: var(--muted, #64748b);
    display: flex;
    font-size: 13px;
    gap: 8px;
    justify-content: center;
    padding: 16px;
}

.shared-select2-option {
    display: flex;
    gap: 10px;
}

:deep(.vs__dropdown-toggle) {
    background: var(--card, #ffffff);
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 10px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    min-height: 40px;
    padding: 4px 2px;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

:deep(.vs__selected-options) {
    align-items: center;
    display: flex;
    flex: 1 1 auto;
    flex-wrap: nowrap;
    min-width: 0;
    overflow: hidden;
    padding: 0 2px;
}

:deep(.vs--open .vs__dropdown-toggle) {
    border-bottom-left-radius: 10px;
    border-bottom-right-radius: 10px;
    border-color: var(--primary, #6366f1);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary, #6366f1) 10%, transparent);
}

:deep(.vs__selected) {
    color: var(--text, #1e293b);
    font-size: 14px;
    font-weight: 500;
    line-height: 1.4;
    margin: 0 4px;
    max-width: 100%;
    min-width: 0;
    overflow: hidden;
    padding: 0 6px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

:deep(.vs__search) {
    background: transparent !important;
    border: 0 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    color: var(--text, #1e293b);
    flex: 1 1 32px;
    font-size: 14px;
    height: auto !important;
    line-height: 1.4;
    margin: 0 !important;
    min-height: 0 !important;
    outline: 0 !important;
    padding: 0 0 0 10px !important;
    width: 0 !important;
}

:deep(.vs--single:not(.vs--open) .vs__selected ~ .vs__search) {
    flex: 0 0 1px;
    opacity: 0;
    padding: 0 !important;
}

:deep(.vs__search::placeholder) {
    color: var(--muted, #94a3b8);
}

:deep(.vs__dropdown-menu) {
    animation: shared-select2-dropdown-fade 0.2s ease;
    background: var(--card, #ffffff);
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 12px;
    margin-top: 8px;
    overflow-x: hidden;
    padding: 6px;
    z-index: 99999 !important;
}

@keyframes shared-select2-dropdown-fade {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

:deep(.vs__dropdown-option) {
    border-radius: 8px;
    color: var(--text, #1e293b);
    font-size: 14px;
    margin-bottom: 2px;
    padding: 10px 14px;
    transition: all 0.15s ease;
}

:deep(.vs__dropdown-option--highlight) {
    background: var(--primary, #6366f1);
    color: var(--on-primary, #ffffff);
}

:deep(.vs__dropdown-option--selected) {
    background: color-mix(in srgb, var(--primary, #6366f1) 8%, transparent);
    color: var(--primary, #6366f1);
    font-weight: 600;
}

:deep(.vs__dropdown-menu::-webkit-scrollbar) {
    width: 6px;
}

:deep(.vs__dropdown-menu::-webkit-scrollbar-thumb) {
    background: var(--border, #cbd5e1);
}

:deep(.vs__dropdown-menu::-webkit-scrollbar-track) {
    background: transparent;
}
</style>
