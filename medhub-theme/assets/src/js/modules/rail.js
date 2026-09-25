/** Previous/next buttons for native scroll-snap rails; hidden when nothing overflows. */
export function initRails() {
	document.querySelectorAll('[data-rail]').forEach((rail) => {
		const track = rail.querySelector('[data-rail-track]');
		const prev = rail.querySelector('[data-rail-prev]');
		const next = rail.querySelector('[data-rail-next]');
		if (!track || !prev || !next) return;

		const controls = prev.parentElement;
		const reduce = window.matchMedia('(prefers-reduced-motion: reduce)');

		const update = () => {
			const max = track.scrollWidth - track.clientWidth;
			controls.hidden = max <= 2;
			prev.disabled = track.scrollLeft <= 2;
			next.disabled = track.scrollLeft >= max - 2;
		};

		const step = (direction) =>
			track.scrollBy({ left: direction * track.clientWidth * 0.85, behavior: reduce.matches ? 'auto' : 'smooth' });

		prev.addEventListener('click', () => step(-1));
		next.addEventListener('click', () => step(1));
		track.addEventListener('scroll', update, { passive: true });
		new ResizeObserver(update).observe(track);
		update();
	});
}
