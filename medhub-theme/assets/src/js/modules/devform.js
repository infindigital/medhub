/**
 * Sandbox development form: never submits anywhere; explains that on submit.
 * (The real form plugin replaces this markup once configured.)
 */
export function initDevForm() {
	document.querySelectorAll('[data-dev-form]').forEach((form) => {
		form.addEventListener('submit', (event) => {
			event.preventDefault();
			const status = form.querySelector('[data-dev-form-status]');
			if (!form.checkValidity()) {
				form.reportValidity();
				return;
			}
			if (status) status.textContent = 'Development form: nothing was sent. The live form will use the site’s form plugin once it is confirmed.';
		});
	});
}
