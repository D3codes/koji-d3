(function () {
	'use strict';
	var root = document.documentElement;
	var applied = new WeakMap();
	function update(image) {
		var scheme = root.dataset.colorScheme === 'dark' ? 'dark' : 'light';
		if (applied.get(image) === scheme) { return; }
		try {
			var attributes = JSON.parse(image.getAttribute('data-d3-image-' + scheme));
			if (!attributes || !attributes.src) { return; }
			['sizes', 'srcset', 'src'].forEach(function (name) {
				if (attributes[name]) { image.setAttribute(name, attributes[name]); }
				else { image.removeAttribute(name); }
			});
			applied.set(image, scheme);
		} catch (error) { /* Leave the regular image intact if metadata is invalid. */ }
	}
	function scan(node) {
		if (node.nodeType !== 1) { return; }
		if (node.matches('img[data-d3-image-dark]')) { update(node); }
		node.querySelectorAll('img[data-d3-image-dark]').forEach(update);
	}
	new MutationObserver(function () { scan(root); }).observe(root, { attributes: true, attributeFilter: ['data-color-scheme'] });
	new MutationObserver(function (records) {
		records.forEach(function (record) { record.addedNodes.forEach(scan); });
	}).observe(root, { childList: true, subtree: true });
	scan(root);
}());
