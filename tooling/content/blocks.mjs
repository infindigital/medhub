/**
 * Draft helpers for tooling/content/build.mjs.
 *
 * Each call returns a descriptor:
 *   { content: '<block markup>' }  copy, written into WordPress with BUILT-IN blocks only
 *   { section: { type, ...args } } layout, a dynamic theme section listed in config/pages.php
 *   { hero: {...} }                split: H1 + intro go into content, the rest into config
 *   { category: '<html>' }         plain HTML for a WooCommerce category description
 *
 * Internal links are written as {{type:slug}} placeholders, and titles as {{label:type:slug}}.
 * tooling/local/apply-content.php resolves them against the real database (and drops links
 * whose target does not exist).
 */

const esc = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
/** "*word*" accent convention → <em> (the serif accent in the design). */
export const accent = (s) => esc(s).replace(/\*(.+?)\*/g, '<em>$1</em>');

const attrs = (o) => {
	const clean = Object.fromEntries(Object.entries(o).filter(([, v]) => v !== undefined && v !== ''));
	return Object.keys(clean).length ? ' ' + JSON.stringify(clean) : '';
};

export const core = {
	heading: (text, level = 2) =>
		`<!-- wp:heading${level === 2 ? '' : attrs({ level })} -->\n<h${level} class="wp-block-heading">${accent(text)}</h${level}>\n<!-- /wp:heading -->`,
	paragraph: (text, className) =>
		`<!-- wp:paragraph${attrs({ className })} -->\n<p${className ? ` class="${className}"` : ''}>${esc(text)}</p>\n<!-- /wp:paragraph -->`,
	group: (inner, className) =>
		`<!-- wp:group${attrs({ className })} -->\n<div class="wp-block-group${className ? ' ' + className : ''}">${inner}</div>\n<!-- /wp:group -->`,
	list: (itemsHtml) =>
		`<!-- wp:list -->\n<ul class="wp-block-list">${itemsHtml.map((li) => `<!-- wp:list-item -->\n<li>${li}</li>\n<!-- /wp:list-item -->`).join('\n\n')}</ul>\n<!-- /wp:list -->`,
	details: (q, a) =>
		`<!-- wp:details -->\n<details class="wp-block-details"><summary>${esc(q)}</summary><!-- wp:paragraph -->\n<p>${esc(a)}</p>\n<!-- /wp:paragraph --></details>\n<!-- /wp:details -->`,
};

/** "type:slug | Label" → <a href="{{type:slug}}">Label</a> */
export const linkHtml = (spec) => {
	const [target, label] = spec.split('|').map((s) => s.trim());
	return `<a href="{{${target}}}">${label ? esc(label) : `{{label:${target}}}`}</a>`;
};

const head = ({ eyebrow, heading, lead }) =>
	[eyebrow ? core.paragraph(eyebrow, 'eyebrow') : '', heading ? core.heading(heading) : '', lead ? core.paragraph(lead) : ''].filter(Boolean);

const section = (type) => (args = {}) => ({ section: { type, ...args } });

export const B = {
	hero: (a) => ({ hero: a }),
	bento: ({ cells, layout, ...a }) => ({
		content: core.group(
			[
				...head(a),
				...cells.map(({ h, p, list, cls }) =>
					core.group([core.heading(h, 3), p ? core.paragraph(p) : '', list ? core.list(list.map(esc)) : ''].filter(Boolean).join('\n\n'), cls)
				),
			].join('\n\n'),
			layout === 'cards' ? 'is-style-medhub-cards' : 'is-style-medhub-bento'
		),
		category: [a.heading ? `<h2>${accent(a.heading)}</h2>` : '', ...cells.map(({ h, p, list }) => `<h3>${accent(h)}</h3>` + (p ? `<p>${esc(p)}</p>` : '') + (list ? `<ul>${list.map((i) => `<li>${esc(i)}</li>`).join('')}</ul>` : ''))].join('\n'),
	}),
	links: ({ items, ...a }) => ({
		content: core.group([...head(a), core.list(items.map(linkHtml))].join('\n\n'), 'is-style-medhub-links'),
		category: (a.heading ? `<h2>${accent(a.heading)}</h2>\n` : '') + `<ul>${items.map((i) => `<li>${linkHtml(i)}</li>`).join('')}</ul>`,
	}),
	faq: ({ items, ...a }) => ({
		content: core.group([...head(a), ...items.map(([q, ans]) => core.details(q, ans))].join('\n\n'), 'is-style-medhub-faq'),
		category: (a.heading ? `<h2>${accent(a.heading)}</h2>\n` : '') + items.map(([q, ans]) => `<details><summary>${esc(q)}</summary><p>${esc(ans)}</p></details>`).join('\n'),
	}),
	// Dynamic sections: layout lives in the theme (config/pages.php), data comes from WooCommerce/WordPress.
	trust: section('trust'),
	marquee: section('marquee'),
	explorer: section('explorer'),
	showcase: section('showcase'),
	products: section('products'),
	brands: section('brands'),
	rental: section('rental'),
	switcher: section('switcher'),
	service: section('service'),
	posts: section('posts'),
	contact: section('contact'),
	cta: section('cta'),
};
