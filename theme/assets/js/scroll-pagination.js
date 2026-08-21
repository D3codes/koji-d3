( function( $ ) {
	'use strict';

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
