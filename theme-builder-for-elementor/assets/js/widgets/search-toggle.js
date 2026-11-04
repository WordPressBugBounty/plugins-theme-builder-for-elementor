/**
 * Theme Builder widget – Search Form as an icon: opens the field (drop-down or the whole screen). Registers itself in the core (widgets.js).
 */
( function ( T ) {
	'use strict';

	if ( ! T ) {
		return;
	}
	var reduceMotion = T.reduceMotion;
	var once = T.once;
	var onVisible = T.onVisible;
	var scrollToY = T.scrollToY;

	// Search Form: the icon opens the field (drop-down or the whole screen).
	function searchToggle( box ) {
		if ( ! once( box, 'tbfeReady' ) ) {
			return;
		}
		var button = box.querySelector( '.tbfe-search-toggle__button' );
		var panel = box.querySelector( '.tbfe-search-toggle__panel' );
		var close = box.querySelector( '.tbfe-search-toggle__close' );
		if ( ! button || ! panel ) {
			return;
		}
		var set = function ( open ) {
			panel.hidden = ! open;
			box.classList.toggle( 'is-open', open );
			button.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			if ( open ) {
				var input = panel.querySelector( 'input' );
				if ( input ) {
					setTimeout( function () {
						input.focus();
					}, 30 );
				}
			}
		};
		button.addEventListener( 'click', function () {
			set( panel.hidden );
		} );
		if ( close ) {
			close.addEventListener( 'click', function () {
				set( false );
				button.focus();
			} );
		}
		box.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key && ! panel.hidden ) {
				set( false );
				button.focus();
			}
		} );
		document.addEventListener( 'click', function ( event ) {
			if ( ! panel.hidden && ! box.contains( event.target ) ) {
				set( false );
			}
		} );
		// The whole screen: a click on the background closes it.
		panel.addEventListener( 'click', function ( event ) {
			if ( event.target === panel && box.classList.contains( 'tbfe-search-toggle--overlay' ) ) {
				set( false );
			}
		} );
	}

	T.register( '.tbfe-search-toggle', 'tbfe-search-form', searchToggle );
}( window.tbfeWidgets ) );
