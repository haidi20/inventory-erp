import { describe, it, expect, beforeEach, vi } from 'vitest';
import { createPinia, setActivePinia } from 'pinia';
import { useComplaintVM } from '@modules/Clinical/MedicalRecord/presentations/viewmodels/complaint.vm';

// Mocking dependencies if necessary
vi.mock('@inertiajs/vue3', () => ({
    useForm: (data: any) => ({
        ...data,
        data: () => data,
        reset: vi.fn(),
        clearErrors: vi.fn(),
        setError: vi.fn(),
        processing: false,
    }),
    router: {
        post: vi.fn(),
    }
}));

vi.mock('sweetalert2', () => ({
    default: {
        fire: vi.fn(),
    }
}));

describe('Complaint ViewModel', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
    });

    it('should initialize with correct default values', () => {
        const vm = useComplaintVM();
        expect(vm.isFormOpen).toBe(false);
        expect(vm.form.type).toBe('pengaduan');
    });

    it('should open and close the form', () => {
        const vm = useComplaintVM();
        vm.openForm();
        expect(vm.isFormOpen).toBe(true);
        vm.closeForm();
        expect(vm.isFormOpen).toBe(false);
    });
});
