/**
 * Theme Builder widget – Reading Progress: the bar fills up while the visitor reads. Registers itself in the core (widgets.js).
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

	function readingProgress( box ) {
		if ( ! once( box, 'tbfeReady' ) ) {
			return;
		}
		var bar = box.querySelector( '.tbfe-progress__bar' );
		var target = 'content' === box.dataset.target ? document.querySelector( '.tbfe-post-content' ) || document.querySelector( '.entry-content' ) : null;
		T.onScroll( function () {
			var value = T.progressOf( target );
			bar.style.transform = 'scaleX(' + value + ')';
			box.setAttribute( 'aria-valuenow', String( Math.round( value * 100 ) ) );
		} );
	}

	T.register( '.tbfe-progress', 'tbfe-reading-progress', readingProgress );
}( window.tbfeWidgets ) );
