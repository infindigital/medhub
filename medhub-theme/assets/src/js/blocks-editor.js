/**
 * Block editor UI for all MedHub blocks (no build-time JSX; uses WordPress globals).
 *
 * Every MedHub block is server-rendered. The editor shows:
 *  - a sidebar panel with one field per block attribute (text / textarea / toggle / number)
 *  - a live server-side preview, or editable inner blocks for wrapper blocks (Bento, FAQ)
 */
(function (wp) {
	if (!wp || !wp.blocks) return;

	const { registerBlockType, getBlockType } = wp.blocks;
	const { createElement: el, Fragment } = wp.element;
	const { InspectorControls, InnerBlocks, useBlockProps } = wp.blockEditor;
	const { PanelBody, TextControl, TextareaControl, ToggleControl, RangeControl } = wp.components;
	const ServerSideRender = wp.serverSideRender;
	const { __ } = wp.i18n;

	const INNER = {
		'medhub/bento': {
			allowed: ['core/group'],
			template: [['core/group', {}, [['core/heading', { level: 3 }], ['core/paragraph']]]],
		},
		'medhub/faq': {
			allowed: ['core/details'],
			template: [['core/details', {}, [['core/paragraph']]]],
		},
	};

	const LONG = ['lead', 'text'];
	const HELP = {
		heading: __('Wrap a word in *asterisks* for the serif accent.', 'medhub'),
		departments: __('Comma-separated department keys from the theme (e.g. oxygen, sleep, rental).', 'medhub'),
		categories: __('Comma-separated WooCommerce category slugs.', 'medhub'),
		category: __('WooCommerce category slug.', 'medhub'),
		productSlug: __('Product slug, as in /product/<slug>/.', 'medhub'),
		tabs: __('Any of: featured, sale, new', 'medhub'),
		primaryUrl: __('Site path such as /shop/ or a full URL.', 'medhub'),
		secondaryUrl: __('Site path such as /shop/ or a full URL.', 'medhub'),
		schema: __('Add these questions to the FAQ schema (Rank Math).', 'medhub'),
	};
	const SKIP = ['lock', 'metadata', 'className', 'anchor', 'style'];
	const label = (key) => key.replace(/([A-Z])/g, ' $1').replace(/^./, (c) => c.toUpperCase());

	(window.medhubBlocks || []).forEach((name) => {
		const inner = INNER[name];

		registerBlockType(name, {
			edit(props) {
				const type = getBlockType(name);
				const blockProps = useBlockProps({ className: 'medhub-editor-block' });

				const fields = Object.keys(type.attributes)
					.filter((key) => !SKIP.includes(key))
					.map((key) => {
						const def = type.attributes[key];
						const value = props.attributes[key];
						const onChange = (v) => props.setAttributes({ [key]: v });
						const common = { key, label: label(key), help: HELP[key], __nextHasNoMarginBottom: true, __next40pxDefaultSize: true };

						if (def.type === 'boolean') return el(ToggleControl, { ...common, checked: !!value, onChange });
						if (def.type === 'number') return el(RangeControl, { ...common, value, min: 1, max: 24, onChange });
						const Control = LONG.includes(key) ? TextareaControl : TextControl;
						return el(Control, { ...common, value: value || '', onChange });
					});

				return el(
					'div',
					blockProps,
					el(InspectorControls, null, el(PanelBody, { title: type.title, initialOpen: true }, fields)),
					inner
						? el(
								Fragment,
								null,
								el('p', { className: 'medhub-editor-label' }, type.title + (props.attributes.heading ? ' · ' + props.attributes.heading : '')),
								el(InnerBlocks, { allowedBlocks: inner.allowed, template: inner.template })
							)
						: el(ServerSideRender, { block: name, attributes: props.attributes })
				);
			},
			save() {
				return inner ? el(InnerBlocks.Content) : null;
			},
		});
	});
})(window.wp);
