/**
 * Scroll reveal: one IntersectionObserver, each element revealed once.
 * Children of [data-reveal-group] get a staggered --i index (capped).
 * CSS only hides content when JS is running and motion is allowed.
 */
export function initReveal() {
	const targets = document.querySelectorAll('[data-reveal-group], [data-reveal]');
	if (!targets.length) return;

	targets.forEach((group) => {
		[...group.children].forEach((child, i) => child.style.setProperty('--i', String(Math.min(i, 8))));
	});

	if (!('IntersectionObserver' in window)) {
		targets.forEach((t) => t.classList.add('is-in'));
		return;
	}

	const observer = new IntersectionObserver(
		(entries) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting) {
					entry.target.classList.add('is-in');
					observer.unobserve(entry.target);
				}
			});
		},
		{ rootMargin: '0px 0px -8% 0px', threshold: 0.05 }
	);

	targets.forEach((t) => observer.observe(t));
}
