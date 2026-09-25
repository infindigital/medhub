/** Adds .is-scrolled to the header once the page scrolls (no scroll listeners). */
export function initHeader() {
	const header = document.querySelector('[data-header]');
	if (!header || !('IntersectionObserver' in window)) return;

	const sentinel = document.createElement('div');
	sentinel.setAttribute('aria-hidden', 'true');
	sentinel.style.cssText = 'position:absolute;top:0;left:0;width:1px;height:80px;pointer-events:none';
	document.body.prepend(sentinel);

	new IntersectionObserver(([entry]) => {
		header.classList.toggle('is-scrolled', !entry.isIntersecting);
	}).observe(sentinel);
}
