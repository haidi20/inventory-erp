/// <reference types="vite/client" />

declare module "*.vue" {
  import type { DefineComponent } from "vue";
  const component: DefineComponent<{}, {}, any>;
  export default component;
}

declare module "vue-select" {
  import { DefineComponent } from "vue";
  const VueSelect: DefineComponent<any, any, any>;
  export default VueSelect;
}
