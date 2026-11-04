/**
 * Theme Builder widget – Table of Contents: the links to the headings of the post. Registers itself in the core (widgets.js).
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

	// Table of Contents.
	function slug( text, used ) {
		var base =
			'tbfe-' +
			( text
				.toLowerCase()
				.normalize( 'NFD' )
				.replace( /[̀-ͯ]/g, '' )
				.replace( /[^a-z0-9]+/g, '-' )
				.replace( /^-+|-+$/g, '' )
				.slice( 0, 60 ) || 'heading' );
		var id = base;
		var i = 2;
		while ( used[ id ] || document.getElementById( id ) ) {
			id = base + '-' + i++;
		}
		used[ id ] = true;
		return id;
	}

	// Text of a heading without its decoration (aria-hidden parts, e.g. the visible word of rotating words).
	function headingText( h ) {
		var copy = h.cloneNode( true );
		copy.querySelectorAll( '[aria-hidden="true"]' ).forEach( function ( el ) {
			el.remove();
		} );
		return copy.textContent.replace( /\s+/g, ' ' ).trim();
	}

	function toc( nav ) {
		if ( ! once( nav, 'tbfeReady' ) ) {
			return;
		}
		var selector = nav.dataset.container;
		var root = null;
		try {
			root = selector ? document.querySelector( selector ) : null;
		} catch ( e ) {}
		root = root || document.querySelector( '.tbfe-post-content' ) || document.querySelector( '.entry-content' ) || document.querySelector( 'main' ) || document.body;

		var levels = ( nav.dataset.headings || 'h2,h3' ).split( ',' );
		var headings = Array.prototype.filter.call( root.querySelectorAll( levels.join( ',' ) ), function ( h ) {
			// Not the headings of the header, footer, lists of posts and post cards.
			return ! nav.contains( h ) && ! h.closest( '.tbfe-toc, .tbfe-location-header, .tbfe-location-footer, .elementor-widget-tbfe-posts, .tbfe-post-card' ) && headingText( h );
		} );
		var listBox = nav.querySelector( '.tbfe-toc__list' );
		if ( headings.length < ( parseInt( nav.dataset.minimum, 10 ) || 1 ) ) {
			if ( nav.dataset.editor ) {
				listBox.innerHTML = '<p class="tbfe-toc__empty"></p>';
				listBox.firstChild.textContent = 'The headings of the post are listed here.';
			} else {
				nav.hidden = true;
			}
			return;
		}

		var tag = nav.classList.contains( 'tbfe-toc--numbers' ) ? 'ol' : 'ul';
		var used = {};
		var links = [];
		var top = document.createElement( tag );
		// Levels of the open items: a heading goes into the sub-list of the last item with a higher level.
		var stack = [ { level: 0 } ];
		headings.forEach( function ( h ) {
			var level = parseInt( h.tagName.slice( 1 ), 10 );
			if ( ! h.id ) {
				h.id = slug( headingText( h ), used );
			}
			while ( stack.length > 1 && stack[ stack.length - 1 ].level >= level ) {
				stack.pop();
			}
			var parent = stack[ stack.length - 1 ];
			var list = top;
			if ( parent.li ) {
				if ( ! parent.sub ) {
					parent.sub = document.createElement( tag );
					parent.li.appendChild( parent.sub );
				}
				list = parent.sub;
			}
			var li = document.createElement( 'li' );
			var a = document.createElement( 'a' );
			a.href = '#' + h.id;
			a.textContent = headingText( h );
			li.appendChild( a );
			list.appendChild( li );
			stack.push( { level: level, li: li } );
			links.push( { a: a, h: h } );
		} );
		listBox.appendChild( top );
		var offset = parseInt( nav.dataset.offset, 10 ) || 0;

		links.forEach( function ( link ) {
			link.a.addEventListener( 'click', function ( event ) {
				event.preventDefault();
				scrollToY( link.h.getBoundingClientRect().top + window.pageYOffset - offset );
				if ( history.replaceState ) {
					history.replaceState( null, '', '#' + link.h.id );
				}
				link.h.setAttribute( 'tabindex', '-1' );
				link.h.focus( { preventScroll: true } );
			} );
		} );

		var toggle = nav.querySelector( '.tbfe-toc__toggle' );
		if ( toggle ) {
			toggle.addEventListener( 'click', function () {
				var collapsed = nav.classList.toggle( 'is-collapsed' );
				toggle.setAttribute( 'aria-expanded', collapsed ? 'false' : 'true' );
			} );
		}

		// The heading the visitor reads now.
		if ( 'IntersectionObserver' in window ) {
			var observer = new IntersectionObserver(
				function ( entries ) {
					entries.forEach( function ( entry ) {
						if ( entry.isIntersecting ) {
							links.forEach( function ( link ) {
								link.a.classList.toggle( 'is-active', link.h === entry.target );
							} );
						}
					} );
				},
				{ rootMargin: '-' + offset + 'px 0px -65% 0px' }
			);
			links.forEach( function ( link ) {
				observer.observe( link.h );
			} );
		}
	}

	T.register( '.tbfe-toc', 'tbfe-toc', toc );
}( window.tbfeWidgets ) );
