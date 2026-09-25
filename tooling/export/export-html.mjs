/**
 * Static HTML export of the MedHub sandbox into the PROJECT ROOT, for VS Code
 * "Go Live" (Live Server) or double-clicking index.html.
 *
 *   1. sandbox/serve.sh must be running (http://localhost:8080) during the export
 *   2. node tooling/export/export-html.mjs
 *
 * Output (all links relative, works on http:// and file://):
 *   index.html                    homepage
 *   <page>.html                   pages and blog articles (e.g. cpap-in-dubai.html)
 *   <page>-page-2.html            pagination
 *   product/<slug>.html           products
 *   product-category/<slug>.html  product categories   (… /<slug>-page-2.html)
 *   brand/<slug>.html             brands
 *   category/<slug>.html          blog categories
 *   404.html
 *   assets/…                      CSS, JS, fonts, images (copied from the sandbox)
 *
 * Generated files are listed in .static-export.json and removed before each new export,
 * so nothing else in the project is ever deleted.
 *
 * Needs the WordPress server and therefore does not work in the static copy:
 * cart, checkout, account, search suggestions, filters/sorting, add to cart, forms.
 * The WordPress theme in medhub-theme/ remains the real frontend.
 */
import { mkdirSync, writeFileSync, copyFileSync, existsSync, rmSync, readFileSync, readdirSync, rmdirSync } from 'node:fs';
import { dirname, join, posix } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = join(dirname(fileURLToPath(import.meta.url)), '..', '..');
const WP = join(ROOT, 'sandbox', 'wp');
const BASE = 'http://localhost:8080';
const MANIFEST = join(ROOT, '.static-export.json');

