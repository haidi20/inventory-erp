import path from 'path';
import { defineConfig } from 'vitest/config';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [vue()],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources'),
            '@modules': path.resolve(__dirname, './modules'),
        },
    },
    test: {
        environment: 'jsdom',
        pool: 'forks',
        maxWorkers: 1,
        setupFiles: ['modules/Core/shared/tests/setup/klinik-runtime.ts'],
        include: [
            'modules/**/*.spec.ts',
            'resources/js/**/*.spec.ts',
        ],
        exclude: [
            'modules/**/*.e2e.spec.ts',
            'modules/**/*.view.spec.ts',
            'resources/js/**/*.e2e.spec.ts',
            'resources/js/**/*.view.spec.ts',
            'node_modules/**/*'
        ],
        coverage: {
            provider: 'v8',
            reporter: ['json-summary', 'text'],
            include: ['modules/**/*.ts', 'resources/js/**/*.ts'],
        },
    },
});
