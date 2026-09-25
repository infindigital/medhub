/**
 * Live product suggestions from the same-origin WooCommerce Store API.
 * Budget: debounced 300 ms, minimum 3 characters, 6 results, in-flight requests
 * aborted, results cached per session. Submitting the form still runs the normal
 * WordPress/WooCommerce search.
 */
const cache = new Map();

const decode = (html) => {
	const el = document.createElement('textarea');
	el.innerHTML = html;
	return el.value;
};

const formatPrice = (prices) => {
	const value = Number(prices.price) / 10 ** prices.currency_minor_unit;
	if (!value) return '';
	const amount = value.toLocaleString('en-AE', {
		minimumFractionDigits: prices.currency_minor_unit,
		maximumFractionDigits: prices.currency_minor_unit,
	});
	return `${prices.currency_prefix}${amount}${prices.currency_suffix}`;
};

export function initSearch() {
	const form = document.querySelector('[data-search]');
	const input = document.querySelector('[data-search-input]');
	const results = document.querySelector('[data-search-results]');
	if (!form || !input || !results || !results.dataset.endpoint) return;

	let timer = 0;
	let controller = null;

	const render = (items, query) => {
		if (input.value.trim() !== query) return;

		if (!items.length) {
			results.innerHTML = `<p class="search-results__empty">${results.dataset.labelNone}</p>`;
			return;
		}

		const list = document.createElement('ul');
		list.className = 'search-results__list';

		items.forEach((item) => {
			const li = document.createElement('li');
			const a = document.createElement('a');
			a.className = 'search-results__item';
			a.href = item.permalink;

			const img = document.createElement('img');
			img.alt = '';
			img.width = 56;
			img.height = 56;
			if (item.images && item.images[0]) img.src = item.images[0].thumbnail;

			const name = document.createElement('span');
			name.className = 'search-results__name';
			name.textContent = decode(item.name);

			const price = document.createElement('span');
			price.className = 'search-results__price';
			const rental = (item.categories || []).some((c) => c.slug === 'medical-equipment-rental');
			price.textContent = formatPrice(item.prices) + (rental && Number(item.prices.price) ? ' / month' : '');

			a.append(img, name, price);
			li.append(a);
			list.append(li);
		});

		results.replaceChildren(list);
	};

	input.addEventListener('input', () => {
		clearTimeout(timer);
		const query = input.value.trim();

		if (query.length < 3) {
			if (controller) controller.abort();
			results.replaceChildren();
			return;
		}

		timer = setTimeout(async () => {
			if (cache.has(query)) {
				render(cache.get(query), query);
				return;
			}

			if (controller) controller.abort();
			controller = new AbortController();

			try {
				const url = new URL(results.dataset.endpoint);
				url.searchParams.set('search', query);
				url.searchParams.set('per_page', '6');
				const response = await fetch(url, { signal: controller.signal, credentials: 'same-origin' });
				if (!response.ok) return;
				const items = await response.json();
				cache.set(query, items);
				render(items, query);
			} catch (error) {
				if (error.name !== 'AbortError') results.replaceChildren();
			}
		}, 300);
	});
}