const DYNAMIC = [/^\/shopping-cart\//, /^\/checkout\//, /^\/my-account\//, /^\/wp-admin\//, /^\/wp-login\.php/, /^\/wp-json\//, /\/feed\/$/, /^\/order-tracking\//, /^\/compare\//, /^\/yith-compare\//, /^\/payment-confirmation\//, /^\/comments\//, /^\/wp-sitemap/];
const ASSET_EXT = /\.(css|js|mjs|png|jpe?g|gif|webp|avif|svg|ico|woff2?|ttf|otf)$/i;

const pages = new Map(); // WordPress path -> html
const queue = [];
const seen = new Set();
const assets = new Set();

const isDynamic = (p) => DYNAMIC.some((re) => re.test(p));
const enqueue = (p) => {
	if (!p.startsWith('/') || seen.has(p) || isDynamic(p) || ASSET_EXT.test(p)) return;
	seen.add(p);
	queue.push(p);
};

function local(url) {
	try {
		const u = new URL(url.replace(/\\\//g, '/'), BASE);
		if (u.origin !== BASE) return null;
		return { path: decodeURIComponent(u.pathname), query: u.search, hash: u.hash };
	} catch {
		return null;
	}
}

/** WordPress URL path → output file (relative to the project root, posix). */
function outFile(p) {
	if (p === '/404/') return '404.html';
	const segs = p.split('/').filter(Boolean);
	if (!segs.length) return 'index.html';

	// ".../page/N" → "...-page-N"
	let suffix = '';
	if (segs.length >= 2 && segs[segs.length - 2] === 'page' && /^\d+$/.test(segs[segs.length - 1])) {
		suffix = '-page-' + segs.pop();
		segs.pop();
	}
	if (!segs.length) return 'index' + suffix + '.html';

	const safe = segs.map((s) => s.replace(/[^\p{L}\p{N}._-]+/gu, '-'));
	const name = safe.pop() + suffix + '.html';
	return [...safe, name].join('/');
}

/** Asset URL path → output file under assets/. */
const assetFile = (p) => 'assets' + p;

async function crawl() {
	enqueue('/');
	try {
		const index = await (await fetch(BASE + '/wp-sitemap.xml')).text();
		for (const sm of [...index.matchAll(/<loc>([^<]+)<\/loc>/g)].map((m) => m[1])) {
			const xml = await (await fetch(sm)).text();
			for (const loc of [...xml.matchAll(/<loc>([^<]+)<\/loc>/g)].map((m) => m[1])) {
				const l = local(loc);
				if (l) enqueue(l.path);
			}
		}
	} catch {
		console.warn('sitemap unavailable, crawling links only');
	}

	while (queue.length) {
		const p = queue.shift();
		const res = await fetch(BASE + p, { redirect: 'follow' });
		if (!res.ok) continue;
		const html = await res.text();
		pages.set(p, html);
		process.stdout.write(`\r  crawled ${pages.size} pages, ${queue.length} queued   `);

		for (const m of html.matchAll(/(?:href|src|action|data-full)=["']([^"']+)["']/g)) {
			const l = local(m[1]);
			if (!l) continue;
			if (ASSET_EXT.test(l.path)) assets.add(l.path);
			else if (!l.query) enqueue(l.path);
		}
	}
	pages.set('/404/', await (await fetch(BASE + '/__static-export-404__/')).text());
	process.stdout.write('\n');
}

/** Relative link from the page written at `fromFile` to a local target. */
function rel(fromFile, target) {
	let to;
	if (ASSET_EXT.test(target.path)) {
		to = assetFile(target.path);
	} else if (isDynamic(target.path)) {
		return '#';
	} else {
		const p = target.path.endsWith('/') ? target.path : target.path + '/';
		to = outFile(pages.has(p) ? p : '/404/');
	}
	let r = posix.relative(posix.dirname(fromFile), to);
	if (!r.startsWith('.')) r = './' + r;
	return r + (ASSET_EXT.test(target.path) ? '' : target.hash);
}

function rewrite(fromFile, html) {
	return (
		html
			.replace(/<script type="importmap"[\s\S]*?<\/script>/g, '')
			.replace(/<link rel="modulepreload"[^>]*>/g, '')
			.replace(/<script type="module"/g, '<script defer')
			.replace(/\s(?:srcset|sizes)="[^"]*"/g, '')
			.replace(/<link rel=['"](?:https:\/\/api\.w\.org\/|alternate|EditURI|shortlink|pingback|preconnect|dns-prefetch)['"][^>]*>/g, '')
			.replace(/((?:href|src|action|data-full|content|data-endpoint)=["'])([^"']*)(["'])/g, (all, a, url, z) => {
				if (!url.startsWith('/') && !/localhost:8080/.test(url)) return all;
				const l = local(url);
				if (!l) return all;
				if (a.startsWith('content') && !ASSET_EXT.test(l.path)) return all;
				if (l.query && !ASSET_EXT.test(l.path) && !/^\?product=/.test(l.query)) return a + '#' + z;
				return a + rel(fromFile, l) + z;
			})
			.replace(/url\((['"]?)(https?:\/\/localhost:8080[^'")]+)\1\)/g, (all, q, url) => {
				const l = local(url);
				return l ? `url(${q}${rel(fromFile, l)}${q})` : all;
			})
	);
}

const written = [];

function copyAsset(p) {
	const src = join(WP, ...p.split('/').filter(Boolean));
	if (!existsSync(src)) return false;
	const destRel = assetFile(p);
	const dest = join(ROOT, ...destRel.split('/'));
	mkdirSync(dirname(dest), { recursive: true });
	copyFileSync(src, dest);
	written.push(destRel);

	if (/\.css$/i.test(p)) {
		for (const m of readFileSync(src, 'utf8').matchAll(/url\((['"]?)([^'")]+)\1\)/g)) {
			if (/^(data:|https?:|#)/.test(m[2])) continue;
			const target = posix.normalize(posix.join(posix.dirname(p), m[2].split(/[?#]/)[0]));
			if (!assets.has(target)) {
				assets.add(target);
				copyAsset(target);
			}
		}
	}
	return true;
}

/** Remove the files written by the previous export (and folders left empty). */
function cleanPrevious() {
	if (!existsSync(MANIFEST)) return;
	const prev = JSON.parse(readFileSync(MANIFEST, 'utf8'));
	const dirs = new Set();
	for (const f of prev.files || []) {
		const abs = join(ROOT, ...f.split('/'));
		rmSync(abs, { force: true });
		let d = dirname(abs);
		while (d.length > ROOT.length) {
			dirs.add(d);
			d = dirname(d);
		}
	}
	[...dirs].sort((a, b) => b.length - a.length).forEach((d) => {
		try {
			if (existsSync(d) && !readdirSync(d).length) rmdirSync(d);
		} catch {}
	});
}

console.log('Exporting static HTML from', BASE);
await crawl();
cleanPrevious();

// Never overwrite project files: only paths that are free (or were ours) are written.
const PROTECTED = new Set(['package.json', 'package-lock.json', '.gitignore']);
for (const [p, html] of pages) {
	const file = outFile(p);
	const abs = join(ROOT, ...file.split('/'));
	const top = file.split('/')[0];
	if (PROTECTED.has(file) || ['medhub-theme', 'docs', 'design', 'tooling', 'sandbox', 'node_modules', 'backups', '.vscode', '.git'].includes(top) || existsSync(abs)) {
		console.warn(`  skipped ${file} (would overwrite a project file)`);
		continue;
	}
	mkdirSync(dirname(abs), { recursive: true });
	writeFileSync(abs, rewrite(file, html));
	written.push(file);
}

let copied = 0;
for (const a of [...assets]) if (copyAsset(a)) copied++;

writeFileSync(MANIFEST, JSON.stringify({ exported: new Date().toISOString(), note: 'Files generated by tooling/export/export-html.mjs. Removed and regenerated on each export.', files: written }, null, 1));
console.log(`  wrote ${pages.size} pages + ${copied} assets. Open index.html (VS Code: Go Live).`);
