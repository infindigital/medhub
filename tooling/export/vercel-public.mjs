/**
 * Vercel build step: copy the committed static HTML copy (the files listed in
 * .static-export.json) into public/, so only the website is deployed — not the
 * theme source, docs or tooling. Run by vercel.json; no dependencies needed.
 */
import { readFileSync, mkdirSync, copyFileSync, rmSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = join(dirname(fileURLToPath(import.meta.url)), '..', '..');
const OUT = join(ROOT, 'public');
const { files } = JSON.parse(readFileSync(join(ROOT, '.static-export.json'), 'utf8'));

rmSync(OUT, { recursive: true, force: true });
for (const f of files) {
	const dest = join(OUT, ...f.split('/'));
	mkdirSync(dirname(dest), { recursive: true });
	copyFileSync(join(ROOT, ...f.split('/')), dest);
}
console.log(`public/: ${files.length} files`);
