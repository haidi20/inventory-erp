import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import ChartOfAccountsIndex from '@modules/Finance/Journal/presentations/views/ChartOfAccountsIndex.vue';
import ChartOfAccountsForm from '@modules/Finance/Journal/presentations/views/Partials/ChartOfAccountsForm.vue';

const apiService = vi.hoisted(() => ({
    get: vi.fn(),
    post: vi.fn(),
    put: vi.fn(),
    delete: vi.fn(),
}));

const swal = vi.hoisted(() => ({
    fire: vi.fn(),
}));

vi.mock('@modules/Core/shared/core/api_service', () => ({
    default: apiService,
}));

vi.mock('@inertiajs/vue3', () => ({
    Head: {
        name: 'Head',
        template: '<div />',
    },
    usePage: () => ({
        props: {
            auth: {
                user: {
                    roles: ['superadmin'],
                    permissions: [],
                },
            },
        },
    }),
}));

vi.mock('sweetalert2', () => ({
    default: swal,
}));

const accountPayload = {
    data: {
        data: [
            {
                uuid: 'account-1',
                code: '10000',
                name: 'KAS',
                type: 'asset',
                is_active: true,
            },
        ],
        meta: {
            current_page: 1,
            per_page: 10,
            total: 105,
            last_page: 11,
        },
    },
};

describe('ChartOfAccountsIndex', () => {
    const mountPage = () => mount(ChartOfAccountsIndex, {
        global: {
            stubs: {
                BaseModal: {
                    template: '<div><slot /></div>',
                },
            },
        },
    });

    beforeEach(() => {
        setActivePinia(createPinia());
        apiService.get.mockResolvedValue(accountPayload);
        apiService.post.mockResolvedValue({ data: {} });
        apiService.put.mockResolvedValue({ data: {} });
        apiService.delete.mockResolvedValue(undefined);
        swal.fire.mockResolvedValue({ isConfirmed: true });
        vi.useFakeTimers();
    });

    afterEach(() => {
        vi.useRealTimers();
        vi.clearAllMocks();
    });

    it('renders account fields returned by the server-side table', async () => {
        const wrapper = mountPage();

        await flushPromises();

        expect(apiService.get).toHaveBeenCalledWith('/accounts', expect.objectContaining({
            params: expect.objectContaining({
                page: 1,
                per_page: 10,
                search_column: 'all',
            }),
        }));

        const row = wrapper.find('tbody tr');

        expect(row.exists()).toBe(true);
        expect(row.text()).toContain('10000');
        expect(row.text()).toContain('KAS');
        expect(row.text()).toContain('asset');
        expect(wrapper.find('[data-testid="coa-status-10000"]').text()).toBe('Aktif');
        expect(wrapper.text()).not.toContain('Tidak ada data ditemukan');
    });

    it('sends debounced search and pagination parameters to the server', async () => {
        const wrapper = mountPage();

        await flushPromises();
        apiService.get.mockClear();
        await wrapper.find('input[type="text"]').setValue('KAS');
        vi.advanceTimersByTime(350);
        await flushPromises();

        expect(apiService.get).toHaveBeenCalledTimes(1);
        expect(apiService.get).toHaveBeenCalledWith('/accounts', expect.objectContaining({
            params: expect.objectContaining({
                page: 1,
                per_page: 10,
                search: 'KAS',
                search_column: 'all',
            }),
        }));
    });

    it('creates an account through the server-side endpoint', async () => {
        const wrapper = mountPage();

        await flushPromises();
        await wrapper.find('[data-testid="btn-create-account"]').trigger('click');

        const modal = wrapper.findComponent(ChartOfAccountsForm);
        await modal.find('[data-testid="input-account-code"]').setValue('99999');
        await modal.find('[data-testid="input-account-name"]').setValue('Akun Uji');
        await modal.find('form').trigger('submit');
        await flushPromises();

        expect(apiService.post).toHaveBeenCalledWith('/accounts', {
            code: '99999',
            name: 'Akun Uji',
            type: 'asset',
            is_active: true,
        });
        expect(wrapper.findComponent(ChartOfAccountsForm).exists()).toBe(false);
    });

    it('updates an existing account through the server-side endpoint', async () => {
        const wrapper = mountPage();

        await flushPromises();
        await wrapper.find('[data-testid="btn-edit-account-10000"]').trigger('click');

        const modal = wrapper.findComponent(ChartOfAccountsForm);
        await modal.find('[data-testid="input-account-name"]').setValue('KAS Utama');
        await modal.find('form').trigger('submit');
        await flushPromises();

        expect(apiService.put).toHaveBeenCalledWith('/accounts/account-1', {
            code: '10000',
            name: 'KAS Utama',
            type: 'asset',
            is_active: true,
        });
    });

    it('deletes an account after confirmation and reloads the table', async () => {
        const wrapper = mountPage();

        await flushPromises();
        apiService.get.mockClear();
        await wrapper.find('[data-testid="btn-delete-account-10000"]').trigger('click');
        await flushPromises();

        expect(swal.fire).toHaveBeenCalledWith(expect.objectContaining({
            icon: 'warning',
            title: 'Hapus akun?',
        }));
        expect(apiService.delete).toHaveBeenCalledWith('/accounts/account-1');
        expect(apiService.get).toHaveBeenCalledTimes(1);
    });
});
