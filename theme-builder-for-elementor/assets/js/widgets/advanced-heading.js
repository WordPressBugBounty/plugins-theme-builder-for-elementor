/**
 * Theme Builder widget – Advanced Heading: the animations of the middle part. Registers itself in the core (widgets.js).
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

	// Advanced Heading.
	function heading( el ) {
		if ( ! once( el, 'tbfeReady' ) ) {
			return;
		}
		var data = {};
		try {
			data = JSON.parse( el.dataset.tbfeAh || '{}' );
		} catch ( e ) {}
		var middle = el.querySelector( '.tbfe-ah__middle' );
		var text = el.querySelector( '.tbfe-ah__text' );
		if ( ! middle || ! text || 'none' === data.animation || 'gradient' === data.animation ) {
			return;
		}
		var duration = parseFloat( getComputedStyle( el ).getPropertyValue( '--tbfe-ah-duration' ) ) || 1200;

		if ( [ 'highlight', 'underline', 'circle', 'strike' ].indexOf( data.animation ) !== -1 ) {
			var run = function () {
				el.classList.remove( 'is-animated' );
				void el.offsetWidth; // Restarts the transition.
				el.classList.add( 'is-animated' );
			};
			if ( reduceMotion ) {
				el.classList.add( 'is-animated' );
				return;
			}
			onVisible( el, function () {
				run();
				if ( data.loop ) {
					setInterval( run, duration + 2600 );
				}
			} );
			return;
		}

		var words = [];
		try {
			words = JSON.parse( middle.dataset.words || '[]' );
		} catch ( e ) {}
		if ( words.length < 2 && 'rotate' === data.animation ) {
			return;
		}
		if ( ! words.length ) {
			words = [ text.textContent ];
		}
		var pause = parseInt( data.pause, 10 ) || 2000;
		var index = 0;

		if ( reduceMotion ) {
			// Without motion the words change, but without typing or sliding.
			if ( words.length > 1 ) {
				setInterval( function () {
					index = ( index + 1 ) % words.length;
					text.textContent = words[ index ];
				}, pause + duration );
			}
			return;
		}

		if ( 'rotate' === data.animation ) {
			// Room for the longest word, so the rest of the heading does not jump.
			var widest = 0;
			words.forEach( function ( word ) {
				text.textContent = word;
				widest = Math.max( widest, text.getBoundingClientRect().width );
			} );
			text.textContent = words[ 0 ];
			middle.style.minWidth = Math.ceil( widest ) + 'px';
			var step = function () {
				if ( ! data.loop && index === words.length - 1 ) {
					return;
				}
				text.classList.add( 'is-out' );
				setTimeout( function () {
					index = ( index + 1 ) % words.length;
					text.textContent = words[ index ];
					text.classList.remove( 'is-out' );
					text.classList.add( 'is-in' );
					void text.offsetWidth;
					text.classList.remove( 'is-in' );
					setTimeout( step, pause );
				}, 320 );
			};
			onVisible( el, function () {
				setTimeout( step, pause );
			} );
			return;
		}

		// Typing: types the word, waits, deletes it and types the next one.
		var speed = Math.max( 30, duration / Math.max( 4, words[ 0 ].length * 2 ) );
		function type( word, i, then ) {
			text.textContent = word.slice( 0, i );
			if ( i < word.length ) {
				setTimeout( function () {
					type( word, i + 1, then );
				}, speed );
			} else {
				then();
			}
		}
		function erase( word, i, then ) {
			text.textContent = word.slice( 0, i );
			if ( i > 0 ) {
				setTimeout( function () {
					erase( word, i - 1, then );
				}, speed / 2 );
			} else {
				then();
			}
		}
		function next() {
			var word = words[ index ];
			type( word, 0, function () {
				if ( ! data.loop && index === words.length - 1 ) {
					el.classList.add( 'is-done' );
					return;
				}
				setTimeout( function () {
					erase( word, word.length, function () {
						index = ( index + 1 ) % words.length;
						next();
					} );
				}, pause );
			} );
		}
		text.textContent = '';
		onVisible( el, next );
	}

	T.register( '.tbfe-ah', 'tbfe-advanced-heading', heading );
}( window.tbfeWidgets ) );
