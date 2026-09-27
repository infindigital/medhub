/**
 * On-page SEO checklist modelled on Rank Math's published tests
 * (https://rankmath.com/kb/score-100-in-tests/). Rank Math's own scoring engine runs only in the
 * editor, so this reports tests passed, not Rank Math's weighted number.
 *
 *   node tooling/seo/check.mjs <export.json> [plan.json]   (plan = apply planned values first)
 */
import { readFileSync, writeFileSync } from 'node:fs';

const [, , exportFile, planFile] = process.argv;
const items = JSON.parse(readFileSync(exportFile, 'utf8'));
if (planFile) {
	const plan = JSON.parse(readFileSync(planFile, 'utf8'));
	for (const it of items) {
		const v = plan[`${it.kind}:${it.id}`];
		if (!v) continue;
		for (const k of ["rm_title", "rm_desc", "kw", "robots"]) if (v[k] !== undefined) it[k] = v[k];
		if (v.content_lead) it.content = it.content.replace(/<p\b([^>]*)>/i, (m) => `${m}${v.content_lead} `);
		if (v.content_prepend) it.content = v.content_prepend + it.content;
		if (v.content_append) it.content += v.content_append;
		if (v.term_description) it.content = v.term_description;
		if (v.thumb_alt) it.thumb_alt = v.thumb_alt;
	}
}

const TEMPLATE = { post: '%title% | MedHub', page: '%title% | MedHub', product: '%title% | MedHub', product_cat: '%term% | MedHub', product_brand: '%term% | MedHub' };
const POWER = /\b(best|complete|essential|easy|simple|proven|trusted|expert|ultimate|guide|quick|free|new|top|affordable|reliable|safe|smart|official|genuine)\b/i;
const SENTIMENT = /\b(best|easy|simple|trusted|reliable|safe|comfortable|quiet|lightweight|affordable|better|smart|quality|genuine|complete|quick|free|fast|gentle|secure)\b/i;

