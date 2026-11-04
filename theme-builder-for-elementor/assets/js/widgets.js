/**
 * Core of the scripts of the Theme Builder widgets. Every widget with a script has its own small file
 * (assets/js/widgets/*.js) that registers itself here, so a page loads only the scripts of its widgets. The core runs
 * them on the site and every time the Elementor editor renders the widget again, and keeps one scroll listener.
 */
( function () {
	'use strict';

	if ( window.tbfeWidgets ) {
		return;
	}

	var reduceMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var scrollers = [];
	var handlers = [];
	var ready = 'loading' !== document.readyState;
	var ticking = false;

	function once( el, key ) {
		if ( el.dataset[ key ] ) {
			return false;
		}
		el.dataset[ key ] = '1';
		return true;
	}

	function onVisible( el, callback ) {
		if ( ! ( 'IntersectionObserver' in window ) ) {
			callback();
			return;
		}
		var observer = new IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						observer.disconnect();
						callback();
					}
				} );
			},
			{ threshold: 0.4 }
		);
		observer.observe( el );
	}

	function scrollToY( y ) {
		window.scrollTo( { top: y, behavior: reduceMotion ? 'auto' : 'smooth' } );
	}

	// Scroll progress of an element (or of the page) from 0 to 1.
	// Back to Top and Reading Progress: updated on scroll.
	function progressOf( target ) {
		var doc = document.documentElement;
		if ( target ) {
			var rect = target.getBoundingClientRect();
			var total = rect.height - window.innerHeight;
			return total > 0 ? Math.min( 1, Math.max( 0, -rect.top / total ) ) : ( rect.top < 0 ? 1 : 0 );
		}
		var max = doc.scrollHeight - window.innerHeight;
		return max > 0 ? Math.min( 1, window.pageYOffset / max ) : 0;
	}

	function update() {
		scrollers.forEach( function ( fn ) {
			fn();
		} );
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
	window.addEventListener( 'resize', update );

	function run( handler, scope ) {
		( scope || document ).querySelectorAll( handler.selector ).forEach( handler.init );
		update();
	}

	// Elementor editor: widgets are rendered again after every change.
	function hook( handler ) {
		if ( handler.hooked || ! window.elementorFrontend || ! window.elementorFrontend.hooks ) {
			return;
		}
		handler.hooked = true;
		window.elementorFrontend.hooks.addAction( 'frontend/element_ready/' + handler.widget + '.default', function ( $scope ) {
			run( handler, $scope[ 0 ] );
		} );
	}
	window.addEventListener( 'elementor/frontend/init', function () {
		handlers.forEach( hook );
	} );
	document.addEventListener( 'DOMContentLoaded', function () {
		ready = true;
		handlers.forEach( function ( handler ) {
			run( handler, document );
		} );
	} );

	window.tbfeWidgets = {
		reduceMotion: reduceMotion,
		once: once,
		onVisible: onVisible,
		scrollToY: scrollToY,
		progressOf: progressOf,
		onScroll: function ( fn ) {
			scrollers.push( fn );
			fn();
		},
		/**
		 * @param {string}   selector CSS selector of the widget markup.
		 * @param {string}   widget   Name of the Elementor widget.
		 * @param {Function} init     Called once for every element.
		 */
		register: function ( selector, widget, init ) {
			var handler = { selector: selector, widget: widget, init: init };
			handlers.push( handler );
			if ( ready ) {
				run( handler, document );
			}
			hook( handler );
		},
	};
}() );
