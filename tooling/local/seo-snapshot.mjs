/**
 * SEO snapshot of the LOCAL site (http://medhub.local) for every URL in docs/url-inventory.csv.
 * Records status, title, meta description, canonical, robots, H1s and JSON-LD @types, so the
 * MedHub theme can be compared against the Elessi baseline.
 *   node tooling/local/seo-snapshot.mjs <out.json>
 */
import { readFileSync, writeFileSync } from 'node:fs';
const OUT = process.argv[2] || 'seo-snapshot.json';
const BASE = 'http://medhub.local';
const rows = readFileSync(new URL('../../docs/url-inventory.csv', import.meta.url), 'utf8').replace(/^\uFEFF/, '').trim().split(/\r?\n/).slice(1);
const urls = rows.map((r) => r.match(/^"[^"]*","https:\/\/medhub\.ae([^"]*)"/)[1]);
const pick = (re, h) => (h.match(re) || [])[1] || null;
const dec = (s) => s && s.replace(/&amp;/g, '&').replace(/&#0?39;|&#8217;/g, "'").replace(/&quot;/g, '"').replace(/&#8211;/g, '–').replace(/&#8212;/g, '—').replace(/&#038;/g, '&').replace(/<[^>]+>/g, '').replace(/\s+/g, ' ').trim();
async function one(path) {
	try {
		const r = await fetch(BASE + path, { redirect: 'manual' });
		const h = r.status === 200 ? await r.text() : '';
		const types = new Set();
		for (const m of h.matchAll(/<script[^>]*application\/ld\+json[^>]*>([\s\S]*?)<\/script>/g)) {
			for (const t of m[1].matchAll(/"@type":\s*(?:"([^"]+)"|\[([^\]]+)\])/g)) (t[1] || t[2].replace(/"/g, '')).split(',').forEach((x) => types.add(x.trim()));
		}
		return {
			path, status: r.status, location: r.headers.get('location')?.replace(BASE, '') || undefined,
			title: dec(pick(/<title[^>]*>([\s\S]*?)<\/title>/, h)),
			description: dec(pick(/<meta name="description" content="([^"]*)"/, h)),
			canonical: pick(/<link rel="canonical" href="([^"]*)"/, h)?.replace(BASE, ''),
			robots: pick(/<meta name=["']robots["'] content=["']([^"']*)["']/, h),
			h1: [...h.matchAll(/<h1[^>]*>([\s\S]*?)<\/h1>/g)].map((m) => dec(m[1])),
			schema: [...types].sort(),
		};
	} catch (e) { return { path, error: String(e) }; }
}
const out = []; let i = 0;
await Promise.all(Array.from({ length: Number(process.env.CONCURRENCY || 2) }, async () => { while (i < urls.length) { const u = urls[i++]; out.push(await one(u)); process.stdout.write(`\r${out.length}/${urls.length}`); } }));
out.sort((a, b) => urls.indexOf(a.path) - urls.indexOf(b.path));
writeFileSync(OUT, JSON.stringify(out, null, 1));
console.log('\nstatus counts', out.reduce((a, r) => ((a[r.status || 'err'] = (a[r.status || 'err'] || 0) + 1), a), {}));
