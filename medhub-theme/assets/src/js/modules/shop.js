/**
 * Shop archive enhancements (progressive – the form works without JS):
 * - sort <select> submits on change (replaces WooCommerce's jQuery handler)
 * - on small screens the filter form is shown in a bottom-sheet <dialog>
 * - empty price fields are not submitted
 */
export function initShop() {
	document.querySelectorAll('form.woocommerce-ordering select.orderby').forEach((select) => {
		select.addEventListener('change', () => select.form.submit());
	});

	const home = document.querySelector('[data-filters-home]');
	const form = document.querySelector('[data-filters-form]');
	const dialog = document.querySelector('[data-filters-dialog]');

	if (form) {
		form.addEventListener('submit', () => {
			form.querySelectorAll('input[type="number"]').forEach((input) => {
				if (input.value === '') input.disabled = true;
			});
		});
	}

	if (!home || !form || !dialog) return;

	// Move the form into the dialog just before dialogs.js opens it, and back on close.
	document.addEventListener(
		'click',
		(event) => {
			if (event.target.closest('[data-open-dialog="filters-dialog"]') && form.parentElement !== dialog) {
				dialog.append(form);
			}
		},
		true
	);

	dialog.addEventListener('close', () => home.append(form));
}
