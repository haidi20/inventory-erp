import { readFileSync, readdirSync } from 'node:fs';
import { join } from 'node:path';
import { describe, expect, it } from 'vitest';

function collectVueFiles(directory: string): string[] {
    return readdirSync(directory, { withFileTypes: true }).flatMap((entry) => {
        const fullPath = join(directory, entry.name);

        return entry.isDirectory() ? collectVueFiles(fullPath) : entry.name.endsWith('.vue') ? [fullPath] : [];
    });
}

function getScript(source: string): string {
    return source.match(/<script[^>]*>([\s\S]*?)<\/script>/)?.[1] ?? '';
}

describe('Vue architecture', () => {
    const featureViews = collectVueFiles('modules').filter((file) => !file.replaceAll('\\', '/').includes('shared/Components/'));
    const sharedComponents = collectVueFiles('modules/Core/shared/Components');

    it('uses the expected feature view inventory', () => {
        expect(featureViews.length).toBeGreaterThan(100);
    });

    it('keeps API, browser, and business calls out of feature views', () => {
        const violations: string[] = [];

        for (const file of featureViews) {
            const script = getScript(readFileSync(file, 'utf8'));
            const forbiddenPatterns: Array<[RegExp, string]> = [
                [/\bapiService\b/, 'apiService'],
                [/\busePage\(/, 'usePage'],
                [/\buseForm\(/, 'useForm'],
                [/\blocalStorage\b/, 'localStorage'],
                [/\bsessionStorage\b/, 'sessionStorage'],
                [/\bSwal\./, 'Swal'],
                [/\baxios\./, 'axios'],
                [/(?<![\w.$])fetch\(/, 'fetch API'],
                [/\bdocument\./, 'document API'],
                [/\bwindow\./, 'window API'],
                [/\bsetTimeout\(/, 'setTimeout'],
                [/\bsetInterval\(/, 'setInterval'],
                [/\buseMagicKeys\b|\bwhenever\(/, 'inline keyboard shortcut logic'],
            ];

            for (const [pattern, label] of forbiddenPatterns) {
                if (pattern.test(script)) violations.push(`${file}: ${label}`);
            }
        }

        expect(violations).toEqual([]);
    });

    it('keeps reactive state and helper functions out of feature views', () => {
        const violations: string[] = [];

        for (const file of featureViews) {
            const script = getScript(readFileSync(file, 'utf8'));
            const forbiddenPatterns: Array<[RegExp, string]> = [
                [/(?<![\w.$])ref\(/, 'ref()'],
                [/(?<![\w.$])ref</, 'ref<T>()'],
                [/\breactive\(/, 'reactive()'],
                [/\bcomputed\(/, 'computed()'],
                [/\bwatch\(/, 'watch()'],
                [/\b(?:async\s+)?function\s+/, 'function declaration'],
                [/\.map\(|\.filter\(|\.reduce\(|\.sort\(/, 'collection transformation'],
            ];

            for (const [pattern, label] of forbiddenPatterns) {
                if (pattern.test(script)) violations.push(`${file}: ${label}`);
            }

            if (/^\s*const\s+\w+\s*(:[^=]+)?\s*=\s*(\[|\{)/m.test(script)) {
                violations.push(`${file}: inline array/object state`);
            }
        }

        expect(violations).toEqual([]);
    });

    it('allows presentational shared adapters but blocks direct business calls', () => {
        const violations: string[] = [];

        for (const file of sharedComponents) {
            const script = getScript(readFileSync(file, 'utf8'));
            const forbiddenPatterns: Array<[RegExp, string]> = [
                [/\bapiService\b/, 'apiService'],
                [/\busePage\(/, 'usePage'],
                [/\buseForm\(/, 'useForm'],
                [/\bSwal\./, 'Swal'],
                [/\baxios\./, 'axios'],
                [/(?<![\w.$])fetch\(/, 'fetch API'],
            ];

            for (const [pattern, label] of forbiddenPatterns) {
                if (pattern.test(script)) violations.push(`${file}: ${label}`);
            }
        }

        expect(violations).toEqual([]);
    });
});
