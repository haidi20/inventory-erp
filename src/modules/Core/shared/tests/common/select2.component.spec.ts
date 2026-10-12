import { describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import Select2 from '@modules/Core/shared/Components/common/Select2.vue';

const vueSelectMock = vi.hoisted(() => ({
    name: 'VueSelect',
    props: ['appendToBody'],
    template: '<div />',
}));

vi.mock('vue-select', () => ({
    default: vueSelectMock,
}));

describe('Select2 component', () => {
    it('mengirim opsi appendToBody ke VueSelect', () => {
        setActivePinia(createPinia());

        const wrapper = mount(Select2, {
            props: {
                modelValue: null,
                options: [{ id: 1, name: 'Alpha' }],
                storeId: 'select2-component-test',
            },
        });

        expect(wrapper.findComponent({ name: 'VueSelect' }).props('appendToBody')).toBe(true);
    });
});
