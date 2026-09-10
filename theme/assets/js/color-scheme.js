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
		// Set the UA canvas directly as well as the theme's CSS selector.
		root.style.colorScheme = dark ? 'dark' : 'light';
		root.style.backgroundColor = dark ? '#171a20' : '';
		document.querySelectorAll('.d3-color-toggle').forEach(function (button) {
			button.setAttribute('aria-checked', String(dark));
		});
	}
	apply();
	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('.d3-color-toggle').forEach(function (button) {
			var drag = null;
			var suppressClick = false;
			function choose(value) {
				preference = value;
				try { localStorage.setItem(key, preference); } catch (error) { /* Storage is optional. */ }
				apply();
			}
			button.addEventListener('pointerdown', function (event) {
				if (!event.isPrimary || event.button !== 0) { return; }
				suppressClick = false;
				drag = { id: event.pointerId, x: event.clientX, start: root.dataset.colorScheme === 'dark' ? 48 : 0, moved: false };
				button.setPointerCapture(event.pointerId);
			});
			button.addEventListener('pointermove', function (event) {
				if (!drag || drag.id !== event.pointerId) { return; }
				var delta = event.clientX - drag.x;
				if (!drag.moved && Math.abs(delta) < 6) { return; }
				drag.moved = true;
				drag.position = Math.max(0, Math.min(48, drag.start + delta));
				button.classList.add('is-dragging');
				button.style.setProperty('--d3-switch-position', drag.position + 'px');
			});
			function finish(event) {
				if (!drag || drag.id !== event.pointerId) { return; }
				var ended = drag;
				drag = null;
				button.classList.remove('is-dragging');
				button.style.removeProperty('--d3-switch-position');
				if (button.hasPointerCapture(event.pointerId)) { button.releasePointerCapture(event.pointerId); }
				if (ended.moved || event.type !== 'pointerup') {
					suppressClick = true;
					if (event.type === 'pointerup') { choose(ended.position >= 24 ? 'dark' : 'light'); }
				}
			}
			button.addEventListener('pointerup', finish);
			button.addEventListener('pointercancel', finish);
			button.addEventListener('lostpointercapture', finish);
			button.addEventListener('click', function (event) {
				if (suppressClick && event.detail !== 0) { suppressClick = false; return; }
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
