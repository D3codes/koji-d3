( function( $ ) {
	'use strict';
	// Koji's AJAX endpoint drops category__in/category__not_in. Retrieve the real
	// next homepage instead, so both JS and ordinary links use the same main query.
	// This also preserves homepage text previews when no tabs are configured.
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
			return page.find( '#posts > .preview' ).map( function() {
				return this.outerHTML;
			} ).get().join( '' );
		};
	} );
}( jQuery ) );