const text = (html) => html.replace(/<(script|style)[\s\S]*?<\/\1>/gi, ' ').replace(/<!--[\s\S]*?-->/g, ' ').replace(/<[^>]+>/g, ' ').replace(/&nbsp;|&#160;/g, ' ').replace(/&amp;/g, '&').replace(/&[#\w]+;/g, ' ').replace(/\s+/g, ' ').trim();
const words = (s) => (s.match(/[\p{L}\p{N}][\p{L}\p{N}'’-]*/gu) || []);
const norm = (s) => words(s.toLowerCase()).join(' ');
const has = (hay, kw) => ` ${norm(hay)} `.includes(` ${norm(kw)} `);
const slugify = (s) => norm(s).replace(/\s+/g, '-');

const kwCount = {};
for (const it of items) if (it.kw) for (const k of it.kw.split(',')) kwCount[norm(k)] = (kwCount[norm(k)] || 0) + 1;

export function check(it) {
	const kw = (it.kw || '').split(',')[0].trim();
	const title = (it.rm_title || TEMPLATE[it.kind]).replace(/%title%|%term%/g, it.name).replace(/%sep%/g, '|').replace(/%sitename%/g, 'Medical Equipment Suppliers in Dubai | Buy Medical Supplies UAE').replace(/%page%/g, '').replace(/\s+/g, ' ').trim();
	const desc = it.rm_desc || '';
	const html = (it.content || '') + (it.kind === 'product' ? ' ' + (it.excerpt || '') : '');
	const body = text(html);
	const w = words(body);
	const first = w.slice(0, Math.max(30, Math.ceil(w.length * 0.1))).join(' ');
	const heads = [...html.matchAll(/<h[2-4][^>]*>([\s\S]*?)<\/h[2-4]>/gi)].map((m) => text(m[1]));
	const alts = [...html.matchAll(/<img\b[^>]*\balt="([^"]*)"/gi)].map((m) => m[1]).concat(it.thumb_alt || []);
	const links = [...html.matchAll(/<a\b[^>]*href="([^"]+)"/gi)].map((m) => m[1]);
	const internal = links.filter((h) => h.startsWith('/') || /medhub\.(ae|local)/.test(h));
	const external = links.filter((h) => /^https?:/.test(h) && !/medhub\.(ae|local)/.test(h));
	const paras = [...html.matchAll(/<p\b[^>]*>([\s\S]*?)<\/p>/gi)].map((m) => words(text(m[1])).length);
	const occurrences = kw ? (` ${norm(body)} `.split(` ${norm(kw)} `).length - 1) : 0;
	const density = w.length ? (occurrences * 100) / w.length : 0;
	const minWords = it.kind === 'product' ? 200 : it.kind.startsWith('product_') ? 100 : 600;
	const isTerm = it.kind.startsWith('product_');

	const t = {
		metaTitle: !!it.rm_title && title.length <= 60,
		metaDesc: desc.length >= 110 && desc.length <= 160,
		keywordSet: !!kw,
		keywordInTitle: !!kw && has(title, kw),
		keywordInMetaDescription: !!kw && has(desc, kw),
		keywordInPermalink: !!kw && (slugify(it.slug).includes(slugify(kw)) || words(kw).filter((x) => x.length > 2).every((x) => slugify(it.slug).includes(x.toLowerCase()))),
		keywordIn10Percent: !!kw && has(first, kw),
		keywordInContent: !!kw && has(body, kw),
		lengthContent: w.length >= minWords,
		keywordInSubheadings: !!kw && heads.some((h) => has(h, kw)),
		keywordInImageAlt: !!kw && alts.some((a) => has(a, kw)),
		keywordDensity: density >= 0.5 && density <= 2.5,
		lengthPermalink: (it.url || '').length <= 75,
		linksHasInternal: internal.length > 0,
		linksHasExternals: external.length > 0,
		keywordNotUsed: !!kw && kwCount[norm(kw)] === 1,
		titleStartWithKeyword: !!kw && has(title.slice(0, Math.ceil(title.length / 2) + kw.length), kw),
		titleHasNumber: /\d/.test(title),
		titleHasPowerWords: POWER.test(title),
		titleSentiment: SENTIMENT.test(title),
		contentHasShortParagraphs: paras.every((n) => n <= 120),
		contentHasAssets: /<img\b|<video\b|<iframe\b/i.test(html) || !!it.thumb,
	};
	if (isTerm) for (const k of ['keywordInImageAlt', 'contentHasAssets', 'linksHasExternals', 'keywordInSubheadings']) delete t[k];
	const keys = Object.keys(t);
	return { title, words: w.length, pass: keys.filter((k) => t[k]).length, total: keys.length, fails: keys.filter((k) => !t[k]) };
}

const rows = items.filter((it) => !(Array.isArray(it.robots) && it.robots.includes('noindex')));
const byKind = {};
const failCount = {};
for (const it of rows) {
	const r = check(it);
	it._check = r;
	const k = (byKind[it.kind] ||= { n: 0, pct: 0 });
	k.n++;
	k.pct += (r.pass / r.total) * 100;
	for (const f of r.fails) failCount[`${it.kind}:${f}`] = (failCount[`${it.kind}:${f}`] || 0) + 1;
}
for (const [k, v] of Object.entries(byKind)) console.log(`${k.padEnd(14)} ${String(v.n).padStart(4)} items, average ${Math.round(v.pct / v.n)}% of tests passed`);
console.log('\nMost common failures:');
for (const [k, v] of Object.entries(failCount).sort((a, b) => b[1] - a[1]).slice(0, 40)) console.log(`${String(v).padStart(4)}  ${k}`);
writeFileSync(exportFile.replace(/\.json$/, '') + (planFile ? '.after' : '.before') + '-check.json', JSON.stringify(rows.map((it) => ({ kind: it.kind, id: it.id, slug: it.slug, kw: it.kw, ...it._check })), null, 1));
