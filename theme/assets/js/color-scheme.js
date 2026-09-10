(function () {
	'use strict';
	var key = 'koji-d3-color-scheme';
	var root = document.documentElement;
	var system = window.matchMedia('(prefers-color-scheme: dark)');
	var preference;
	function valid(value) { return value === 'dark' || value === 'light'; }
	try { preference = localStorage.getItem(key); } catch (error) { /* Storage is optional. */ }
	function apply() {
		var dark = valid(preference) ? preference === 'dark' : system.matches;
		root.dataset.colorScheme = dark ? 'dark' : 'light';
		document.querySelectorAll('.d3-color-toggle').forEach(function (button) {
			button.setAttribute('aria-checked', String(dark));
		});
	}
	apply();
	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('.d3-color-toggle').forEach(function (button) {
			button.addEventListener('click', function () {
				preference = root.dataset.colorScheme === 'dark' ? 'light' : 'dark';
				try { localStorage.setItem(key, preference); } catch (error) { /* Keep the in-page choice. */ }
				apply();
			});
			button.hidden = false;
		});
		apply();
	});
	system.addEventListener('change', apply);
	window.addEventListener('storage', function (event) {
		if (event.key === key || event.key === null) {
			preference = event.newValue;
			apply();
		}
	});
}());
