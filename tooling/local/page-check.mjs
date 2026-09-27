/**
 * Quick functional snapshot of key local pages (status, H1, main text size, raw shortcodes,
 * builder markup) for before/after comparisons: node tooling/local/page-check.mjs <out.json>
 */
import { writeFileSync } from 'node:fs';
const BASE = 'http://medhub.local';
const paths = [
	'/', '/shop/', '/compare/', '/yith-compare/', '/shopping-cart/', '/order-tracking/', '/checkout/', '/my-account/',
	'/pulse-oximeter-dubai/', '/suction-machine-dubai/', '/cpap-masks-dubai/', '/bipap-in-dubai-modes-explained/',
	'/oxygen-concentrator-in-dubai-how-it-works/', '/enteral-feeding-pumps-in-dubai-medhub-uae-supplier/',
	'/device-on-rent/', '/inogen-portable-oxygen-concentrator/', '/devilbiss-blue-cpap/', '/compact-525-oxygen-concentrator/', '/vacuaide-portable-suction-unit/',
];
const strip = (h) => h.replace(/<script[\s\S]*?<\/script>|<style[\s\S]*?<\/style>/g, '').replace(/<[^>]+>/g, ' ').replace(/&[a-z#0-9]+;/g, ' ').replace(/\s+/g, ' ').trim();
const out = [];
for (const p of paths) {
	const r = await fetch(BASE + p, { redirect: 'manual', headers: { Accept: 'text/html' } });
	const h = r.status === 200 ? await r.text() : '';
	const main = (h.match(/<main[\s\S]*<\/main>/) || [h])[0];
	const text = strip(main);
	out.push({
		path: p, status: r.status, location: r.headers.get('location') || undefined,
		h1: [...h.matchAll(/<h1[^>]*>([\s\S]*?)<\/h1>/g)].map((m) => strip(m[1])),
		words: text.split(' ').length,
		rawShortcodes: [...new Set((text.match(/\[\/?[a-z][a-z0-9_-]+[^\]]*\]/g) || []).map((s) => s.slice(0, 30)))],
		elementor: (main.match(/elementor-widget|elementor-element/g) || []).length,
		wooForms: (main.match(/woocommerce-cart-form|woocommerce-checkout|woocommerce-form-login|track_order|woocommerce-MyAccount/g) || []).length,
		images: (main.match(/<img/g) || []).length,
	});
	process.stdout.write('.');
}
writeFileSync(process.argv[2], JSON.stringify(out, null, 1));
console.log('\n' + out.map((r) => `${r.status} ${r.path} words=${r.words} img=${r.images} el=${r.elementor} woo=${r.wooForms}${r.rawShortcodes.length ? ' RAW:' + r.rawShortcodes.join(',') : ''}`).join('\n'));
