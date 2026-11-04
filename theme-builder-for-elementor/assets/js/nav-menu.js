/**
 * Nav Menu widget: mobile toggle and drop-downs (click, keyboard, Escape).
 */
( function () {
	'use strict';

	function setup( nav ) {
		if ( nav.dataset.tbfeReady ) {
			return;
		}
		nav.dataset.tbfeReady = '1';

		var toggle = nav.querySelector( '.tbfe-nav__toggle' );
		var menu = nav.querySelector( '.tbfe-nav__menu' );
		if ( ! menu ) {
			return;
		}

		// A button for each item with a sub-menu (opens it on touch and keyboard).
		menu.querySelectorAll( '.menu-item-has-children > a' ).forEach( function ( link ) {
			var button = document.createElement( 'button' );
			button.type = 'button';
			button.className = 'tbfe-nav__sub-toggle';
			button.setAttribute( 'aria-expanded', 'false' );
			button.setAttribute( 'aria-label', link.textContent.trim() );
			button.innerHTML = '<span aria-hidden="true"></span>';
			link.after( button );
			button.addEventListener( 'click', function () {
				var item = button.parentElement;
				var open = item.classList.toggle( 'is-open' );
				button.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			} );
		} );

		if ( toggle ) {
			toggle.addEventListener( 'click', function () {
				var open = nav.classList.toggle( 'is-open' );
				toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			} );
		}

		nav.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' !== event.key ) {
				return;
			}
			var item = event.target.closest( '.is-open' );
			if ( item && item !== nav ) {
				item.classList.remove( 'is-open' );
				var sub = item.querySelector( '.tbfe-nav__sub-toggle' );
				if ( sub ) {
					sub.setAttribute( 'aria-expanded', 'false' );
					sub.focus();
				}
			} else if ( nav.classList.contains( 'is-open' ) && toggle ) {
				nav.classList.remove( 'is-open' );
				toggle.setAttribute( 'aria-expanded', 'false' );
				toggle.focus();
			}
		} );

		document.addEventListener( 'click', function ( event ) {
			if ( ! nav.contains( event.target ) ) {
				nav.querySelectorAll( '.menu-item.is-open' ).forEach( function ( item ) {
					item.classList.remove( 'is-open' );
				} );
			}
		} );
	}

	function init( root ) {
		( root || document ).querySelectorAll( '.tbfe-nav' ).forEach( setup );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', function () {
			init();
		} );
	} else {
		init();
	}

	// The Elementor editor renders widgets again after each change.
	window.addEventListener( 'elementor/frontend/init', function () {
		if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
			window.elementorFrontend.hooks.addAction( 'frontend/element_ready/tbfe-nav-menu.default', function ( $scope ) {
				init( $scope[ 0 ] );
			} );
		}
	} );
}() );
