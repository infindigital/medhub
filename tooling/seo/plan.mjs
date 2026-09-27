/**
 * Builds tooling/seo/plan.json: Rank Math title, description and focus keyword for every
 * indexable page, post, product, product category and brand, plus the content additions that
 * help the on-page tests (focus keyword in a heading / the opening text, internal links,
 * image alt text).
 *
 *   tooling/local/wp.sh eval-file tooling/seo/export.php tmp/seo-export.json
 *   node tooling/seo/plan.mjs tmp/seo-export.json
 *   node tooling/seo/check.mjs tmp/seo-export.json tooling/seo/plan.json
 *   tooling/local/wp.sh eval-file tooling/local/apply-seo-plan.php apply|status|rollback
 *
 * Generated text only restates facts from the database (product name, category, brand, the
 * store's delivery policy). Hand-written entries are in PAGES below.
 */
import { readFileSync, writeFileSync } from 'node:fs';

const items = JSON.parse(readFileSync(process.argv[2] || 'tmp/seo-export.json', 'utf8'));
const plan = {};
const put = (it, v) => (plan[`${it.kind}:${it.id}`] = v);
const dec = (s) => String(s || '').replace(/&amp;/g, '&').replace(/&#0?39;|&#8217;/g, "'").replace(/\s+/g, ' ').trim();
const esc = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
const text = (html) => dec(String(html || '').replace(/<[^>]+>/g, ' ').replace(/&nbsp;/g, ' ').replace(/&[#\w]+;/g, ' '));
// Whole sentences only: trailing sentences are dropped until it fits.
const fit = (s, max = 160) => {
	const parts = s.match(/[^.!?]+[.!?]+(\s|$)/g) || [s];
	let out = '';
	for (const part of parts) if ((out + part).trim().length <= max) out += part;
	return (out || parts[0]).trim();
};
const pad = (d) => {
	for (const extra of [' Delivery across the UAE.', ' Ask MedHub in Deira for advice before you buy.', ' Order online from MedHub.']) {
		if (d.length < 120 && !d.toLowerCase().includes(extra.trim().toLowerCase().replace(/\.$/, '')) && (d + extra).length <= 160) d += extra;
	}
	return d;
};
const firstSentence = (s) => (text(s).match(/^.{40,200}?[.!?](\s|$)/) || [''])[0].trim();
const STOP = /^(and|&|of|the|for|with|a|an|in|to|by|-|–)$/i;

const termUrl = {};
for (const t of items.filter((i) => i.kind === 'product_cat' || i.kind === 'product_brand')) termUrl[`${t.kind}:${dec(t.name)}`] = t.url;

/* ---------------- Products ---------------- */
const cleanName = (n) =>
	dec(n)
		.replace(/^buy\s+/i, '')
		.replace(/\s+in\s+(dubai|uae)\b.*$/i, '')
		.replace(/\s*\|.*$/, '')
		.replace(/\s+online$/i, '')
		.trim();
const shortName = (s, n = 7) => {
	const cut = s.split(/\s+(?:with|for|\+|–|-)\s+|\s*[(,]/)[0].trim();
	let w = cut.split(/\s+/);
	if (w.length > 8) w = w.slice(0, n);
	while (w.length > 2 && STOP.test(w.at(-1))) w.pop();
	return w.join(' ');
};

const products = items.filter((i) => i.kind === 'product');
const used = new Map();
const kwOf = new Map();
for (const p of products) {
	const s = cleanName(p.name);
	let kw = shortName(s);
	const all = s.replace(/\s*\(.*?\)/g, '').split(/\s+/);
	for (let n = kw.split(' ').length + 1; used.has(kw.toLowerCase()) && n <= all.length; n++) kw = all.slice(0, n).join(' ').replace(/[,(]+$/, '');
	// Short enough to sit in a 60-character title with " in Dubai | MedHub".
	let kwWords = kw.split(' ');
	while (kwWords.length > 2 && (kwWords.join(' ').length > 51 || STOP.test(kwWords.at(-1)))) kwWords.pop();
	kw = kwWords.join(' ');
	if (p.rental) {
		const r = shortName(s).split(' ');
		while (r.length > 2 && r.join(' ').length > 44) r.pop();
		kw = `${r.join(' ')} Rental`;
	}
	// Size/pack variants: add the name's last word (e.g. "3.5PEF", "Large") to keep keywords unique.
	if (used.has(kw.toLowerCase())) {
		const last = all.filter((x) => !kw.toLowerCase().split(' ').includes(x.toLowerCase())).at(-1);
		if (last) kw = `${kw} ${last}`;
	}
	used.set(kw.toLowerCase(), p.id);
	kwOf.set(p.id, kw);
}

for (const p of products) {
	const s = cleanName(p.name);
	const kw = kwOf.get(p.id);
	const plain = s.replace(/\s*\(.*?\)/g, '').trim();
	const cat = dec(p.cats?.[0] || '');
	const brand = dec(p.brands?.[0] || '');

	const title = (p.rental ? [`${kw} in Dubai | MedHub`, `${kw} | MedHub`] : [`${plain} in Dubai | MedHub`, `${plain} | MedHub`, `${kw} in Dubai | MedHub`, `${kw} | MedHub`]).find((t) => t.length <= 60) || `${kw} | MedHub`;

	let desc = p.rental
		? `${kw} in Dubai: rent the ${plain} from MedHub by the month. Ask MedHub about rental terms and availability.`
		: `Buy the ${plain.length > 70 ? kw : plain} in Dubai from MedHub${brand ? ` (${brand})` : ''}.${cat ? ` From our ${cat} range, with delivery across the UAE.` : ' Delivery across the UAE.'}`;
	if (desc.length < 120) {
		const extra = firstSentence(p.excerpt) || firstSentence(p.content);
		if (extra) desc = `${desc} ${extra}`;
	}
	desc = pad(fit(desc));

	const heading = p.rental ? kw : plain;
	const links = [];
	if (cat && termUrl[`product_cat:${cat}`]) links.push(`<a href="${termUrl[`product_cat:${cat}`]}">${esc(cat)}</a>`);
	if (brand && termUrl[`product_brand:${brand}`]) links.push(`<a href="${termUrl[`product_brand:${brand}`]}">${esc(brand)} products</a>`);
	const closing = p.rental
		? `<p>The ${esc(kw)} is available from MedHub in Dubai by the month. See all <a href="/product-category/medical-equipment-rental/">medical equipment rental</a> options, or <a href="/contact-us-medhub/">contact MedHub</a> about rental terms.</p>`
		: `<p>Order the ${esc(kw)} from MedHub in Dubai, with delivery across the UAE.${links.length ? ` Browse more ${links.join(' and ')},` : ''} or <a href="/contact-us-medhub/">contact MedHub</a> for advice before you buy.</p>`;

	put(p, {
		rm_title: title,
		rm_desc: desc,
		kw,
		content_prepend: `<h2>${esc(heading)}</h2>\n`,
		content_append: `\n${closing}`,
		thumb_alt: p.thumb_alt ? undefined : plain,
	});
}

/* ---------------- Brands ---------------- */
const listOf = (a) => (a.length > 1 ? `${a.slice(0, -1).join(', ')} and ${a.at(-1)}` : a[0] || '');
for (const b of items.filter((i) => i.kind === 'product_brand')) {
	const name = dec(b.name);
	if (!b.count) {
		put(b, { robots: ['noindex', 'follow'], rm_title: `${name} | MedHub` });
		continue;
	}
	const cats = (b.product_cats || []).map((c) => dec(c).replace(/\s+in Dubai$/i, '')).filter((c) => c !== 'Uncategorized').slice(0, 3);
	const prods = (b.products || []).map((n) => cleanName(n).replace(/\s*\(.*?\)/g, '')).slice(0, 2);
	put(b, {
		kw: name,
		rm_title: [`${name} Products in Dubai | MedHub`, `${name} in Dubai | MedHub`, `${name} | MedHub`].find((t) => t.length <= 60),
		rm_desc: pad(fit(`Shop ${name} products in Dubai at MedHub${cats.length ? `, including ${listOf(cats)}` : ''}. Delivery across the UAE, or visit our shop in Deira.`)),
		term_description: b.content
			? undefined
			: `<p>MedHub stocks ${esc(name)} products in Dubai${cats.length ? ` across our ${esc(listOf(cats))} ${cats.length > 1 ? 'ranges' : 'range'}` : ''}${prods.length ? `, including the ${prods.map(esc).join(' and the ')}` : ''}. Order online with delivery across the UAE, or <a href="/contact-us-medhub/">contact MedHub</a> for advice before you buy.</p>`,
	});
}

/* ---------------- Product categories without meta ---------------- */
for (const c of items.filter((i) => i.kind === 'product_cat')) {
	const name = dec(c.name);
	if (c.slug === 'uncategorized') {
		put(c, { robots: ['noindex', 'follow'], rm_title: 'Uncategorized | MedHub' });
		continue;
	}
	if (c.rm_title && c.rm_desc && c.kw) continue;
	const prods = (c.products || []).map((n) => cleanName(n).replace(/\s*\(.*?\)/g, '')).slice(0, 2);
	put(c, {
		kw: name.toLowerCase(),
		rm_title: [`${name} in Dubai | MedHub`, `${name} | MedHub`].find((t) => t.length <= 60),
		rm_desc: [2, 1, 0].map((n) => `Shop ${name.toLowerCase()} in Dubai at MedHub${n ? `, including the ${prods.slice(0, n).join(' and the ')}` : ''}. Order online with delivery across the UAE.`).find((d) => d.length <= 160).replace(/^(.*)$/, (d) => pad(d)),
		term_description: c.content
			? undefined
			: `<p>MedHub supplies ${esc(name.toLowerCase())} in Dubai${prods.length ? `, including the ${prods.map(esc).join(' and the ')}` : ''}. Order online with delivery across the UAE, or <a href="/contact-us-medhub/">contact MedHub</a> for advice.</p>`,
	});
}
// Titles that still use %sitename% (the long site name).
for (const it of items) if (/%sitename%/.test(it.rm_title) && !plan[`${it.kind}:${it.id}`]) put(it, { rm_title: it.rm_title.replace(/\s*%sep%\s*%sitename%/, ' | MedHub') });

/* ---------------- Pages and posts (hand-written) ---------------- */
const NHLBI_SLEEP = '<a href="https://www.nhlbi.nih.gov/health/sleep-apnea" target="_blank" rel="noopener">sleep apnea overview from the US National Heart, Lung, and Blood Institute</a>';
const NHLBI_OXYGEN = '<a href="https://www.nhlbi.nih.gov/health/oxygen-therapy" target="_blank" rel="noopener">oxygen therapy overview from the US National Heart, Lung, and Blood Institute</a>';
const FDA_INFUSION = '<a href="https://www.fda.gov/medical-devices/general-hospital-devices-and-supplies/infusion-pumps" target="_blank" rel="noopener">US FDA information on infusion pumps</a>';
const section = (h, p) => `\n\n<!-- wp:heading -->\n<h2 class="wp-block-heading">${h}</h2>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>${p}</p>\n<!-- /wp:paragraph -->`;

const PAGES = {
	// Policies: focus keyword = the phrase the page is actually about. Content untouched.
	5650: { kw: 'delivery policy' },
	5656: { kw: 'refund and return policy' },
	4093: { kw: 'return policy' },
	5658: { kw: 'cancellation policy' },
	5646: { kw: 'privacy policy' },
	5648: { kw: 'cookie policy' },
	5644: { kw: 'terms & conditions' },
	4091: { kw: 'conditions of sale' },
	5652: { kw: 'multiple shipments policy', rm_desc: "Our multiple shipments policy: multiple bookings, orders or shipments on medhub.ae may appear as separate postings on the cardholder's monthly statement." },
	3170: { kw: 'contact MedHub' },
	8: { kw: 'shop medical equipment' },
	4307: { kw: 'device on rent', rm_title: 'Device on Rent in Dubai | MedHub', rm_desc: 'Device on rent in Dubai from MedHub: the Inogen portable oxygen concentrator, DeVilbiss BLUE CPAP, Compact 525 and VacuAide portable suction unit.' },
	4325: { kw: 'sleep apnea machine in Dubai' }, // the old keyword started with an invisible U+2060 character
	3806: { rm_desc: 'MedHub is a medical equipment supplier in Dubai for CPAP, BiPAP, oxygen concentrators and patient-care equipment, to buy or rent with UAE delivery.' },

	6063: {
		kw: 'medical equipment suppliers in Dubai',
		rm_desc: 'How to choose between medical equipment suppliers in Dubai: oxygen machines, CPAP, BiPAP and ventilator support explained for home respiratory care.',
		append: section('Shop respiratory care equipment at MedHub', `MedHub is one of the medical equipment suppliers in Dubai with a shop you can visit in Deira. Browse <a href="/product-category/oxygen-concentrator/">oxygen therapy</a>, <a href="/product-category/cpap/">CPAP</a> and <a href="/product-category/bipap/">BiPAP machines</a>, see what is available for <a href="/product-category/medical-equipment-rental/">monthly rental</a>, or <a href="/contact-us-medhub/">contact MedHub</a> for advice. For background on home oxygen, see this ${NHLBI_OXYGEN}.`),
	},
	6065: {
		kw: 'sleep apnea treatment',
		lead: 'CPAP therapy is a common sleep apnea treatment, and this guide explains how it works in practice.',
		append: section('Starting sleep apnea treatment with MedHub', `When you are ready to start sleep apnea treatment, compare <a href="/cpap-in-dubai/">CPAP machines in Dubai</a>, browse <a href="/product-category/auto-cpap-machine/">auto CPAP machines</a>, <a href="/product-category/buy-cpap-bipap-masks/">CPAP masks</a> and <a href="/product-category/travel-cpap-machine/">travel CPAP</a>, or <a href="/contact-us-medhub/">contact MedHub</a> with your prescription. For medical background, read the ${NHLBI_SLEEP}.`),
	},
	6067: {
		kw: "portable oxygen machine buyer's guide",
		rm_desc: "A portable oxygen machine buyer's guide for Dubai: how portable concentrators work, the main types and brands, and what to check before you buy or rent.",
		lead: "This portable oxygen machine buyer's guide is for anyone in Dubai choosing a portable concentrator for the first time.",
		append: section("After this portable oxygen machine buyer's guide", `Compare models on our <a href="/portable-oxygen-machine-in-dubai/">portable oxygen machines in Dubai</a> page, browse <a href="/product-category/portable-oxygen-concentrator/">portable oxygen concentrators</a> and <a href="/product-category/oxygen-concentrator-accessories/">accessories</a>, or see <a href="/product-category/medical-equipment-rental/">rental options</a>. For medical background, read the ${NHLBI_OXYGEN}.`),
	},
	6069: {
		kw: 'BiPAP vs CPAP',
		rm_desc: 'BiPAP vs CPAP explained: what BiPAP is, who needs it, machine types, masks, prices and insurance cover for BiPAP therapy in Dubai.',
		append: section('BiPAP vs CPAP: choosing with MedHub', `Once you know whether BiPAP or CPAP suits your prescription, compare <a href="/bipap-in-dubai/">BiPAP machines in Dubai</a>, browse <a href="/product-category/auto-bipap-machine/">auto BiPAP machines</a> and <a href="/product-category/buy-cpap-bipap-masks/">masks</a>, or <a href="/contact-us-medhub/">contact MedHub</a>. For medical background, read the ${NHLBI_SLEEP}.`),
	},
	6230: {
		rm_desc: 'Travel CPAP machine in Dubai guide: how travel units differ from standard CPAP, using one on a plane, prices, accessories and renting a travel CPAP.',
		lead: 'Choosing a travel CPAP machine in Dubai starts with how and where you travel.',
		append: section('Find a travel CPAP machine in Dubai', `See the <a href="/product-category/travel-cpap-machine/">travel CPAP machines</a> MedHub stocks, compare <a href="/cpap-in-dubai/">CPAP machines in Dubai</a>, add <a href="/product-category/cpap-bipap-accessories/">CPAP accessories</a> for your trip, or <a href="/contact-us-medhub/">contact MedHub</a>. For medical background, read the ${NHLBI_SLEEP}.`),
	},
	6233: {
		rm_desc: 'Choosing an infusion pump in Dubai: the types available, common uses at home, accessories, and safety and hygiene for IV therapy at home.',
		lead: 'If you need an infusion pump in Dubai for home or clinical use, this guide explains the options.',
		append: section('Getting an infusion pump in Dubai from MedHub', `See the <a href="/product-category/infusion-pump/">infusion pumps</a> MedHub supplies, check <a href="/product-category/medical-equipment-rental/">rental options</a> and <a href="/product-category/hygiene-and-infection-control-products/">hygiene and infection control products</a>, or <a href="/contact-us-medhub/">contact MedHub</a>. For regulatory background, see the ${FDA_INFUSION}.`),
	},
	6237: {
		lead: 'Looking for a pulse oximeter in Dubai for home use? Start here.',
		append: section('Buying a pulse oximeter in Dubai from MedHub', `Browse <a href="/product-category/pulse-oximeter/">pulse oximeters</a>, other <a href="/product-category/health-monitoring-devices/">health monitoring devices</a> and <a href="/product-category/patient-monitor/">patient monitors</a>, or <a href="/contact-us-medhub/">contact MedHub</a> for advice.`),
	},
	6240: {
		lead: 'Looking for a suction machine in Dubai for home care? Start here.',
		append: section('Buying or renting a suction machine in Dubai', `Browse <a href="/product-category/suction-machine/">suction machines</a>, <a href="/product-category/tracheostomy-care-products-in-dubai/">tracheostomy care products</a> and <a href="/product-category/enteral-feeding-pump/">enteral feeding pumps</a>, see <a href="/product-category/medical-equipment-rental/">rental options</a>, or <a href="/contact-us-medhub/">contact MedHub</a>.`),
	},
	6570: {
		kw: 'BiPAP modes',
		rm_title: 'BiPAP Modes Explained: 4 Modes, Quick Guide | MedHub',
		rm_desc: 'BiPAP modes explained: what S, T, ST and AVAPS mean on your machine and how each mode works, with next steps for BiPAP in Dubai.',
		lead: 'BiPAP modes decide how your machine delivers each breath.',
		append: section('BiPAP modes and your next step', `Compare <a href="/bipap-in-dubai/">BiPAP machines in Dubai</a> and <a href="/product-category/bipap/">BiPAP models</a>, or <a href="/contact-us-medhub/">contact MedHub</a> with your prescription. For medical background, read the ${NHLBI_SLEEP}.`),
	},
	6600: {
		kw: 'how an oxygen concentrator works',
		rm_title: 'How an Oxygen Concentrator Works: 8 Simple Steps | MedHub',
		rm_desc: 'How an oxygen concentrator works, step by step: pressure swing adsorption explained simply, and what it means when choosing one in Dubai.',
		lead: 'Here is how an oxygen concentrator works, in plain language.',
		append: section('Now you know how an oxygen concentrator works', `Compare <a href="/oxygen-concentrator-in-dubai/">oxygen concentrators in Dubai</a>, browse <a href="/product-category/buy-oxygen-concentrator/">home concentrators</a> and <a href="/product-category/oxygen-concentrator-accessories/">accessories</a>, or see <a href="/product-category/medical-equipment-rental/">rental options</a>. For medical background, read the ${NHLBI_OXYGEN}.`),
	},
};
for (const it of items.filter((i) => i.kind === 'page' || i.kind === 'post')) {
	const e = PAGES[it.id];
	if (!e) continue;
	put(it, { kw: e.kw, rm_title: e.rm_title, rm_desc: e.rm_desc, content_lead: e.lead, content_append: e.append, thumb_alt: e.kw && it.thumb && !new RegExp(e.kw.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'i').test(it.thumb_alt) ? dec(it.name) : undefined });
}

for (const v of Object.values(plan)) for (const k of Object.keys(v)) if (v[k] === undefined) delete v[k];
writeFileSync(new URL('./plan.json', import.meta.url), JSON.stringify(plan, null, 1));
const count = {};
for (const k of Object.keys(plan)) count[k.split(':')[0]] = (count[k.split(':')[0]] || 0) + 1;
console.log('plan.json:', count);
