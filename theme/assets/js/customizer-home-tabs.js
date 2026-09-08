( function( $, api, i18n ) {
	'use strict';
	var __ = i18n.__;
	api.control( 'koji_d3_home_tabs', function( control ) {
		var tabs = control.setting.get();
		tabs = Array.isArray( tabs ) ? JSON.parse( JSON.stringify( tabs ) ) : [];
		var editor = control.container.find( '.home-tabs-editor' );
		function save() {
			control.setting.set( JSON.parse( JSON.stringify( tabs ) ) );
		}
		function render( focusIndex ) {
			editor.empty();
			tabs.forEach( function( tab, index ) {
				var row = $( '<fieldset class="home-tab-editor">' ).appendTo( editor );
				$( '<legend>' ).text( ( index + 1 ) + '. ' + ( tab.label || __( 'Untitled tab', 'koji-d3' ) ) ).appendTo( row );
				var nameLabel = $( '<label>' ).text( __( 'Name', 'koji-d3' ) ).appendTo( row );
				$( '<input type="text" class="home-tab-name">' ).val( tab.label ).appendTo( nameLabel ).on( 'input', function() {
					tab.label = this.value;
					row.find( 'legend' ).text( ( index + 1 ) + '. ' + this.value );
					save();
				} );
				$( '<p class="description">' ).text( __( 'URL identifier: ', 'koji-d3' ) + tab.id ).appendTo( row );
				var modeLabel = $( '<label>' ).text( __( 'Filter mode', 'koji-d3' ) ).appendTo( row );
				var mode = $( '<select>' ).appendTo( modeLabel );
				[ [ 'include', __( 'Specific categories', 'koji-d3' ) ], [ 'all', __( 'All categories', 'koji-d3' ) ], [ 'exclude', __( 'All except', 'koji-d3' ) ] ].forEach( function( option ) {
					$( '<option>' ).val( option[0] ).text( option[1] ).appendTo( mode );
				} );
				mode.val( tab.mode );
				var categories = $( '<fieldset class="home-tab-categories">' ).appendTo( row );
				var legend = $( '<legend>' ).appendTo( categories );
				kojiD3TabCategories.forEach( function( category ) {
					var label = $( '<label>' ).appendTo( categories );
					$( '<input type="checkbox">' ).prop( 'checked', tab.categories.indexOf( category.id ) !== -1 ).appendTo( label ).on( 'change', function() {
						tab.categories = tab.categories.filter( function( id ) { return id !== category.id; } );
						if ( this.checked ) { tab.categories.push( category.id ); }
						save();
					} );
					label.append( document.createTextNode( category.name ) );
				} );
				function updateMode() {
					categories.prop( 'hidden', tab.mode === 'all' );
					legend.text( tab.mode === 'exclude' ? __( 'Categories to exclude', 'koji-d3' ) : __( 'Categories to include', 'koji-d3' ) );
				}
				mode.on( 'change', function() { tab.mode = this.value; updateMode(); save(); } );
				updateMode();
				var actions = $( '<div class="home-tab-actions">' ).appendTo( row );
				[ [ -1, __( 'Move up', 'koji-d3' ) ], [ 1, __( 'Move down', 'koji-d3' ) ] ].forEach( function( move ) {
					$( '<button type="button" class="button">' ).text( move[1] ).prop( 'disabled', index + move[0] < 0 || index + move[0] >= tabs.length ).appendTo( actions ).on( 'click', function() {
						tabs.splice( index, 1 ); tabs.splice( index + move[0], 0, tab ); save(); render( index + move[0] );
						control.container.find( '.home-tabs-status' ).text( __( 'Tab moved. The first tab is the default.', 'koji-d3' ) );
					} );
				} );
				$( '<button type="button" class="button">' ).text( __( 'Remove', 'koji-d3' ) ).appendTo( actions ).on( 'click', function() {
					tabs.splice( index, 1 ); save(); render( Math.min( index, tabs.length - 1 ) );
					if ( ! tabs.length ) { control.container.find( '.home-tabs-add' ).trigger( 'focus' ); }
				} );
			} );
			control.container.find( '.home-tabs-add' ).prop( 'disabled', tabs.length >= 20 );
			if ( focusIndex >= 0 ) { editor.children().eq( focusIndex ).find( '.home-tab-name' ).trigger( 'focus' ); }
		}
		control.container.find( '.home-tabs-add' ).on( 'click', function() {
			if ( tabs.length >= 20 ) { return; }
			var id;
			do { id = 'tab-' + Math.random().toString( 36 ).slice( 2, 10 ); } while ( tabs.some( function( tab ) { return tab.id === id; } ) );
			tabs.push( { id: id, label: __( 'New tab', 'koji-d3' ), mode: 'all', categories: [] } );
			save(); render( tabs.length - 1 );
		} );
		render();
	} );
}( jQuery, wp.customize, wp.i18n ) );
