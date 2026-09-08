( function() {
	'use strict';
	function hideBrokenPreview( image ) {
		if ( image.matches( '.d3-link-preview > img' ) ) {
			image.hidden = true;
		}
	}
	document.addEventListener( 'error', function( event ) {
		if ( event.target instanceof HTMLImageElement ) { hideBrokenPreview( event.target ); }
	}, true );
	document.querySelectorAll( '.d3-link-preview > img' ).forEach( function( image ) {
		if ( image.complete && image.naturalWidth === 0 ) { hideBrokenPreview( image ); }
	} );
}() );
