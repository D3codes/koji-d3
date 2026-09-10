(function (wp) {
	'use strict';
	var el = wp.element.createElement;
	var __ = wp.i18n.__;
	function DarkImageControl(props) {
		var id = props.attributes.d3DarkImageId || 0;
		var media = wp.data.useSelect(function (select) {
			return id ? select('core').getMedia(id) : null;
		}, [id]);
		return el(wp.blockEditor.InspectorControls, null,
			el(wp.components.PanelBody, { title: __('Dark mode image', 'koji-d3'), initialOpen: false },
				el('p', null, __('Optional alternative for the theme’s dark mode. Use the same dimensions and subject; the block’s layout and alt text apply to both images.', 'koji-d3')),
				media && el('img', { src: media.source_url, alt: '', style: { maxWidth: '100%', height: 'auto' } }),
				el(wp.blockEditor.MediaUploadCheck, null,
					el(wp.blockEditor.MediaUpload, {
						allowedTypes: ['image'], value: id,
						onSelect: function (image) { props.setAttributes({ d3DarkImageId: image.id }); },
						render: function (control) { return el(wp.components.Button, { variant: 'secondary', onClick: control.open }, id ? __('Replace dark image', 'koji-d3') : __('Select dark image', 'koji-d3')); }
					})),
				id > 0 && el(wp.components.Button, { variant: 'tertiary', isDestructive: true, onClick: function () { props.setAttributes({ d3DarkImageId: 0 }); } }, __('Remove dark image', 'koji-d3'))
			));
	}
	wp.hooks.addFilter('editor.BlockEdit', 'koji-d3/dark-image', wp.compose.createHigherOrderComponent(function (BlockEdit) {
		return function (props) {
			return el(wp.element.Fragment, null, el(BlockEdit, props), props.name === 'core/image' && props.isSelected && el(DarkImageControl, props));
		};
	}, 'withDarkImage'));
}(window.wp));
