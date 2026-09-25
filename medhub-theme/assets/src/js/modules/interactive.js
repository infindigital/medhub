/**
 * Interaction layer (no libraries):
 *  - hero: product carousel, pointer tilt + depth parallax + cursor glow
 *  - cards/tiles: cursor spotlight, subtle 3D tilt on department tiles
 *  - magnetic buttons
 *  - counters that count up when they scroll into view
 *
 * Pointer effects only run with a fine pointer (mouse/trackpad); nothing animates
 * with prefers-reduced-motion, and the carousel never auto-advances in that case.
 */
const reduce = window.matchMedia('(prefers-reduced-motion: reduce)');
const fine = window.matchMedia('(hover: hover) and (pointer: fine)');
const pointerFx = () => fine.matches && !reduce.matches;

/** Run `fn` at most once per animation frame. */
const rafThrottle = (fn) => {
	let queued = false;
	let lastArgs;
	return (...args) => {
		lastArgs = args;
		if (queued) return;
		queued = true;
		requestAnimationFrame(() => {
			queued = false;
			fn(...lastArgs);
		});
	};
};

export function initInteractive() {
	initHeroSlides();
	initHeroPointer();
	initSpotlights();
	initMagnetic();
	initCounters();
}

/* ---------------- Hero carousel ---------------- */
function initHeroSlides() {
	document.querySelectorAll('[data-hero-slides]').forEach((root) => {
		const slides = [...root.querySelectorAll('[data-slide]')];
		const chips = [...root.querySelectorAll('[data-slide-chip]')];
		const dots = [...root.querySelectorAll('[data-slide-to]')];
		const pauseBtn = root.querySelector('[data-slide-pause]');
		const status = root.querySelector('[data-slide-status]');
		if (slides.length < 2) return;

		let index = 0;
		let timer = 0;
		let paused = reduce.matches;
		let hovering = false;

		const show = (next, announce = false) => {
			index = (next + slides.length) % slides.length;
			slides.forEach((s, i) => {
				s.classList.toggle('is-active', i === index);
				s.toggleAttribute('aria-hidden', i !== index);
				// Lazy images of upcoming slides start loading before they are shown.
				const img = s.querySelector('img');
				if (img && i === (index + 1) % slides.length) img.loading = 'eager';
			});
			chips.forEach((c, i) => {
				c.classList.toggle('is-active', i === index);
				c.toggleAttribute('aria-hidden', i !== index);
				c.tabIndex = i === index ? 0 : -1;
			});
			dots.forEach((d, i) => (i === index ? d.setAttribute('aria-current', 'true') : d.removeAttribute('aria-current')));
			if (announce && status) status.textContent = chips[index]?.querySelector('.hero__chip-name')?.textContent || '';
		};

		const schedule = () => {
			clearTimeout(timer);
			if (!paused && !hovering && !document.hidden) timer = setTimeout(() => (show(index + 1), schedule()), 5200);
		};

		dots.forEach((dot) => dot.addEventListener('click', () => (show(Number(dot.dataset.slideTo), true), schedule())));

		if (pauseBtn) {
			pauseBtn.setAttribute('aria-pressed', String(paused));
			pauseBtn.addEventListener('click', () => {
				paused = !paused;
				pauseBtn.setAttribute('aria-pressed', String(paused));
				schedule();
			});
		}

		root.addEventListener('pointerenter', () => ((hovering = true), schedule()));
		root.addEventListener('pointerleave', () => ((hovering = false), schedule()));
		root.addEventListener('focusin', () => ((hovering = true), schedule()));
		root.addEventListener('focusout', (e) => {
			if (!root.contains(e.relatedTarget)) (hovering = false), schedule();
		});
		document.addEventListener('visibilitychange', schedule);

		schedule();
	});
}

