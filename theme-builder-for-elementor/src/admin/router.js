import { useEffect, useState } from '@wordpress/element';

/**
 * Hash router: #/templates/header → [ 'templates', 'header' ].
 */
const read = () =>
	window.location.hash
		.replace( /^#\/?/, '' )
		.split( '/' )
		.filter( Boolean );

export function useRoute() {
	const [ route, setRoute ] = useState( read );

	useEffect( () => {
		const onChange = () => {
			setRoute( read() );
			window.scrollTo( 0, 0 );
		};
		window.addEventListener( 'hashchange', onChange );
		return () => window.removeEventListener( 'hashchange', onChange );
	}, [] );

	// Highlights the matching item of the WordPress admin menu.
	useEffect( () => {
		const page = route[ 0 ] || '';
		document.querySelectorAll( '#toplevel_page_tbfe .wp-submenu a' ).forEach( ( link ) => {
			const href = link.getAttribute( 'href' ) || '';
			const target = href.includes( '#/' ) ? href.split( '#/' )[ 1 ] : '';
			link.parentElement.classList.toggle( 'current', target === page );
		} );
	}, [ route ] );

	return route;
}

export const go = ( path ) => {
	window.location.hash = '#/' + path;
};
