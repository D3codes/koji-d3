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

		$( window ).on( 'scroll', function() {
			didScroll = true;
		} );

		setInterval( function() {
			if ( didScroll ) {
				didScroll = false;
				$( window ).triggerHandler( 'did-interval-scroll' );
			}
		}, 250 );
	};
}( jQuery ) );
