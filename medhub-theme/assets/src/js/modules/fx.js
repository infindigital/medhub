/**
 * Small visual effects (no libraries):
 *  - click ripple on buttons
 *  - back-to-top button with a scroll-progress ring
 *  - cart count "bump" when WooCommerce refreshes the fragment
 *
 * All decorative; skipped with prefers-reduced-motion where it animates.
 */
const reduce = window.matchMedia('(prefers-reduced-motion: reduce)');

export function initFx() {
	initRipple();
	initToTop();
	initCartBump();
}

/* ---------------- Click ripple ---------------- */
function initRipple() {
	document.addEventListener('pointerdown', (e) => {
		if (reduce.matches) return;
		const btn = e.target.closest('.btn:not(.btn--ghost), .button, .single_add_to_cart_button');
		if (!btn) return;

		const r = btn.getBoundingClientRect();
		const ripple = document.createElement('span');
		ripple.className = 'ripple';
		ripple.setAttribute('aria-hidden', 'true');
		ripple.style.setProperty('--d', `${Math.max(r.width, r.height) * 2.2}px`);
		ripple.style.setProperty('--x', `${e.clientX - r.left}px`);
		ripple.style.setProperty('--y', `${e.clientY - r.top}px`);
		if (getComputedStyle(btn).position === 'static') btn.style.position = 'relative';
		btn.style.overflow = 'hidden';
		btn.append(ripple);
		ripple.addEventListener('animationend', () => ripple.remove(), { once: true });
	});
}

/* ---------------- Back to top ---------------- */
function initToTop() {
	const btn = document.createElement('button');
	btn.type = 'button';
	btn.className = 'to-top';
	btn.setAttribute('aria-label', document.documentElement.lang?.startsWith('ar') ? 'العودة إلى الأعلى' : 'Back to top');
	btn.innerHTML =
		'<svg class="to-top__ring" viewBox="0 0 36 36" aria-hidden="true"><circle class="to-top__track" cx="18" cy="18" r="15.9155"/><circle class="to-top__bar" cx="18" cy="18" r="15.9155" pathLength="100"/></svg>' +
		'<svg class="to-top__arrow" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M5 12l7-7 7 7"/></svg>';
	document.body.append(btn);

	let queued = false;
	const update = () => {
		queued = false;
		const max = document.documentElement.scrollHeight - window.innerHeight;
		const p = max > 0 ? Math.min(1, window.scrollY / max) : 0;
		btn.style.setProperty('--p', p.toFixed(4));
		btn.classList.toggle('is-visible', window.scrollY > window.innerHeight * 0.8);
	};
	window.addEventListener(
		'scroll',
		() => {
			if (!queued) (queued = true), requestAnimationFrame(update);
		},
		{ passive: true }
	);
	update();

	btn.addEventListener('click', () => {
		window.scrollTo({ top: 0, behavior: reduce.matches ? 'auto' : 'smooth' });
		document.querySelector('.logo')?.focus({ preventScroll: true });
	});
}

/* ---------------- Cart count bump ---------------- */
function initCartBump() {
	const count = document.querySelector('.cart-count');
	const holder = count?.parentElement;
	if (!holder) return;

	let last = count.dataset.count;
	new MutationObserver(() => {
		const current = holder.querySelector('.cart-count');
		if (!current || current.dataset.count === last) return;
		last = current.dataset.count;
		current.classList.add('is-bumped');
		current.addEventListener('animationend', () => current.classList.remove('is-bumped'), { once: true });
	}).observe(holder, { childList: true });
}
