import { useMagicKeys, whenever } from '@vueuse/core';
import { defineStore } from 'pinia';
import type { FormShortcutHandlers } from './form_shortcuts.state';

function createFormShortcutsVMStore(storeId: string, handlers: FormShortcutHandlers) {
    return defineStore(storeId, () => {
        const { ctrl_s, ctrl_f, escape, f1 } = useMagicKeys({
            passive: false,
            onEventFired(event) {
                if ((event.ctrlKey && (event.key === 's' || event.key === 'f')) || event.key === 'F1') {
                    if (event.type === 'keydown') event.preventDefault();
                }
            },
        });

        whenever(ctrl_s, () => {
            if (!handlers.submit || (handlers.canSubmit && !handlers.canSubmit())) return;
            void handlers.submit();
        });

        whenever(ctrl_f, () => {
            if (!handlers.focusSearch) return;
            handlers.focusSearch(handlers.searchInput?.() ?? null);
        });

        whenever(escape, () => {
            if (handlers.helpVm.isVisible) {
                handlers.helpVm.close();
                return;
            }
            handlers.cancel?.(handlers.searchInput?.() ?? null);
        });

        whenever(f1, () => {
            handlers.helpVm.toggle();
        });

        return {};
    });
}

export function useFormShortcuts(handlers: FormShortcutHandlers) {
    createFormShortcutsVMStore(`${handlers.helpVm.$id}-shortcuts`, handlers)();
}
