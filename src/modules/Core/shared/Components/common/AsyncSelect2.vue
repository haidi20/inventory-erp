<template>
    <Select2
        :model-value="modelValue"
        mode="server"
        :endpoint="endpoint"
        :initial-options="initialOptions"
        :params="params"
        :label="label"
        :value-key="valueKey"
        :placeholder="placeholder"
        :clearable="clearable"
        :disabled="disabled"
        @update:model-value="emit('update:modelValue', $event)"
        @change="emit('change', $event)"
    />
</template>

<script setup lang="ts">
import Select2 from './Select2.vue';
import type { Select2Option } from './select2.types';

withDefaults(defineProps<{
    endpoint: string;
    initialOptions?: Select2Option[];
    modelValue?: any;
    label?: string;
    valueKey?: string;
    placeholder?: string;
    clearable?: boolean;
    disabled?: boolean;
    params?: Record<string, any>;
}>(), {
    initialOptions: () => [],
    modelValue: null,
    label: 'label',
    valueKey: 'uuid',
    placeholder: '',
    clearable: true,
    disabled: false,
    params: () => ({}),
});

const emit = defineEmits<{
    (event: 'update:modelValue', value: any): void;
    (event: 'change', value: any): void;
}>();
</script>
