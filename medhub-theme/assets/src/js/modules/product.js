/**
 * Product page enhancements:
 * - gallery: thumbnails scroll the native slide track; current thumb follows swipes;
 *   full-size image in a <dialog> lightbox
 * - quantity: −/+ buttons around WooCommerce's own number input
 * - sticky buy bar (mobile): shown while the main buy button is off-screen;
 *   its button clicks the real add-to-cart button (or follows the quote link)
 */
export function initProduct() {
	initGallery();
	initQuantity();
	initStickyBuy();
}

function initGallery() {
	const gallery = document.querySelector('[data-gallery]');
	if (!gallery) return;

	const track = gallery.querySelector('[data-gallery-track]');
	const slides = [...gallery.querySelectorAll('.gallery__slide')];
	const thumbs = [...gallery.querySelectorAll('[data-gallery-thumb]')];
	const lightbox = gallery.querySelector('[data-gallery-lightbox]');
	const lightboxImg = gallery.querySelector('[data-gallery-lightbox-img]');
	const reduce = window.matchMedia('(prefers-reduced-motion: reduce)');
	let current = 0;

	const setCurrent = (index) => {
		current = index;
		thumbs.forEach((t, i) => (i === index ? t.setAttribute('aria-current', 'true') : t.removeAttribute('aria-current')));
	};

	thumbs.forEach((thumb, index) => {
		thumb.addEventListener('click', () => {
			track.scrollTo({ left: slides[index].offsetLeft, behavior: reduce.matches ? 'auto' : 'smooth' });
			setCurrent(index);
		});
	});

	if ('IntersectionObserver' in window && slides.length > 1) {
		const io = new IntersectionObserver(
			(entries) => entries.forEach((e) => e.isIntersecting && setCurrent(slides.indexOf(e.target))),
			{ root: track, threshold: 0.6 }
		);
		slides.forEach((s) => io.observe(s));
	}

	const open = gallery.querySelector('[data-gallery-open]');
	if (open && lightbox && lightboxImg) {
		open.addEventListener('click', () => {
			const img = slides[current]?.querySelector('img');
			if (!img) return;
			lightboxImg.src = img.dataset.full || img.currentSrc || img.src;
			lightboxImg.alt = img.alt;
			lightbox.showModal();
		});
	}
}

function initQuantity() {
	document.querySelectorAll('.buy-box .quantity').forEach((wrap) => {
		const input = wrap.querySelector('input.qty');
		if (!input || input.type === 'hidden' || wrap.querySelector('.qty-btn')) return;

		const make = (dir, label, icon) => {
			const b = document.createElement('button');
			b.type = 'button';
			b.className = 'qty-btn';
			b.setAttribute('aria-label', label);
			b.innerHTML = `<svg class="icon" aria-hidden="true"><use href="#i-${icon}"></use></svg>`;
			b.addEventListener('click', () => {
				const step = Number(input.step) || 1;
				const min = input.min === '' ? 1 : Number(input.min);
				const max = input.max === '' ? Infinity : Number(input.max);
				const next = Math.min(max, Math.max(min, (Number(input.value) || min) + dir * step));
				input.value = String(next);
				input.dispatchEvent(new Event('change', { bubbles: true }));
			});
			return b;
		};

		input.before(make(-1, 'Decrease quantity', 'minus'));
		input.after(make(1, 'Increase quantity', 'plus'));
	});
}

function initStickyBuy() {
	const bar = document.querySelector('[data-sticky-buy]');
	const action = document.querySelector('[data-buy-action]');
	if (!bar || !action || !('IntersectionObserver' in window)) return;

	new IntersectionObserver(([entry]) => {
		bar.hidden = entry.isIntersecting || entry.boundingClientRect.top > 0;
	}).observe(action);

	bar.querySelector('[data-sticky-buy-go]').addEventListener('click', () => {
		const real = action.querySelector('.single_add_to_cart_button, a.btn');
		if (real) real.click();
		else action.scrollIntoView({ block: 'center' });
	});
}
