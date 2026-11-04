/**
 * Theme Builder widget – Share Buttons: copy the link. Registers itself in the core (widgets.js).
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

	// Share Buttons: copy the link.
	function share( box ) {
		box.querySelectorAll( '.tbfe-share__button--copy' ).forEach( function ( button ) {
			if ( ! once( button, 'tbfeReady' ) ) {
				return;
			}
			button.addEventListener( 'click', function () {
				var url = button.dataset.url;
				var label = button.querySelector( '.tbfe-share__label' );
				var text = label ? label.textContent : '';
				var done = function () {
					button.classList.add( 'is-copied' );
					if ( label ) {
						label.textContent = button.dataset.copied || text;
					}
					setTimeout( function () {
						button.classList.remove( 'is-copied' );
						if ( label ) {
							label.textContent = text;
						}
					}, 2000 );
				};
				// Older browsers, or when the browser refuses the clipboard API.
				var fallback = function () {
					var area = document.createElement( 'textarea' );
					area.value = url;
					area.setAttribute( 'readonly', '' );
					area.style.position = 'fixed';
					area.style.opacity = '0';
					document.body.appendChild( area );
					area.select();
					try {
						if ( document.execCommand( 'copy' ) ) {
							done();
						}
					} catch ( e ) {}
					area.remove();
				};
				if ( navigator.clipboard && window.isSecureContext ) {
					navigator.clipboard.writeText( url ).then( done, fallback );
					return;
				}
				fallback();
			} );
		} );
	}

	T.register( '.tbfe-share', 'tbfe-share-buttons', share );
}( window.tbfeWidgets ) );
