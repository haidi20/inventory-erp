import { describe, it, expect } from 'vitest';
import { readFileSync, readdirSync, statSync } from 'fs';
import path from 'path';

/**
 * UI/UX Consistency Test (No-Dependency Version)
 * Memastikan semua form mengikuti pola yang sama untuk Side Help Drawer.
 */

function getAllFormFiles(dir: string): string[] {
    let results: string[] = [];
    const list = readdirSync(dir);
    list.forEach(file => {
        file = path.join(dir, file);
        const stat = statSync(file);
        if (stat && stat.isDirectory()) {
            results = results.concat(getAllFormFiles(file));
        } else if (file.endsWith('Form.vue')) {
            results.push(file);
        }
    });
    return results;
}

describe('UI/UX Consistency: Side Help Drawer', () => {
    const featuresDir = path.resolve(__dirname, '../../../../../modules');
    const forms = getAllFormFiles(featuresDir);

    forms.forEach(formPath => {
        const componentName = path.basename(formPath);
        const content = readFileSync(formPath, 'utf-8');

        describe(`Component: ${componentName}`, () => {
            it('harus memiliki InfoIcon untuk memicu bantuan', () => {
                expect(content).toContain('InfoIcon');
                expect(content).toContain('@click="helpVm.toggle()"');
            });

            it('harus menggunakan Transition slide-right untuk help-drawer', () => {
                expect(content).toContain('Transition name="slide-right"');
                expect(content).toContain('class="help-drawer"');
            });

            it('harus menggunakan Transition fade untuk drawer-overlay', () => {
                expect(content).toContain('Transition name="fade"');
                expect(content).toContain('class="drawer-overlay"');
            });

            it('harus memiliki struktur header drawer yang standar', () => {
                expect(content).toContain('class="help-drawer-header"');
                expect(content).toContain('class="help-drawer-icon"');
                expect(content).toContain('class="help-drawer-close"');
                expect(content).toContain('title="Tutup (Esc)"');
            });

            it('harus memiliki struktur body drawer yang standar', () => {
                expect(content).toContain('class="help-drawer-body"');
                expect(content).toContain('class="help-section-title"');
            });

            it('harus menangani shortcut F1 untuk toggle bantuan', () => {
                expect(content).toContain('useFormShortcuts({');
                expect(content).toContain('helpVm,');
            });

            it('harus menangani shortcut Esc dengan prioritas menutup bantuan', () => {
                expect(content).toContain("from '@modules/Core/shared/Components/common/form_shortcuts.vm'");
            });

            it('harus menggunakan style eksternal atau minimal tidak ada style HelpSection lama', () => {
                expect(content).not.toContain('HelpSection');
            });
        });
    });
});
