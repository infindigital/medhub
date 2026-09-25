/**
 * Native <dialog> open/close (focus trap, Esc and inert background come from the browser).
 * [data-open-dialog="id"] opens, [data-close-dialog] closes, a click on the backdrop closes.
 */
export function initDialogs() {
	let opener = null;

	document.addEventListener('click', (event) => {
		const openBtn = event.target.closest('[data-open-dialog]');
		if (openBtn) {
			const dialog = document.getElementById(openBtn.dataset.openDialog);
			if (dialog && typeof dialog.showModal === 'function') {
				opener = openBtn;
				dialog.showModal();
				const autofocus = dialog.querySelector('input[type="search"]');
				if (autofocus) autofocus.focus();
			}
			return;
		}

		if (event.target.closest('[data-close-dialog]')) {
			event.target.closest('dialog')?.close();
			return;
		}

		// Clicks on the ::backdrop target the <dialog> element itself.
		if (event.target instanceof HTMLDialogElement) {
			event.target.close();
		}
	});

	document.querySelectorAll('dialog').forEach((dialog) => {
		dialog.addEventListener('close', () => {
			if (opener) opener.focus();
			opener = null;
		});
	});
}
