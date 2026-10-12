import { existsSync, readFileSync } from 'node:fs';

const path = process.argv[2] ?? 'test-results/junit.xml';
const threshold = Number(process.argv[3] ?? 1);

if (!existsSync(path)) {
    console.error(`Playwright report tidak ditemukan: ${path}`);
    process.exit(1);
}

const xml = readFileSync(path, 'utf8');
const tests = (xml.match(/<testcase\b/g) ?? []).length;
const failures = (xml.match(/<failure\b/g) ?? []).length + (xml.match(/<error\b/g) ?? []).length;
const passRate = tests === 0 ? 0 : ((tests - failures) / tests) * 100;

console.log(`Playwright functional coverage: ${passRate.toFixed(2)}% (${tests - failures}/${tests} scenarios, gate ${threshold}%)`);

if (tests === 0 || passRate < threshold) {
    console.error(`Playwright coverage gate gagal: ${passRate}% < ${threshold}%.`);
    process.exit(1);
}
