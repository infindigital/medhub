/**
 * Static HTML copy of the LOCAL WordPress site (http://medhub.local) for the Vercel design
 * preview. The WordPress theme in medhub-theme/ stays the real frontend.
 *
 *   node tooling/export/export-html.mjs        (LocalWP site "medhub" must be running)
 *
 * Output: preview/ (emptied and rewritten on every run), all links relative:
 *   index.html, <page>.html, product/<slug>.html, product-category/<slug>.html,
 *   brand/<slug>.html, category/<slug>.html, 404.html, assets/… (only files the pages use)
 *
 * Left out because they need the WordPress server: cart, checkout, account, order tracking,
 * compare, search, filters/sorting, add to cart and forms (their links point to "#").
 * Only public pages are fetched, as an anonymous visitor.
 */
import { mkdirSync, writeFileSync, copyFileSync, existsSync, rmSync, readFileSync } from 'node:fs';
import { dirname, join, posix } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = join(dirname(fileURLToPath(import.meta.url)), '..', '..');
const WP = process.env.MEDHUB_WP_ROOT || 'C:/Users/Infin Digital/Local Sites/medhub/app/public';
const BASE = 'http://medhub.local';
const OUT = join(ROOT, 'preview');

const DYNAMIC = [/^\/shopping-cart\//, /^\/cart\//, /^\/checkout\//, /^\/my-account\//, /^\/wp-admin\//, /^\/wp-login\.php/, /^\/wp-json\//, /\/feed\/$/, /^\/order-tracking\//, /^\/compare\//, /^\/yith-compare\//, /^\/payment-confirmation\//, /^\/comments\//, /^\/wp-comments-post\.php/, /sitemap/, /^\/locations\.kml/, /^\/author\//, /^\/wp-content\/uploads\/wc-logs/];
const ASSET_EXT = /\.(css|js|mjs|png|jpe?g|gif|webp|avif|svg|ico|woff2?|ttf|otf|pdf)$/i;

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
		const u = new URL(url.replace(/\\\//g, '/').replace(/&amp;/g, '&'), BASE);
		if (u.origin !== BASE) return null;
		return { path: decodeURIComponent(u.pathname), query: u.search, hash: u.hash };
	} catch {
		return null;
	}
}

/** WordPress URL path → output file (relative to preview/, posix). */
function outFile(p) {
	if (p === '/404/') return '404.html';
	const segs = p.split('/').filter(Boolean);
	if (!segs.length) return 'index.html';
	let suffix = '';
	if (segs.length >= 2 && segs[segs.length - 2] === 'page' && /^\d+$/.test(segs[segs.length - 1])) {
		suffix = '-page-' + segs.pop();
		segs.pop();
	}
	if (!segs.length) return 'index' + suffix + '.html';
	const safe = segs.map((s) => s.replace(/[^\p{L}\p{N}._-]+/gu, '-').replace(/^-+/, '') || 'page');
	return [...safe.slice(0, -1), safe.at(-1) + suffix + '.html'].join('/');
}

const assetFile = (p) => 'assets' + p;

const failed = [];

/** Fetch with retries: the local server can be slow or briefly overloaded. */
async function get(url, tries = 4) {
	for (let i = 1; ; i++) {
		try {
			const res = await fetch(url, { redirect: 'follow', signal: AbortSignal.timeout(90_000) });
			if (res.ok || res.status === 404 || i >= tries) return res;
		} catch (e) {
			if (i >= tries) throw e;
		}
		await new Promise((r) => setTimeout(r, 3000 * i));
	}
}

async function fetchPage(p) {
	let res;
	try {
		res = await get(BASE + encodeURI(p));
	} catch (e) {
		failed.push(`${p} (${e.name})`);
		return;
	}
	if (!res.ok) {
		if (res.status !== 404) failed.push(`${p} (${res.status})`);
		return;
	}
	const finalPath = local(res.url)?.path;
	if (finalPath && finalPath !== p && isDynamic(finalPath)) return;
	const html = await res.text();
	pages.set(p, html);
	for (const m of html.matchAll(/(?:href|src|action|data-full|data-src)=["']([^"']+)["']/g)) {
		const l = local(m[1]);
		if (!l) continue;
		if (ASSET_EXT.test(l.path)) assets.add(l.path);
		else if (!l.query) enqueue(l.path);
	}
	for (const m of html.matchAll(/url\((['"]?)([^'")]+)\1\)/g)) {
		const l = local(m[2]);
		if (l && ASSET_EXT.test(l.path)) assets.add(l.path);
	}
}

async function crawl() {
	enqueue('/');
	try {
		const index = await (await fetch(BASE + '/sitemap_index.xml')).text();
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
	const worker = async () => {
		while (queue.length) {
			await fetchPage(queue.shift());
			process.stdout.write(`\r  crawled ${pages.size} pages, ${queue.length} queued   `);
		}
	};
	// Links found while crawling refill the queue, so keep going until it stays empty.
	while (queue.length) await Promise.all(Array.from({ length: 2 }, worker));
	pages.set('/404/', await (await fetch(BASE + '/__static-export-404__/')).text());
	process.stdout.write('\n');
}

function rel(fromFile, target) {
	let to;
	if (ASSET_EXT.test(target.path)) to = assetFile(target.path);
	else if (isDynamic(target.path)) return '#';
	else {
		const p = target.path.endsWith('/') ? target.path : target.path + '/';
		to = outFile(pages.has(p) ? p : '/404/');
	}
	let r = posix.relative(posix.dirname(fromFile), to);
	if (!r.startsWith('.')) r = './' + r;
	return r + (ASSET_EXT.test(target.path) ? '' : target.hash);
}

/** JSON-LD without author/person nodes (the preview does not publish staff names or logins). */
function scrubSchema(html) {
	return html.replace(/(<script[^>]*application\/ld\+json[^>]*>)([\s\S]*?)(<\/script>)/g, (all, a, json, z) => {
		try {
			const strip = (v) => {
				if (Array.isArray(v)) return v.filter((x) => !(x && typeof x === "object" && x["@type"] === "Person")).map(strip);
				if (v && typeof v === "object") {
					const o = {};
					for (const [k, x] of Object.entries(v)) if (k !== "author") o[k] = strip(x);
					return o;
				}
				return v;
			};
			return a + JSON.stringify(strip(JSON.parse(json))) + z;
		} catch {
			return all;
		}
	});
}

function rewrite(fromFile, html) {
	return scrubSchema(html)
		.replace(/<meta name="twitter:label(\d)" content="Written by"\s*\/?>\s*<meta name="twitter:data\1" content="[^"]*"\s*\/?>/g, '')
		.replace(/<script type="importmap"[\s\S]*?<\/script>/g, '')
		.replace(/<link rel="modulepreload"[^>]*>/g, '')
		.replace(/<script type="module"/g, '<script defer')
		.replace(/\s(?:srcset|sizes)="[^"]*"/g, '')
		.replace(/<link rel=['"](?:https:\/\/api\.w\.org\/|alternate|EditURI|shortlink|pingback|preconnect|dns-prefetch)['"][^>]*>/g, '')
		.replace(/((?:href|src|action|data-full|data-src|content|data-endpoint)=["'])([^"']*)(["'])/g, (all, a, url, z) => {
			if (!url.startsWith('/') && !url.includes('medhub.local')) return all;
			const l = local(url);
			if (!l) return all;
			if (a.startsWith('content') && !ASSET_EXT.test(l.path)) return all;
			if (l.query && !ASSET_EXT.test(l.path)) return a + '#' + z;
			return a + rel(fromFile, l) + z;
		})
		.replace(/url\((['"]?)(https?:\/\/medhub\.local[^'")]+)\1\)/g, (all, q, url) => {
			const l = local(url);
			return l ? `url(${q}${rel(fromFile, l)}${q})` : all;
		})
		.replace(/http:\/\/medhub\.local/g, 'https://medhub.ae'); // remaining absolute URLs (schema, canonical)
}

let copied = 0;
function copyAsset(p) {
	const src = join(WP, ...p.split('/').filter(Boolean));
	if (!existsSync(src)) return;
	const dest = join(OUT, ...assetFile(p).split('/'));
	if (existsSync(dest)) return;
	mkdirSync(dirname(dest), { recursive: true });
	copyFileSync(src, dest);
	copied++;
	if (/\.css$/i.test(p)) {
		for (const m of readFileSync(src, 'utf8').matchAll(/url\((['"]?)([^'")]+)\1\)/g)) {
			if (/^(data:|https?:|#)/.test(m[2])) continue;
			copyAsset(posix.normalize(posix.join(posix.dirname(p), m[2].split(/[?#]/)[0])));
		}
	}
}

console.log('Exporting static HTML from', BASE);
await crawl();
rmSync(OUT, { recursive: true, force: true });
for (const [p, html] of pages) {
	const file = outFile(p);
	const abs = join(OUT, ...file.split('/'));
	mkdirSync(dirname(abs), { recursive: true });
	writeFileSync(abs, rewrite(file, html));
}
for (const a of assets) copyAsset(a);
writeFileSync(join(OUT, 'robots.txt'), 'User-agent: *\nDisallow: /\n');
console.log(`  preview/: ${pages.size} pages + ${copied} assets`);
if (failed.length) {
	console.error(`  ${failed.length} page(s) could not be fetched:\n    ${failed.join('\n    ')}`);
	process.exitCode = 1;
}
