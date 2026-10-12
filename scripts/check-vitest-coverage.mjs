import { existsSync, readFileSync } from 'node:fs';

const path = process.argv[2] ?? 'coverage/coverage-summary.json';
const threshold = Number(process.argv[3] ?? 1);

if (!existsSync(path)) {
    console.error(`Coverage report tidak ditemukan: ${path}`);
    process.exit(1);
}

const summary = JSON.parse(readFileSync(path, 'utf8'));
const { lines } = summary.total ?? {};

if (!lines) {
    console.error('Report coverage tidak memiliki totals.');
    process.exit(1);
}

console.log(`Vitest line coverage: ${lines.pct}% (${lines.covered}/${lines.total}, gate ${threshold}%)`);

if (lines.pct < threshold) {
    console.error(`Coverage gate gagal: ${lines.pct}% < ${threshold}%.`);
    process.exit(1);
}
