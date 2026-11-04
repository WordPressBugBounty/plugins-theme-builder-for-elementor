/**
 * Theme Builder widget – Slider: every slide is a Section template. Registers itself in the core (widgets.js).
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

	// Slider: every slide is a Section template.
	function slider( el ) {
		if ( ! once( el, 'tbfeReady' ) ) {
			return;
		}
		var o = {};
		try {
			o = JSON.parse( el.dataset.tbfeSlider || '{}' );
		} catch ( e ) {}
		var viewport = el.querySelector( '.tbfe-slider__viewport' );
		var track = el.querySelector( '.tbfe-slider__track' );
		var slides = Array.prototype.slice.call( track.children );
		var prev = el.querySelector( '.tbfe-slider__arrow--prev' );
		var next = el.querySelector( '.tbfe-slider__arrow--next' );
		var dotsBox = el.querySelector( '.tbfe-slider__dots' );
		var play = el.querySelector( '.tbfe-slider__play' );
		var fade = 'fade' === o.effect;
		var index = 0;
		var timer = null;
		var hovered = false;
		// The visitor stopped it (or does not want motion).
		var stopped = reduceMotion;

		function perView() {
			if ( fade ) {
				return 1;
			}
			var value = parseInt( getComputedStyle( el ).getPropertyValue( '--tbfe-per-view' ), 10 ) || 1;
			return Math.max( 1, Math.min( value, slides.length ) );
		}
		function lastIndex() {
			return Math.max( 0, slides.length - perView() );
		}

		function dots() {
			if ( ! dotsBox ) {
				return;
			}
			var count = lastIndex() + 1;
			if ( dotsBox.children.length !== count ) {
				dotsBox.innerHTML = '';
				for ( var i = 0; i < count; i++ ) {
					var dot = document.createElement( 'button' );
					dot.type = 'button';
					dot.className = 'tbfe-slider__dot';
					dot.setAttribute( 'aria-label', ( dotsBox.dataset.label || '%d' ).replace( '%d', i + 1 ) );
					dot.dataset.index = String( i );
					dotsBox.appendChild( dot );
				}
			}
			Array.prototype.forEach.call( dotsBox.children, function ( dot, i ) {
				var active = i === index;
				dot.classList.toggle( 'is-active', active );
				if ( active ) {
					dot.setAttribute( 'aria-current', 'true' );
				} else {
					dot.removeAttribute( 'aria-current' );
				}
			} );
		}

		function go( i ) {
			var max = lastIndex();
			if ( i > max ) {
				i = o.loop ? 0 : max;
			}
			if ( i < 0 ) {
				i = o.loop ? max : 0;
			}
			index = i;
			var view = perView();
			if ( ! fade ) {
				track.style.transform = 'translate3d(' + -( slides[ index ] ? slides[ index ].offsetLeft : 0 ) + 'px, 0, 0)';
			}
			slides.forEach( function ( slide, k ) {
				var visible = k >= index && k < index + view;
				slide.classList.toggle( 'is-active', visible );
				slide.setAttribute( 'aria-hidden', visible ? 'false' : 'true' );
				// Links of hidden slides are not reachable with the keyboard.
				slide.inert = ! visible;
			} );
			if ( prev ) {
				prev.disabled = ! o.loop && 0 === index;
			}
			if ( next ) {
				next.disabled = ! o.loop && max === index;
			}
			dots();
		}

		function stop() {
			clearInterval( timer );
			timer = null;
		}
		function start() {
			stop();
			if ( o.autoplay && ! stopped && ! hovered && ! document.hidden && slides.length > 1 ) {
				timer = setInterval( function () {
					go( index + 1 );
				}, o.autoplay );
			}
		}
		function restart() {
			if ( timer ) {
				start();
			}
		}

		if ( prev ) {
			prev.addEventListener( 'click', function () {
				go( index - 1 );
				restart();
			} );
		}
		if ( next ) {
			next.addEventListener( 'click', function () {
				go( index + 1 );
				restart();
			} );
		}
		if ( dotsBox ) {
			dotsBox.addEventListener( 'click', function ( event ) {
				var dot = event.target.closest( '.tbfe-slider__dot' );
				if ( dot ) {
					go( parseInt( dot.dataset.index, 10 ) );
					restart();
				}
			} );
		}
		if ( play ) {
			var label = function () {
				play.classList.toggle( 'is-paused', stopped );
				play.setAttribute( 'aria-label', stopped ? play.dataset.play : play.dataset.pause );
			};
			label();
			play.addEventListener( 'click', function () {
				stopped = ! stopped;
				label();
				start();
			} );
		}
		if ( o.pause ) {
			el.addEventListener( 'mouseenter', function () {
				hovered = true;
				stop();
			} );
			el.addEventListener( 'mouseleave', function () {
				hovered = false;
				start();
			} );
		}
		// Keyboard focus inside stops the autoplay (the visitor reads or uses a link).
		el.addEventListener( 'focusin', function () {
			hovered = true;
			stop();
		} );
		el.addEventListener( 'focusout', function ( event ) {
			if ( ! el.contains( event.relatedTarget ) ) {
				hovered = false;
				start();
			}
		} );
		el.addEventListener( 'keydown', function ( event ) {
			if ( 'ArrowLeft' === event.key || 'ArrowRight' === event.key ) {
				if ( event.target.closest( 'input, textarea, select' ) ) {
					return;
				}
				event.preventDefault();
				go( index + ( 'ArrowRight' === event.key ? 1 : -1 ) );
			}
		} );
		document.addEventListener( 'visibilitychange', start );
		window.addEventListener( 'resize', function () {
			go( index );
		} );

		// Swipe with a finger or drag with the mouse.
		var startX = null;
		var moved = 0;
		viewport.addEventListener( 'pointerdown', function ( event ) {
			if ( slides.length < 2 || ( 'mouse' === event.pointerType && 0 !== event.button ) ) {
				return;
			}
			startX = event.clientX;
			moved = 0;
		} );
		viewport.addEventListener( 'pointermove', function ( event ) {
			if ( null === startX ) {
				return;
			}
			moved = event.clientX - startX;
			if ( Math.abs( moved ) > 6 && ! fade ) {
				el.classList.add( 'is-dragging' );
				var base = slides[ index ] ? slides[ index ].offsetLeft : 0;
				track.style.transform = 'translate3d(' + ( moved - base ) + 'px, 0, 0)';
			}
		} );
		var release = function () {
			if ( null === startX ) {
				return;
			}
			el.classList.remove( 'is-dragging' );
			startX = null;
			if ( Math.abs( moved ) > 50 ) {
				go( index + ( moved < 0 ? 1 : -1 ) );
				restart();
			} else {
				go( index );
			}
		};
		viewport.addEventListener( 'pointerup', release );
		viewport.addEventListener( 'pointercancel', release );
		viewport.addEventListener( 'pointerleave', release );
		// A drag is not a click on a link of the slide.
		viewport.addEventListener(
			'click',
			function ( event ) {
				if ( Math.abs( moved ) > 6 ) {
					event.preventDefault();
					event.stopPropagation();
					moved = 0;
				}
			},
			true
		);
		viewport.addEventListener( 'dragstart', function ( event ) {
			event.preventDefault();
		} );

		el.classList.add( 'is-ready' );
		go( 0 );
		start();
	}

	T.register( '.tbfe-slider', 'tbfe-slider', slider );
}( window.tbfeWidgets ) );
