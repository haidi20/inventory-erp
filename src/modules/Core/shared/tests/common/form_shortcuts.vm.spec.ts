import { beforeEach, describe, expect, it, vi } from 'vitest';
import { createPinia, setActivePinia } from 'pinia';
import { ref } from 'vue';
import { useFormHelpVM } from '@modules/Core/shared/Components/common/form_help.vm';
import { useFormShortcuts } from '@modules/Core/shared/Components/common/form_shortcuts.vm';

const shortcutRefs = {
    ctrl_s: ref(false),
    ctrl_f: ref(false),
    escape: ref(false),
    f1: ref(false),
};

const shortcutCallbacks = new Map<unknown, () => void>();

vi.mock('@vueuse/core', () => ({
    useMagicKeys: vi.fn(() => shortcutRefs),
    whenever: vi.fn((source: unknown, callback: () => void) => {
        shortcutCallbacks.set(source, callback);
    }),
}));

function trigger(key: keyof typeof shortcutRefs) {
    const callback = shortcutCallbacks.get(shortcutRefs[key]);

    if (!callback) throw new Error(`Shortcut callback ${key} was not registered`);
    callback();
}

describe('Form shortcuts VM', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        shortcutCallbacks.clear();
    });

    it('submits only when allowed and handles help first on escape', () => {
        const helpVm = useFormHelpVM('form-shortcuts-test');
        const submit = vi.fn();
        const cancel = vi.fn();
        let canSubmit = false;

        useFormShortcuts({
            helpVm,
            canSubmit: () => canSubmit,
            submit,
            cancel,
        });
        helpVm.show();

        trigger('ctrl_s');
        expect(submit).not.toHaveBeenCalled();

        canSubmit = true;
        trigger('ctrl_s');
        expect(submit).toHaveBeenCalledTimes(1);

        trigger('escape');
        expect(helpVm.isVisible).toBe(false);
        expect(cancel).not.toHaveBeenCalled();

        trigger('escape');
        expect(cancel).toHaveBeenCalledTimes(1);

        trigger('f1');
        expect(helpVm.isVisible).toBe(true);
    });
});
