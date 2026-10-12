export type Select2Mode = 'client' | 'server';

export interface Select2Option {
    [key: string]: any;
}

export interface Select2Props {
    modelValue?: any;
    options?: Select2Option[];
    getData?: Select2Option[];
    initialOptions?: Select2Option[];
    endpoint?: string;
    mode?: Select2Mode;
    params?: Record<string, any>;
    label?: string;
    valueKey?: string;
    reduce?: (option: Select2Option) => any;
    fieldId?: string;
    fieldName?: string;
    nameVModel?: string;
    placeholder?: string;
    clearable?: boolean;
    disabled?: boolean;
    loading?: boolean;
    appendToBody?: boolean;
    storeId?: string;
}

export interface Select2Config {
    mode?: Select2Mode;
    options?: Select2Option[];
    initialOptions?: Select2Option[];
    endpoint?: string;
    params?: Record<string, any>;
    selectedValue?: any;
}

export interface Select2State {
    search: string;
    options: Select2Option[];
    isLoading: boolean;
}
