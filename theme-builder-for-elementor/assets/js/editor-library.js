/**
 * Theme Builder Library in the Elementor editor: a button in every "add section" area opens the demo library; the
 * chosen section, template or demo page is inserted at that place with Elementor's import command (Ctrl+Z works).
 */
( function ( $ ) {
	'use strict';

	var config = window.tbfeEditorLibrary || {};
	var t = config.i18n || {};
	var LOGO =
		'<svg viewBox="0 0 32 32" aria-hidden="true" focusable="false"><rect x="2" y="2" width="28" height="8" rx="2.5" fill="currentColor" opacity=".9"/><rect x="2" y="12" width="11" height="18" rx="2.5" fill="currentColor" opacity=".55"/><rect x="15" y="12" width="15" height="8" rx="2.5" fill="currentColor"/><rect x="15" y="22" width="15" height="8" rx="2.5" fill="currentColor" opacity=".55"/></svg>';

	var state = {
		index: null,
		tab: 'sections',
		type: '',
		search: '',
		at: undefined,
		addSection: null,
		busy: false,
		siteColors: false,
	};
	try {
		state.siteColors = '1' === window.localStorage.getItem( 'tbfeElibSiteColors' );
	} catch ( e ) {}
	var modal = null;

	function esc( text ) {
		return String( text === undefined || text === null ? '' : text ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ];
		} );
	}

	function api( path, body ) {
		return fetch( config.rest + path, {
			method: body ? 'POST' : 'GET',
			credentials: 'same-origin',
			headers: { 'X-WP-Nonce': config.nonce, 'Content-Type': 'application/json' },
			body: body ? JSON.stringify( body ) : undefined,
		} ).then( function ( response ) {
			return response.json().then( function ( data ) {
				if ( ! response.ok ) {
					throw new Error( ( data && data.message ) || t.loadError );
				}
				return data;
			} );
		} );
	}

	// Template type of the open document ("tbfe-header" → "header"), '' for other documents.
	function documentType() {
		var doc = window.elementor && elementor.documents && elementor.documents.getCurrent();
		var type = doc && doc.config ? doc.config.type : '';
		return type && 0 === type.indexOf( 'tbfe-' ) ? type.slice( 5 ) : '';
	}

	// The button next to "Add Template" in the "add section" area.
	function addButtonToTemplate() {
		var template = document.getElementById( 'tmpl-elementor-add-section' );
		if ( ! template || -1 !== template.innerHTML.indexOf( 'tbfe-library-button' ) ) {
			return;
		}
		var button =
			'<button type="button" class="elementor-add-section-area-button tbfe-library-button" data-tooltip="' +
			esc( t.button ) +
			'" aria-label="' +
			esc( t.button ) +
			'">' +
			LOGO +
			'</button>';
		template.innerHTML = template.innerHTML.replace( '<div class="elementor-add-section-drag-title"', button + '<div class="elementor-add-section-drag-title"' );
	}

	function listenInPreview() {
		elementor.$previewContents.off( 'click.tbfe' ).on( 'click.tbfe', '.tbfe-library-button', function ( event ) {
			event.preventDefault();
			event.stopPropagation();
			var addSection = this.closest( '.elementor-add-section' );
			var at;
			// An "add section" area between sections: insert at its place (the one at the end adds to the end).
			if ( addSection && 'elementor-add-new-section' !== addSection.id ) {
				at = $( addSection ).prevAll( '.elementor-element' ).length;
			}
			open( at, addSection );
		} );
	}

	function build() {
		modal = document.createElement( 'div' );
		modal.className = 'tbfe-elib';
		modal.hidden = true;
		modal.innerHTML =
			'<div class="tbfe-elib__dialog" role="dialog" aria-modal="true" aria-labelledby="tbfe-elib-title">' +
			'<header class="tbfe-elib__head">' +
			'<span class="tbfe-elib__logo">' +
			LOGO +
			'</span>' +
			'<h2 id="tbfe-elib-title">' +
			esc( t.title ) +
			'</h2>' +
			'<nav class="tbfe-elib__tabs" role="tablist">' +
			'<button type="button" role="tab" data-tab="sections">' +
			esc( t.sections ) +
			'</button>' +
			'<button type="button" role="tab" data-tab="templates">' +
			esc( t.templates ) +
			'</button>' +
			'<button type="button" role="tab" data-tab="pages">' +
			esc( t.pages ) +
			'</button>' +
			'</nav>' +
			'<button type="button" class="tbfe-elib__close" aria-label="' +
			esc( t.close ) +
			'">&times;</button>' +
			'</header>' +
			'<div class="tbfe-elib__tools">' +
			'<input type="search" class="tbfe-elib__search" placeholder="' +
			esc( t.search ) +
			'" aria-label="' +
			esc( t.search ) +
			'">' +
			'<select class="tbfe-elib__type" aria-label="' +
			esc( t.templates ) +
			'"></select>' +
			'<label class="tbfe-elib__colors" title="' +
			esc( t.siteHelp ) +
			'"><input type="checkbox" class="tbfe-elib__site-colors"> ' +
			esc( t.siteColors ) +
			'</label>' +
			'<span class="tbfe-elib__note">' +
			esc( t.imagesNote ) +
			'</span>' +
			'<a class="tbfe-elib__manage" target="_blank" rel="noreferrer" href="' +
			esc( config.libraryUrl ) +
			'">' +
			esc( t.manage ) +
			'</a>' +
			'</div>' +
			'<div class="tbfe-elib__body" tabindex="-1"></div>' +
			'</div>';
		document.body.appendChild( modal );

		modal.addEventListener( 'click', function ( event ) {
			if ( event.target === modal || event.target.closest( '.tbfe-elib__close' ) ) {
				close();
				return;
			}
			var tab = event.target.closest( '[data-tab]' );
			if ( tab ) {
				state.tab = tab.dataset.tab;
				render();
				return;
			}
			var insert = event.target.closest( '[data-insert]' );
			if ( insert && ! insert.disabled ) {
				doInsert( insert );
			}
		} );
		modal.querySelector( '.tbfe-elib__search' ).addEventListener( 'input', function ( event ) {
			state.search = event.target.value.toLowerCase();
			render();
		} );
		var siteColors = modal.querySelector( '.tbfe-elib__site-colors' );
		siteColors.checked = state.siteColors;
		siteColors.addEventListener( 'change', function () {
			state.siteColors = siteColors.checked;
			try {
				window.localStorage.setItem( 'tbfeElibSiteColors', state.siteColors ? '1' : '0' );
			} catch ( e ) {}
		} );
		modal.querySelector( '.tbfe-elib__type' ).addEventListener( 'change', function ( event ) {
			state.type = event.target.value;
			render();
		} );
		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key && modal && ! modal.hidden ) {
				close();
			}
		} );
	}

	function open( at, addSection ) {
		if ( ! modal ) {
			build();
		}
		state.at = at;
		state.addSection = addSection;
		// In a template of a part (header, single…) the demos of that part first, else the sections.
		var type = documentType();
		if ( type && 'section' !== type ) {
			state.tab = 'templates';
			state.type = type;
		} else {
			state.tab = 'sections';
		}
		modal.hidden = false;
		document.body.classList.add( 'tbfe-elib-open' );
		modal.querySelector( '.tbfe-elib__search' ).value = state.search;
		render();
		modal.querySelector( '.tbfe-elib__search' ).focus();
		if ( ! state.index ) {
			api( 'library' )
				.then( function ( index ) {
					state.index = index;
					render();
				} )
				.catch( function ( error ) {
					body().innerHTML = '<p class="tbfe-elib__message">' + esc( error.message ) + '</p>';
				} );
		}
	}

	function close() {
		modal.hidden = true;
		document.body.classList.remove( 'tbfe-elib-open' );
	}

	function body() {
		return modal.querySelector( '.tbfe-elib__body' );
	}

	function matches( entry ) {
		if ( ! state.search ) {
			return true;
		}
		var text = [ entry.title, entry.description, entry.site, ( entry.tags || [] ).join( ' ' ) ].join( ' ' ).toLowerCase();
		return -1 !== text.indexOf( state.search );
	}

	// Cards of the current tab.
	function entries() {
		var index = state.index;
		if ( 'pages' === state.tab ) {
			var pages = [];
			index.sites.forEach( function ( site ) {
				site.pages.forEach( function ( page ) {
					pages.push( {
						kind: 'page',
						id: page.id,
						title: page.title,
						site: site.title,
						label: site.title,
						image: page.image,
						preview: page.preview,
						pro: site.pro,
						tall: true,
					} );
				} );
			} );
			return pages;
		}
		return index.items
			.filter( function ( item ) {
				if ( 'sections' === state.tab ) {
					return 'section' === item.type;
				}
				return 'section' !== item.type && ( ! state.type || item.type === state.type );
			} )
			.map( function ( item ) {
				return {
					kind: 'item',
					id: item.id,
					title: item.title,
					description: item.description,
					tags: item.tags,
					label: config.types[ item.type ] || item.type,
					image: item.image,
					preview: item.preview,
					pro: item.pro,
				};
			} );
	}

	function typeOptions() {
		var select = modal.querySelector( '.tbfe-elib__type' );
		select.hidden = 'templates' !== state.tab;
		if ( select.hidden || ! state.index ) {
			return;
		}
		var used = {};
		state.index.items.forEach( function ( item ) {
			if ( 'section' !== item.type ) {
				used[ item.type ] = true;
			}
		} );
		var html = '<option value="">' + esc( t.allTypes ) + '</option>';
		Object.keys( config.types ).forEach( function ( slug ) {
			if ( used[ slug ] ) {
				html += '<option value="' + esc( slug ) + '"' + ( slug === state.type ? ' selected' : '' ) + '>' + esc( config.types[ slug ] ) + '</option>';
			}
		} );
		select.innerHTML = html;
	}

	function card( entry ) {
		var locked = entry.pro && ! config.pro;
		var image = entry.image
			? '<img src="' +
			  esc( entry.image.src ) +
			  '" srcset="' +
			  esc( entry.image.srcset ) +
			  '" sizes="260px" alt="" loading="lazy" style="background-image:url(' +
			  esc( entry.image.lqip ) +
			  ')">'
			: '<span class="tbfe-elib__noimage">' + LOGO + '</span>';
		return (
			'<article class="tbfe-elib__card' +
			( entry.tall ? ' is-tall' : '' ) +
			( locked ? ' is-locked' : '' ) +
			'">' +
			'<div class="tbfe-elib__thumb">' +
			image +
			'<span class="tbfe-elib__chip">' +
			esc( entry.label ) +
			'</span>' +
			( entry.pro ? '<span class="tbfe-elib__pro">' + esc( t.pro ) + '</span>' : '' ) +
			'<div class="tbfe-elib__actions">' +
			( entry.preview ? '<a class="tbfe-elib__btn tbfe-elib__btn--ghost" href="' + esc( entry.preview ) + '" target="_blank" rel="noreferrer">' + esc( t.preview ) + '</a>' : '' ) +
			'<button type="button" class="tbfe-elib__btn" data-insert="' +
			esc( entry.kind + ':' + entry.id ) +
			'"' +
			( locked ? ' disabled title="' + esc( t.proLocked ) + '"' : '' ) +
			'>' +
			esc( locked ? t.proLocked : t.insert ) +
			'</button>' +
			'</div>' +
			'</div>' +
			'<h3>' +
			esc( entry.title ) +
			'</h3>' +
			'</article>'
		);
	}

	function render() {
		modal.querySelectorAll( '[data-tab]' ).forEach( function ( tab ) {
			var active = tab.dataset.tab === state.tab;
			tab.classList.toggle( 'is-active', active );
			tab.setAttribute( 'aria-selected', active ? 'true' : 'false' );
		} );
		typeOptions();
		if ( ! state.index ) {
			body().innerHTML = '<p class="tbfe-elib__message"><span class="tbfe-elib__spinner"></span>' + esc( t.loading ) + '</p>';
			return;
		}
		var list = entries().filter( matches );
		body().innerHTML = list.length ? '<div class="tbfe-elib__grid">' + list.map( card ).join( '' ) + '</div>' : '<p class="tbfe-elib__message">' + esc( t.empty ) + '</p>';
	}

	function doInsert( button ) {
		if ( state.busy ) {
			return;
		}
		var parts = button.dataset.insert.split( ':' );
		state.busy = true;
		button.disabled = true;
		button.textContent = t.inserting;
		modal.classList.add( 'is-busy' );

		api( 'library/content', { kind: parts[ 0 ], id: parseInt( parts[ 1 ], 10 ), colors: state.siteColors ? 'site' : null } )
			.then( function ( result ) {
				var options = undefined !== state.at ? { at: state.at } : {};
				$e.run( 'document/elements/import', {
					model: new Backbone.Model( { title: result.title } ),
					data: { content: result.content },
					options: options,
				} );
				// The "add section" area between sections closes after the insert, like with Elementor's templates.
				if ( state.addSection && 'elementor-add-new-section' !== state.addSection.id ) {
					var closeButton = state.addSection.querySelector( '.elementor-add-section-close' );
					if ( closeButton ) {
						closeButton.click();
					}
				}
				close();
				if ( elementor.notifications && elementor.notifications.showToast ) {
					elementor.notifications.showToast( { message: t.inserted } );
				}
			} )
			.catch( function ( error ) {
				if ( elementor.notifications && elementor.notifications.showToast ) {
					elementor.notifications.showToast( { message: ( error && error.message ) || t.insertError } );
				}
			} )
			.then( function () {
				state.busy = false;
				button.disabled = false;
				button.textContent = t.insert;
				modal.classList.remove( 'is-busy' );
			} );
	}

	$( window ).on( 'elementor:init', function () {
		addButtonToTemplate();
		elementor.on( 'preview:loaded', listenInPreview );
	} );
}( jQuery ) );