/* ---------------- Hero pointer: tilt, parallax, glow ---------------- */
function initHeroPointer() {
	document.querySelectorAll('[data-tilt]').forEach((visual) => {
		const arch = visual.querySelector('.hero__arch');
		if (!arch) return;

		const move = rafThrottle((e) => {
			const r = visual.getBoundingClientRect();
			const x = (e.clientX - r.left) / r.width - 0.5; // -0.5 … 0.5
			const y = (e.clientY - r.top) / r.height - 0.5;
			arch.style.setProperty('--rx', `${(-y * 9).toFixed(2)}deg`);
			arch.style.setProperty('--ry', `${(x * 11).toFixed(2)}deg`);
			visual.style.setProperty('--px', (x * 2).toFixed(3));
			visual.style.setProperty('--py', (y * 2).toFixed(3));
			const a = arch.getBoundingClientRect();
			arch.style.setProperty('--mx', `${(((e.clientX - a.left) / a.width) * 100).toFixed(1)}%`);
			arch.style.setProperty('--my', `${(((e.clientY - a.top) / a.height) * 100).toFixed(1)}%`);
		});

		visual.addEventListener('pointermove', (e) => {
			if (!pointerFx()) return;
			visual.classList.add('is-pointer');
			move(e);
		});

		visual.addEventListener('pointerleave', () => {
			visual.classList.remove('is-pointer');
			['--rx', '--ry', '--mx', '--my'].forEach((p) => arch.style.removeProperty(p));
			['--px', '--py'].forEach((p) => visual.style.removeProperty(p));
		});
	});
}

/* ---------------- Card spotlights + tile tilt ---------------- */
const SPOTLIGHT = '.pcard, .tile, .link-card, .bento > .wp-block-group, .bento__cell, .cta__panel, .contact-item, .brand-tile, .care__panel, .mega-feature';

function initSpotlights() {
	if (!fine.matches) return;

	document.querySelectorAll(SPOTLIGHT).forEach((el) => {
		el.classList.add('has-spotlight');
		const tilt = el.classList.contains('tile');
		if (tilt) el.classList.add('has-tilt');

		const move = rafThrottle((e) => {
			const r = el.getBoundingClientRect();
			const x = e.clientX - r.left;
			const y = e.clientY - r.top;
			el.style.setProperty('--mx', `${x}px`);
			el.style.setProperty('--my', `${y}px`);
			if (tilt && !reduce.matches) {
				el.style.setProperty('--rx', `${((0.5 - y / r.height) * 5).toFixed(2)}deg`);
				el.style.setProperty('--ry', `${((x / r.width - 0.5) * 6).toFixed(2)}deg`);
			}
		});

		el.addEventListener('pointerenter', () => el.classList.add('is-lit'));
		el.addEventListener('pointermove', move);
		el.addEventListener('pointerleave', () => {
			el.classList.remove('is-lit');
			el.style.removeProperty('--rx');
			el.style.removeProperty('--ry');
		});
	});
}

/* ---------------- Magnetic buttons ---------------- */
function initMagnetic() {
	document.querySelectorAll('[data-magnetic], .cta__actions .btn:not(.btn--ghost)').forEach((btn) => {
		const move = rafThrottle((e) => {
			const r = btn.getBoundingClientRect();
			const x = e.clientX - (r.left + r.width / 2);
			const y = e.clientY - (r.top + r.height / 2);
			btn.style.setProperty('--tx', `${(x * 0.18).toFixed(1)}px`);
			btn.style.setProperty('--ty', `${(y * 0.28).toFixed(1)}px`);
		});
		btn.addEventListener('pointermove', (e) => pointerFx() && move(e));
		btn.addEventListener('pointerleave', () => {
			btn.style.removeProperty('--tx');
			btn.style.removeProperty('--ty');
		});
	});
}

/* ---------------- Counters ---------------- */
function initCounters() {
	const counters = document.querySelectorAll('[data-count-to]');
	if (!counters.length || reduce.matches || !('IntersectionObserver' in window)) return;

	const format = new Intl.NumberFormat(document.documentElement.lang || 'en');
	const io = new IntersectionObserver(
		(entries) => {
			entries.forEach((entry) => {
				if (!entry.isIntersecting) return;
				io.unobserve(entry.target);
				const el = entry.target;
				const to = Number(el.dataset.countTo);
				const start = performance.now();
				const duration = Math.min(1600, 500 + to * 25);
				const tick = (now) => {
					const t = Math.min(1, (now - start) / duration);
					const eased = 1 - Math.pow(1 - t, 3);
					el.textContent = format.format(Math.round(to * eased));
					if (t < 1) requestAnimationFrame(tick);
				};
				requestAnimationFrame(tick);
			});
		},
		{ threshold: 0.6 }
	);

	counters.forEach((el) => {
		// Start from zero only for counters that are still below the fold.
		if (el.getBoundingClientRect().top > window.innerHeight) {
			el.textContent = '0';
			io.observe(el);
		}
	});
}
