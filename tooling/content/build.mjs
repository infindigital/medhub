/**
 * Content build (Option B: layout in the theme, copy in WordPress with built-in blocks).
 *
 *   node tooling/content/build.mjs [--write-config]
 *
 * Output:
 *   tooling/content/dist/page-<key>.html      block markup (built-in blocks only) for each page
 *   tooling/content/dist/category-<slug>.html HTML appended to a WooCommerce category description
 *   medhub-theme/config/pages.php             page layouts (only with --write-config: after the
 *                                             first run the config file is edited by hand)
 *
 * The HTML files are applied to the LOCAL copy by tooling/local/apply-content.php.
 */
import { mkdirSync, writeFileSync, existsSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { B, core } from './blocks.mjs';
import { pages as draftPages, categories } from './drafts.mjs';

const here = dirname(fileURLToPath(import.meta.url));
const dist = join(here, 'dist');
const configFile = join(here, '..', '..', 'medhub-theme', 'config', 'pages.php');
mkdirSync(dist, { recursive: true });

/* Homepage: same sections and copy as the approved design (former patterns/home.html). */
const home = {
	sections: [
		B.hero({
			eyebrow: 'Medical equipment · Deira, Dubai',
			heading: 'Medical equipment in Dubai, *chosen with care.*',
			lead: 'A medical equipment supplier in Dubai for oxygen, sleep and patient-care equipment from recognised brands, to buy or rent, with delivery across the UAE.',
			primaryLabel: 'Explore Medical Equipment',
			primaryUrl: '/shop/',
			secondaryLabel: 'Medical Equipment Rental',
			secondaryUrl: '/product-category/medical-equipment-rental/',
			showProducts: true,
		}),
		B.trust(),
		B.marquee({ source: 'departments' }),
		B.explorer({ eyebrow: 'Shop by need', heading: 'Find the right equipment, *faster.*', lead: 'Eight departments, from oxygen and sleep therapy to hospital beds and everyday medical supplies.' }),
		B.showcase({ eyebrow: 'In the catalogue', heading: 'Featured *equipment*', linkLabel: 'Shop all equipment' }),
		B.bento({
			eyebrow: 'Why MedHub',
			heading: 'A medical equipment supplier in Dubai *you can visit.*',
			cells: [
				{ cls: 'is-feature', h: 'Local, in Deira', p: 'A shop on Airport Road in Port Saeed, Deira, where you can see equipment and ask questions before you decide.' },
				{ h: 'Buy or rent', p: 'Buy outright, or rent selected equipment by the month when you only need it for a while.' },
				{ h: 'Recognised brands', p: 'Philips Respironics, ResMed, Inogen, Yuwell, Abbott and more, in one catalogue.' },
				{ cls: 'is-dark', h: 'Respiratory focus', p: 'CPAP, BiPAP, oxygen concentrators, suction and airway care, alongside patient-care supplies.' },
			],
		}),
		B.brands({ eyebrow: 'Brands', heading: 'Brands in *our range*', lead: 'Every brand below has products in the catalogue today.' }),
		B.rental({ eyebrow: 'Medical equipment rental', heading: 'Need it for weeks, *not years?*', lead: 'Rent oxygen concentrators, CPAP machines, hospital beds, suction units, feeding pumps and patient monitors by the month.', ctaLabel: 'See all rental equipment' }),
		B.switcher({ eyebrow: 'Sleep & respiratory care', heading: 'Compare by *equipment type.*', lead: 'Choose a category to see what it covers and what is available now.' }),
		B.service({ eyebrow: 'Dubai delivery & support', heading: 'From our shelves *to your door.*' }),
		B.posts({ eyebrow: 'Guides', heading: 'Know before *you buy.*', lead: 'Plain-language guides to oxygen therapy, sleep apnea equipment and patient care.' }),
		B.faq({
			eyebrow: 'FAQ',
			heading: 'Questions, *answered.*',
			lead: 'Ordering, delivery and rental at MedHub.',
			items: [
				['How much does delivery cost?', 'Standard delivery within the UAE costs AED 25. Orders are processed within 24 hours of payment confirmation and delivered within 2–3 business days.'],
				['Do you deliver outside the UAE?', 'Not at the moment. Delivery is available within the United Arab Emirates only.'],
				['Can I rent equipment instead of buying it?', 'Yes. Selected equipment, including oxygen concentrators, CPAP machines, hospital beds and patient monitors, is available on monthly rental. Rental prices are shown per month on each product.'],
				['How can I pay?', 'Orders are paid online by card on a secure payment page at checkout.'],
				['Do you sell medicines?', 'No. MedHub supplies medical equipment and supplies, not medicines.'],
			],
		}),
		B.cta({ eyebrow: 'Not sure what fits?', heading: 'Talk to MedHub *before* you buy.', text: 'Tell us what you need and we will help you compare options, whether you are buying or renting.', primaryLabel: 'Shop Medical Equipment', primaryUrl: '/shop/', secondaryLabel: 'Rent Equipment', secondaryUrl: '/product-category/medical-equipment-rental/' }),
	],
};

// D5: /faq/ is not created on the real site.
const { faq: _skipFaq, ...realPages } = draftPages;
const pages = { home, ...realPages };

const config = {};
for (const [key, page] of Object.entries(pages)) {
	const content = [];
	const layout = { hero: null, sections: [] };
	for (const part of page.sections) {
		if (part.hero) {
			const { heading, lead, ...rest } = part.hero;
			content.push(core.heading(heading, 1));
			if (lead) content.push(core.paragraph(lead));
			layout.hero = rest;
		} else if (part.content) {
			content.push(part.content);
			layout.sections.push({ type: 'content' });
		} else if (part.section) {
			layout.sections.push(part.section);
		}
	}
	writeFileSync(join(dist, `page-${key}.html`), content.join('\n\n') + '\n');
	config[key] = layout;
}

for (const [slug, cat] of Object.entries(categories)) {
	const html = cat.sections.map((s) => s.category).filter(Boolean).join('\n\n');
	writeFileSync(join(dist, `category-${slug}.html`), html + '\n');
}

/* config/pages.php */
const php = (v, ind = '\t') => {
	if (Array.isArray(v)) return v.length ? `array(\n${v.map((x) => ind + '\t' + php(x, ind + '\t')).join(',\n')},\n${ind})` : 'array()';
	if (v && typeof v === 'object') {
		const e = Object.entries(v).filter(([, x]) => x !== undefined && x !== null);
		// Flat entries (a section, the hero) on one line when short enough to read.
		if (e.every(([, x]) => typeof x !== 'object')) {
			const line = `array( ${e.map(([k, x]) => `'${k}' => ${php(x)}`).join(', ')} )`;
			if (line.length <= 150) return line;
		}
		return `array(\n${e.map(([k, x]) => `${ind}\t'${k}' => ${php(x, ind + '\t')}`).join(',\n')},\n${ind})`;
	}
	if (typeof v === 'string') return "'" + v.replace(/\\/g, '\\\\').replace(/'/g, "\\'") + "'";
	if (typeof v === 'boolean') return v ? 'true' : 'false';
	return String(v);
};

if (process.argv.includes('--write-config') || !existsSync(configFile)) {
	writeFileSync(
		configFile,
		`<?php
/**
 * Page layouts (Option B): which theme sections a page shows, in which order.
 *
 * The COPY of these pages (H1, intro, "how to choose" cards, link lists, FAQs) is edited in
 * WordPress with built-in blocks. This file only decides the layout around it:
 *
 *   'hero'     eyebrow, buttons and product visuals for the page header
 *              (showProducts: WooCommerce "Featured" products; categories: featured/newest from
 *              those product categories). The H1 and intro come from the page's first
 *              Heading (H1) and Paragraph blocks.
 *   'sections' in order. type "content" = the next block of the page content
 *              (e.g. a Group styled "MedHub: FAQ"); any other type is a dynamic section from
 *              template-parts/sections/ filled from WooCommerce/WordPress.
 *
 * Keys: "home" for the front page, otherwise the page slug (invisible characters ignored).
 * Pages without an entry use the default layout (hero + content + closing call to action).
 *
 * First generated by tooling/content/build.mjs; maintained by hand from here on.
 *
 * @package MedHub
 */

defined( 'ABSPATH' ) || exit;

return ${php(config, '')};
`
	);
	console.log('wrote', configFile);
}

console.log(`content: ${Object.keys(pages).length} pages, ${Object.keys(categories).length} categories → ${dist}`);
