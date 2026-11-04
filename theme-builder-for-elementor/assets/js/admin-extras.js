/**
 * Small additions of Theme Builder to other admin screens (TBFE\Admin::extras, TBFE\Library\News::notice):
 * - the "Websites Import" button after "Add New" of the page and post lists (window.tbfeExtras.listButton),
 * - the "Import design" button after "Edit with Elementor" in the editors (window.tbfeExtras.editorButton),
 * - closing the notice of new demos marks them as seen (window.tbfeExtras.ajaxUrl).
 */
( function () {
	var data = window.tbfeExtras || {};

	if ( data.listButton ) {
		var anchor = document.querySelector( '.wrap .page-title-action' ) || document.querySelector( '.wrap h1' );
		if ( anchor && ! document.querySelector( '.tbfe-list-import' ) ) {
			anchor.insertAdjacentHTML( 'afterend', data.listButton );
		}
	}

	if ( data.editorButton ) {
		// Elementor adds its button late (and the block editor may render the toolbar again): watch for it.
		var add = function () {
			var button = document.getElementById( 'elementor-switch-mode-button' );
			if ( button && ! ( button.nextElementSibling && button.nextElementSibling.classList.contains( 'tbfe-editor-import' ) ) ) {
				button.insertAdjacentHTML( 'afterend', data.editorButton );
			}
		};
		add();
		new MutationObserver( add ).observe( document.body, { childList: true, subtree: true } );
	}

	var notice = document.querySelector( '.tbfe-news' );
	if ( notice ) {
		// The close button (added by WordPress) marks the demos as seen.
		notice.addEventListener( 'click', function ( event ) {
			if ( event.target.closest( '.notice-dismiss' ) ) {
				var body = new FormData();
				body.append( 'action', 'tbfe_library_seen' );
				body.append( '_ajax_nonce', notice.getAttribute( 'data-nonce' ) );
				fetch( window.ajaxurl || data.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body } );
			}
		} );
	}
}() );
