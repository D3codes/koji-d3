( function( $ ) {
	'use strict';
	// Koji's AJAX endpoint drops category__in/category__not_in. Retrieve the real
	// next page instead, so both JS and ordinary links use the same main query.
	// This also preserves homepage and search text previews without configured tabs.
	// Keep Koji's existing append, Masonry, focus, and scroll behavior.
	$.ajaxPrefilter( function( options, original ) {
		if ( ! original.data || original.data.action !== 'koji_ajax_load_more' ) {
			return;
		}
		var args = JSON.parse( original.data.json_data );
		options.url = kojiD3HomeTabs.pageUrl.replace( '987654321', String( args.paged ) );
		options.type = 'GET';
		options.data = '';
		options.dataType = 'html';
		options.dataFilter = function( html ) {
			var page = $( '<div>' ).append( $.parseHTML( html ) );
			return page.find( '#posts > article.preview' ).map( function() {
				return this.outerHTML;
			} ).get().join( '' );
		};
	} );
}( jQuery ) );

// Progressive enhancement: category links still work without JavaScript.
( function() {
	'use strict';
	var nav = document.querySelector( '.home-tabs' );
	if ( ! nav ) { return; }
	var track = nav.querySelector( '.home-tabs-track' );
	var links = Array.from( track.querySelectorAll( 'a' ) );
	var current = track.querySelector( '[aria-current="page"]' );
	if ( ! current ) { return; }
	var indicator = document.createElement( 'span' );
	indicator.className = 'home-tabs-indicator';
	indicator.setAttribute( 'aria-hidden', 'true' );
	track.prepend( indicator );
	var reduced = window.matchMedia( '(prefers-reduced-motion: reduce)' );
	var pending = false;
	function position( link ) {
		indicator.style.left = link.offsetLeft + 'px';
		indicator.style.width = link.offsetWidth + 'px';
	}
	function reveal( link ) {
		var box = link.getBoundingClientRect();
		var viewport = nav.getBoundingClientRect();
		if ( box.left < viewport.left || box.right > viewport.right ) {
			nav.scrollLeft += box.left - viewport.left - ( nav.clientWidth - box.width ) / 2;
		}
	}
	function reset() {
		indicator.style.transition = 'none';
		position( current );
		reveal( current );
		requestAnimationFrame( function() { indicator.style.transition = ''; } );
	}
	reset();
	nav.classList.add( 'is-enhanced' );
	if ( document.fonts ) { document.fonts.ready.then( reset ); }
	if ( window.ResizeObserver ) { new ResizeObserver( reset ).observe( track ); }
	window.addEventListener( 'resize', reset );
	window.addEventListener( 'pageshow', function() { pending = false; reset(); } );
	function select( link ) {
		if ( pending ) { return; }
		position( link );
		reveal( link );
		if ( link === current ) { return; }
		pending = true;
		window.setTimeout( function() { window.location.assign( link.href ); }, reduced.matches ? 0 : 180 );
	}
	var drag = null;
	var suppressClick = false;
	nav.addEventListener( 'click', function( event ) {
		if ( suppressClick && event.detail !== 0 ) { event.preventDefault(); suppressClick = false; return; }
		var link = event.target.closest( 'a' );
		if ( ! link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey ) { return; }
		event.preventDefault();
		select( link );
	} );
	nav.addEventListener( 'focusin', function( event ) {
		if ( links.includes( event.target ) ) { reveal( event.target ); }
	} );
	nav.addEventListener( 'keydown', function( event ) {
		var index = links.indexOf( event.target );
		if ( index < 0 || event.altKey || event.ctrlKey || event.metaKey ) { return; }
		var rtl = getComputedStyle( track ).direction === 'rtl';
		if ( event.key === 'ArrowRight' ) { index += rtl ? -1 : 1; }
		else if ( event.key === 'ArrowLeft' ) { index += rtl ? 1 : -1; }
		else if ( event.key === 'Home' ) { index = 0; }
		else if ( event.key === 'End' ) { index = links.length - 1; }
		else { return; }
		event.preventDefault();
		links[ ( index + links.length ) % links.length ].focus();
	} );
	// Touch swipes on unselected tabs scroll naturally; drag the selection to choose.
	nav.addEventListener( 'pointerdown', function( event ) {
		if ( pending || ! event.isPrimary || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey ) { return; }
		var link = event.target.closest( 'a' );
		if ( ! link || ( event.pointerType !== 'mouse' && link !== current ) ) { return; }
		suppressClick = false;
		drag = { id: event.pointerId, x: event.clientX, clientX: event.clientX, moved: false, link: link, grab: link === current ? event.clientX - current.getBoundingClientRect().left : current.offsetWidth / 2 };
		nav.setPointerCapture( event.pointerId );
	} );
	nav.addEventListener( 'dragstart', function( event ) { event.preventDefault(); } );
	var dragFrame = null;
	function moveDrag() {
		if ( ! drag || ! drag.moved ) { return; }
		var bounds = nav.getBoundingClientRect();
		var edge = 36;
		var speed = drag.clientX < bounds.left + edge ? -8 : ( drag.clientX > bounds.right - edge ? 8 : 0 );
		nav.scrollLeft += speed;
		var left = drag.clientX - track.getBoundingClientRect().left - drag.grab;
		left = Math.max( 0, Math.min( left, track.offsetWidth - indicator.offsetWidth ) );
		indicator.style.left = left + 'px';
		var center = left + indicator.offsetWidth / 2;
		drag.link = links.reduce( function( best, link ) {
			function distance( item ) { return Math.abs( center - item.offsetLeft - item.offsetWidth / 2 ); }
			return distance( link ) < distance( best ) ? link : best;
		} );
		dragFrame = requestAnimationFrame( moveDrag );
	}
	nav.addEventListener( 'pointermove', function( event ) {
		if ( ! drag || event.pointerId !== drag.id ) { return; }
		drag.clientX = event.clientX;
		if ( drag.moved || Math.abs( event.clientX - drag.x ) < 6 ) { return; }
		drag.moved = true;
		nav.classList.add( 'is-dragging' );
		moveDrag();
	} );
	function finish( event ) {
		if ( ! drag || event.pointerId !== drag.id ) { return; }
		var ended = drag;
		cancelAnimationFrame( dragFrame );
		drag = null;
		nav.classList.remove( 'is-dragging' );
		if ( nav.hasPointerCapture( event.pointerId ) ) { nav.releasePointerCapture( event.pointerId ); }
		if ( ended.moved && event.type === 'pointerup' ) {
			suppressClick = true;
			// Keep suppressing the synthetic click until the next pointer gesture.
			select( ended.link );
		} else if ( event.type === 'pointerup' ) {
			suppressClick = true;
			select( ended.link );
		} else {
			suppressClick = true;
			position( current );
		}
	}
	nav.addEventListener( 'pointerup', finish );
	nav.addEventListener( 'pointercancel', finish );
	nav.addEventListener( 'lostpointercapture', finish );
}() );
