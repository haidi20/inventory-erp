import type { useFormHelpVM } from './form_help.vm';

export type FormHelpVM = ReturnType<typeof useFormHelpVM>;

export interface FormShortcutHandlers {
    helpVm: FormHelpVM;
    canSubmit?: () => boolean;
    submit?: () => void | Promise<void>;
    cancel?: (searchInput: HTMLInputElement | null) => void;
    focusSearch?: (input: HTMLInputElement | null) => void;
    searchInput?: () => HTMLInputElement | null;
}
