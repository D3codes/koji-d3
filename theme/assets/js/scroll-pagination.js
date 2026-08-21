( function( $ ) {
	'use strict';

	// Infinite scrolling makes the home page feel like one continuous feed, so
	// don't turn its URL into /page/2/ when more posts are appended. Direct
	// visits to paginated archives retain Koji's standard history behavior.
	if ( window.kojiD3Pagination && window.kojiD3Pagination.preserveHomeUrl ) {
		koji.loadMore.updateHistory = function() {};
	}

	// Koji's default handler listens for both "scroll" and "load". The load
	// event makes an oversized viewport behave as though the visitor scrolled.
	koji.intervalScroll.init = function() {
		var didScroll = false;
		var scrollKeys = [ 32, 34, 35, 40 ]; // Space, Page Down, End, Arrow Down.

		$( window ).on( 'scroll', function() {
			// Some browsers emit a scroll event at page load even though the
			// viewport is still at the top. Ignore that synthetic startup event.
			if ( $( window ).scrollTop() > 0 ) {
				didScroll = true;
			}
		} );

		// A page can be shorter than an oversized viewport, so a genuine scroll
		// attempt might not change scrollTop. Treat explicit user gestures as a
		// scroll even when the document itself cannot move yet.
		$( window ).on( 'wheel touchmove', function( event ) {
			if ( event.type === 'touchmove' || ! event.originalEvent || event.originalEvent.deltaY > 0 ) {
				didScroll = true;
			}
		} );

		$( document ).on( 'keydown', function( event ) {
			if ( $( event.target ).is( 'input, textarea, select, [contenteditable]' ) ) {
				return;
			}

			if ( scrollKeys.indexOf( event.which ) !== -1 ) {
				didScroll = true;
			}
		} );

		setInterval( function() {
			if ( didScroll ) {
				didScroll = false;
				$( window ).triggerHandler( 'did-interval-scroll' );
			}
		}, 250 );
	};
}( jQuery ) );
