/**
 * Header behaviour: "is-scrolled" after the first pixels (background, shadow, shrink) and "is-hidden" while the
 * visitor scrolls down (hide when scrolling down).
 */
( function () {
	'use strict';

	var headers = document.querySelectorAll( '.tbfe-header--sticky, .tbfe-header--transparent' );
	if ( ! headers.length ) {
		return;
	}
	var last = window.pageYOffset;
	var ticking = false;

	function update() {
		var y = window.pageYOffset;
		headers.forEach( function ( header ) {
			header.classList.toggle( 'is-scrolled', y > 10 );
			// Hidden only after the header itself scrolled away, and never while a menu is open in it. Without a
			// movement (the last event of a smooth scroll) it stays as it is.
			if ( header.classList.contains( 'tbfe-header--hide' ) && y !== last ) {
				var open = header.querySelector( '.is-open, [aria-expanded="true"]' );
				header.classList.toggle( 'is-hidden', ! open && y > last && y > header.offsetHeight * 2 );
			}
		} );
		last = y;
		ticking = false;
	}

	window.addEventListener(
		'scroll',
		function () {
			if ( ! ticking ) {
				ticking = true;
				window.requestAnimationFrame( update );
			}
		},
		{ passive: true }
	);
	update();
}() );
