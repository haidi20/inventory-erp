import { defineStore } from 'pinia';
import { ref } from 'vue';
import { FORM_HELP_INITIAL_STATE } from './form_help.state';
import type { FormHelpState } from './form_help.state';

export function createFormHelpVMStore(storeId: string) {
    return defineStore(storeId, () => {
        const initialState: FormHelpState = { ...FORM_HELP_INITIAL_STATE };
        const isVisible = ref(initialState.isVisible);

        function show(): void {
            isVisible.value = true;
        }

        function close(): void {
            isVisible.value = false;
        }

        function toggle(): void {
            isVisible.value = !isVisible.value;
        }

        return { isVisible, show, close, toggle };
    });
}

export function useFormHelpVM(storeId: string) {
    return createFormHelpVMStore(storeId)();
}
