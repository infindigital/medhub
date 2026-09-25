/**
 * Accessible tabs (WAI-ARIA tabs pattern, automatic activation).
 * Arrow keys move between tabs (Up/Down when aria-orientation="vertical"),
 * Home/End jump to the first/last tab.
 */
export function initTabs() {
	document.querySelectorAll('[data-tablist]').forEach((list) => {
		const tabs = [...list.querySelectorAll('[role="tab"]')];
		const vertical = list.getAttribute('aria-orientation') === 'vertical';

		const select = (tab, focus = false) => {
			tabs.forEach((t) => {
				const selected = t === tab;
				t.setAttribute('aria-selected', String(selected));
				t.tabIndex = selected ? 0 : -1;
				const panel = document.getElementById(t.getAttribute('aria-controls'));
				if (panel) panel.hidden = !selected;
			});
			if (focus) tab.focus();
			// Keep the selected pill visible in horizontally scrolling tab rows.
			tab.scrollIntoView({ block: 'nearest', inline: 'nearest' });
		};

		tabs.forEach((tab, index) => {
			tab.addEventListener('click', () => select(tab));
			tab.addEventListener('keydown', (event) => {
				const prev = vertical ? ['ArrowUp', 'ArrowLeft'] : ['ArrowLeft'];
				const next = vertical ? ['ArrowDown', 'ArrowRight'] : ['ArrowRight'];
				let target = null;

				if (prev.includes(event.key)) target = tabs[(index - 1 + tabs.length) % tabs.length];
				if (next.includes(event.key)) target = tabs[(index + 1) % tabs.length];
				if (event.key === 'Home') target = tabs[0];
				if (event.key === 'End') target = tabs[tabs.length - 1];

				if (target) {
					event.preventDefault();
					select(target, true);
				}
			});
		});
	});
}
