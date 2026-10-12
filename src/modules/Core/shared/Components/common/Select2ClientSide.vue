<template>
    <Select2
        :model-value="modelValue"
        :options="getData"
        mode="client"
        :label="label"
        :value-key="valueKey"
        :reduce="reduce"
        :field-id="fieldId"
        :field-name="fieldName"
        :placeholder="placeholder"
        :clearable="clearable"
        :disabled="disabled"
        :loading="loading"
        :append-to-body="appendToBody"
        @update:model-value="emit('update:modelValue', $event)"
        @change="emit('change', $event)"
        @update:name-v-model="emit('update:nameVModel', $event)"
        @open="emit('open')"
        @close="emit('close')"
        @search="emit('search', $event)"
    />
</template>

<script setup lang="ts">
import Select2 from './Select2.vue';
import type { Select2Option } from './select2.types';

withDefaults(defineProps<{
    getData: Select2Option[];
    fieldId?: string;
    fieldName?: string;
    placeholder?: string;
    disabled?: boolean;
    clearable?: boolean;
    modelValue?: any;
    label?: string;
    valueKey?: string;
    reduce?: (option: Select2Option) => any;
    nameVModel?: string;
    loading?: boolean;
    appendToBody?: boolean;
}>(), {
    fieldId: undefined,
    fieldName: undefined,
    placeholder: '',
    disabled: false,
    clearable: true,
    modelValue: null,
    label: 'text',
    valueKey: 'id',
    reduce: undefined,
    nameVModel: undefined,
    loading: false,
    appendToBody: true,
});

const emit = defineEmits<{
    (event: 'update:modelValue', value: any): void;
    (event: 'change', value: any): void;
    (event: 'update:nameVModel', value: string): void;
    (event: 'open'): void;
    (event: 'close'): void;
    (event: 'search', query: string): void;
}>();
</script>
