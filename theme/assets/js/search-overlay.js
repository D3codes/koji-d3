( function() {
	'use strict';
	var overlay = document.querySelector( '.search-overlay' );
	if ( ! overlay ) { return; }
	// Koji's toggle attribute helper can leave stale ARIA values after closing.
	jQuery( overlay ).on( 'toggled', function() {
		var expanded = String( overlay.classList.contains( 'active' ) );
		overlay.setAttribute( 'aria-expanded', expanded );
		document.querySelectorAll( '[data-toggle-target=".search-overlay"]' ).forEach( function( toggle ) {
			toggle.setAttribute( 'aria-pressed', expanded );
		} );
	} );
	document.addEventListener( 'click', function( event ) {
		if ( ! overlay.classList.contains( 'active' ) ||
			event.target.closest( '.search-overlay .search-form, .search-untoggle, .search-toggle' ) ) {
			return;
		}
		var close = overlay.querySelector( '.search-untoggle' );
		if ( ! close ) { return; }
		// Dismiss only: don't also activate a link visible through the backdrop.
		event.preventDefault();
		event.stopPropagation();
		close.click();
		var opener = Array.from( document.querySelectorAll( '.search-toggle' ) ).find( function( button ) {
			return button.getClientRects().length > 0;
		} );
		if ( opener ) { opener.focus( { preventScroll: true } ); }
	}, true );
}() );
