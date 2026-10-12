import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

const restrictRelativeImports = () => {
    return {
        name: 'restrict-relative-imports',
        enforce: 'pre',
        transform(code, id) {
            // Only check our source files
            if (id.replace(/\\/g, '/').includes('resources/js')) {
                // Look for: import ... from '../' or import '../' or require('../')
                // This regex catches ../ at any level
                const relativeImportRegex = /from\s+['"](\.\.\/)+|import\s+['"](\.\.\/)+|require\(['"](\.\.\/)+/g;

                if (relativeImportRegex.test(code)) {
                    throw new Error(
                        `\n[Vite Validation] Relative import (../) detected in:\n${id}\n\n` +
                        `Standard: Please use the alias "@/" instead of relative paths.`
                    );
                }
            }
            return null;
        }
    };
};

export default defineConfig({
    plugins: [
        restrictRelativeImports(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/login.css',
                'resources/css/dashboard.css',
                'resources/js/app.ts'
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources'),
            '@modules': path.resolve(__dirname, './modules'),
        },
    },
    server: {
        host: '127.0.0.1',
        watch: {
            ignored: ['**/storage/framework/views/**', '**/vendor/**', '**/node_modules/**'],
            usePolling: true,
            interval: 100,
        },
        hmr: {
            host: '127.0.0.1',
            overlay: true,
        },
    },
    optimizeDeps: {
        include: ['vue', '@inertiajs/vue3', 'pinia'],
    },
});
