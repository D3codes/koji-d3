( function( $ ) {
	'use strict';

	// Infinite scrolling makes the home page feel like one continuous feed, so
	// don't turn its URL into /page/2/ when more posts are appended. Direct
	// visits to paginated archives retain Koji's standard history behavior.
	if ( window.kojiD3Pagination && window.kojiD3Pagination.preserveHomeUrl ) {
		koji.loadMore.updateHistory = function() {};
	}

	// Koji focuses the first title in each appended page. Keep that focus for
	// keyboard and assistive-technology users, but prevent the browser from
	// scrolling the newly focused title to the top of the viewport.
	var kojiFocus = $.fn.focus;
	$.fn.focus = function() {
		var $target = this.first();
		var isAppendedPostTitle = $target.is( '.preview-title a, .d3-link-focus' ) &&
			$target.closest( '[class*="post-from-page-"]' ).length;

		if ( arguments.length === 0 && $( 'body' ).hasClass( 'pagination-type-scroll' ) && isAppendedPostTitle ) {
			$target.get( 0 ).focus( { preventScroll: true } );
			return this;
		}

		return kojiFocus.apply( this, arguments );
	};

	// Link previews have their own title markup. Focus the first appended Link
	// just as Koji focuses normal posts, without jumping during infinite scroll.
	$( window ).on( 'ajax-content-loaded', function() {
		var $last = $( '#posts > [class*="post-from-page-"]' ).last();
		var pageClass = ( $last.attr( 'class' ) || '' ).match( /\bpost-from-page-\d+\b/ );
		if ( ! pageClass ) { return; }
		var $first = $( '#posts > .' + pageClass[0] ).first();
		var target = $first.filter( '.preview-link' ).find( '.d3-link-focus' ).get( 0 );
		if ( target ) { target.focus( { preventScroll: $( 'body' ).hasClass( 'pagination-type-scroll' ) } ); }
	} );

	// Koji uses the same startup event to reveal page 1 and to check whether
	// another page should load. Keep the startup event for the initial content,
	// but require genuine scroll intent before allowing pagination.
	koji.loadMore.detectScroll = function( $pagination, queryArgs ) {
		var hasScrollIntent = false;
		var scrollKeys = [ 32, 34, 35, 40 ]; // Space, Page Down, End, Arrow Down.

		function maybeLoadPosts() {
			if ( ! hasScrollIntent || window.lastPage || window.loading ) {
				return;
			}

			var paginationOffset = $pagination.offset().top;
			var windowBottom = $( window ).scrollTop() + $( window ).outerHeight();

			if ( windowBottom > paginationOffset ) {
				hasScrollIntent = false;
				koji.loadMore.loadPosts( $pagination, queryArgs );
			}
		}

		$( window ).on( 'scroll', function() {
			if ( $( window ).scrollTop() > 0 ) {
				hasScrollIntent = true;
			}
		} );

		$( window ).on( 'wheel touchmove', function( event ) {
			if ( event.type === 'touchmove' || ! event.originalEvent || event.originalEvent.deltaY > 0 ) {
				hasScrollIntent = true;
				maybeLoadPosts();
			}
		} );

		$( document ).on( 'keydown', function( event ) {
			if ( $( event.target ).is( 'input, textarea, select, [contenteditable]' ) ) {
				return;
			}

			if ( scrollKeys.indexOf( event.which ) !== -1 ) {
				hasScrollIntent = true;
				maybeLoadPosts();
			}
		} );

		$( window ).on( 'did-interval-scroll', maybeLoadPosts );
	};
}( jQuery ) );
