import { computed, ref, watch } from 'vue';
import { defineStore } from 'pinia';
import { usePage } from '@inertiajs/vue3';
import type { ProductFormOptions } from './product.state';
import type { ProductModel } from '@modules/Operational/Master/domain/models/product.model';
import { useProductVM } from './product.vm';
import { PRODUCT_FORM_DEFAULT_TAB, PRODUCT_FORM_TABS } from '../state/product_form.state';
import type { ProductFormTab } from '../state/product_form.state';

export const useProductFormVM = defineStore('product-form-vm', () => {
    const productVm = useProductVM();
    const activeTab = ref<ProductFormTab>(PRODUCT_FORM_DEFAULT_TAB);
    const initialOptions = ref<ProductFormOptions>({ units: [], kinds: [], groups: [], types: [], brands: [], origins: [], grades: [], accounts: [] });
    const page = usePage<{ options?: ProductFormOptions }>();
    const options = computed(() => page.props.options ?? initialOptions.value);

    watch(options, (value) => productVm.updateProductFormOptions(value), { deep: true });

    function selectTab(tab: ProductFormTab): void { activeTab.value = tab; }

    function handleImageDrop(event: DragEvent): void {
        const files = event.dataTransfer?.files;
        if (files?.length) void productVm.addImageFiles(files);
    }

    function initialize(product: ProductModel | null, formOptions: ProductFormOptions): void {
        activeTab.value = PRODUCT_FORM_DEFAULT_TAB;
        initialOptions.value = formOptions;
        productVm.initializeProductForm(product, formOptions);
    }

    return { productVm, activeTab, options, tabs: PRODUCT_FORM_TABS, selectTab, handleImageDrop, initialize };
});
