/**
 * Mega menu / dropdown disclosure.
 * - Click or Enter/Space toggles; Esc closes and returns focus; click outside closes.
 * - On devices with a fine pointer, hover opens after a short intent delay.
 */
export function initNav() {
	const nav = document.querySelector('[data-nav]');
	if (!nav) return;

	const triggers = [...nav.querySelectorAll('[data-nav-trigger]')];
	const backdrop = document.querySelector('[data-mega-backdrop]');
	const canHover = window.matchMedia('(hover: hover) and (pointer: fine)');
	let openTrigger = null;
	let timer = 0;

	const panelOf = (trigger) => document.getElementById(trigger.getAttribute('aria-controls'));

	const close = (focus = false) => {
		if (!openTrigger) return;
		const trigger = openTrigger;
		trigger.setAttribute('aria-expanded', 'false');
		panelOf(trigger).hidden = true;
		if (backdrop) backdrop.hidden = true;
		openTrigger = null;
		if (focus) trigger.focus();
	};

	const open = (trigger) => {
		if (openTrigger === trigger) return;
		close();
		const panel = panelOf(trigger);
		trigger.setAttribute('aria-expanded', 'true');
		panel.hidden = false;
		if (backdrop && panel.classList.contains('mega')) backdrop.hidden = false;
		openTrigger = trigger;
	};

	triggers.forEach((trigger) => {
		const item = trigger.parentElement;

		trigger.addEventListener('click', () => {
			clearTimeout(timer);
			openTrigger === trigger ? close() : open(trigger);
		});

		item.addEventListener('mouseenter', () => {
			if (!canHover.matches) return;
			clearTimeout(timer);
			timer = setTimeout(() => open(trigger), openTrigger ? 0 : 120);
		});

		item.addEventListener('mouseleave', () => {
			if (!canHover.matches) return;
			clearTimeout(timer);
			timer = setTimeout(() => {
				if (openTrigger === trigger) close();
			}, 220);
		});

		item.addEventListener('focusout', (event) => {
			if (openTrigger === trigger && !item.contains(event.relatedTarget)) close();
		});
	});

	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape' && openTrigger) close(true);
	});

	document.addEventListener('click', (event) => {
		if (openTrigger && !openTrigger.parentElement.contains(event.target)) close();
	});

	if (backdrop) backdrop.addEventListener('click', () => close());
}
