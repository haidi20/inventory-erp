import './bootstrap';
import './echo';
import '@/css/app.css';

import { createApp, h, type DefineComponent } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { route } from 'ziggy-js';
import { ZiggyVue } from 'ziggy-js';

import { createPinia } from 'pinia';

(window as any).route = route;

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';
const pinia = createPinia();

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        const pages = import.meta.glob<DefineComponent>([
            './features/**/*.vue',
            '../../modules/**/*.vue',
        ]);
        
        // Handle full paths like 'Settings/User/presentations/views/UserIndex'
        let fullPath = `./features/${name}.vue`;
        if (pages[fullPath]) {
            return resolvePageComponent(fullPath, pages);
        }

        // Handle nested paths if name is just 'Settings/User'
        const pageName = name.split('/').pop();
        let foundPath = Object.keys(pages).find((p) => {
            const lowP = p.toLowerCase();
            const lowName = name.toLowerCase();
            const lowPageName = pageName?.toLowerCase();
            
            return lowP.includes(lowName) && (
                lowP.endsWith(`${lowPageName}.vue`) || 
                (!lowPageName?.endsWith('index') && lowP.endsWith(`${lowPageName}index.vue`))
            );
        });

        if (!foundPath) {
            throw new Error(`Inertia component [${name}] not found in features directory structure.`);
        }
        
        return resolvePageComponent(foundPath, pages);
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .use(ZiggyVue, (window as any).Ziggy)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
