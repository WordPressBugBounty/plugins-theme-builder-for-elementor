/**
 * Theme Builder widget – Back to Top: shows after scrolling, the progress ring. Registers itself in the core (widgets.js).
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

	function toTop( button ) {
		if ( ! once( button, 'tbfeReady' ) ) {
			return;
		}
		var after = parseInt( button.dataset.showAfter, 10 ) || 0;
		var ring = button.querySelector( '.tbfe-to-top__ring circle' );
		var editor = button.classList.contains( 'is-visible' );
		button.addEventListener( 'click', function () {
			scrollToY( 0 );
		} );
		T.onScroll( function () {
			if ( ! editor ) {
				button.classList.toggle( 'is-visible', window.pageYOffset > after );
			}
			if ( ring ) {
				ring.style.strokeDashoffset = String( 100 - T.progressOf( null ) * 100 );
			}
		} );
	}

	T.register( '.tbfe-to-top', 'tbfe-back-to-top', toTop );
}( window.tbfeWidgets ) );
