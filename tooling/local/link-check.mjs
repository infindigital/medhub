/**
 * Internal link check on the LOCAL copy: collects every internal link from key pages and
 * reports links that do not return 200.   node tooling/local/link-check.mjs [out.json]
 */
import { writeFileSync } from 'node:fs';

const BASE = 'http://medhub.local';
const pages = [
	'/', '/shop/', '/medical-equipment/', '/contact-us-medhub/', '/cpap-in-dubai/', '/bipap-in-dubai/',
	'/oxygen-concentrator-in-dubai/', '/portable-oxygen-machine-in-dubai/', '/oxygen-machine-in-dubai/',
	'/%e2%81%a0sleep-apnea-machine-in-dubai/', '/devilbiss-service-centre-in-dubai/',
	'/product-category/portable-oxygen-concentrator/', '/product-category/medical-equipment-rental/', '/product-category/hospital-furniture/',
	'/product-category/suction-machine/', '/product-category/enteral-feeding-pump/', '/product-category/pulse-oximeter/',
	'/product-category/patient-monitor/', '/product-category/medical-disposables/', '/product-category/buy-cpap-bipap-masks/',
	'/product-category/cpap-bipap-accessories/', '/product-category/travel-cpap-machine/',
	'/cpap-masks-dubai/', '/bipap-in-dubai-modes-explained/', '/oxygen-concentrator-in-dubai-how-it-works/',
	'/enteral-feeding-pumps-in-dubai-medhub-uae-supplier/', '/pulse-oximeter-dubai/', '/suction-machine-dubai/',
	'/product/inogen-rove-6-portable-oxygen-concentrator/',
];

const links = new Map(); // url -> first page seen on
for (const page of pages) {
	const html = await (await fetch(BASE + page)).text();
	const main = (html.match(/<main[\s\S]*<\/main>/) || [html])[0];
	for (const m of main.matchAll(/href="([^"#]+)/g)) {
		let url;
		try { url = new URL(m[1].replace(/&amp;/g, '&'), BASE + page); } catch { continue; }
		if (!/^medhub\.(local|ae)$/.test(url.hostname) || url.search || /\.(jpe?g|png|webp|pdf)$/i.test(url.pathname)) continue;
		const key = url.pathname;
		if (!links.has(key)) links.set(key, { from: page, live: url.hostname === 'medhub.ae' });
	}
	process.stdout.write('.');
}

const results = [];
for (const [path, info] of links) {
	const r = await fetch(BASE + path, { redirect: 'manual' });
	results.push({ path, status: r.status, location: r.headers.get('location') || undefined, ...info });
	process.stdout.write(r.status === 200 ? '' : '!');
}
writeFileSync(process.argv[2] || 'link-check.json', JSON.stringify(results, null, 1));
const bad = results.filter((r) => r.status !== 200);
console.log(`\n${links.size} unique internal links from ${pages.length} pages; not 200: ${bad.length}`);
for (const b of bad) console.log(`  ${b.status} ${b.path}${b.location ? ' → ' + b.location : ''}  (from ${b.from}${b.live ? ', absolute medhub.ae link' : ''})`);
console.log(`absolute https://medhub.ae links in content (fine on production): ${results.filter((r) => r.live).length}`);
