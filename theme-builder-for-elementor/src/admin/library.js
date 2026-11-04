import { __, sprintf } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';
import { api } from './api';
import { useStore } from './store';
import Icon from './components/Icon';

/**
 * Previews shown from the demos site are kept on this site afterwards (one request for all of them).
 */
const keepShown = ( index ) => {
	const keys = [];
	const add = ( image ) => image && image.remote && keys.push( image.key );
	index.items.forEach( ( item ) => add( item.image ) );
	index.sites.forEach( ( site ) => {
		add( site.image );
		site.pages.forEach( ( page ) => add( page.image ) );
	} );
	if ( keys.length ) {
		// After the images were shown, so the copy does not slow the page down.
		setTimeout( () => api.keepPreviews( [ ...new Set( keys ) ] ).catch( () => {} ), 3000 );
	}
};

/**
 * The library: loaded by the server once a day; refresh by hand at most every 6 hours.
 */
export function useLibrary() {
	const { data, toast } = useStore();
	const [ index, setIndex ] = useState( null );
	const [ error, setError ] = useState( '' );
	const [ busy, setBusy ] = useState( false );

	const load = ( refresh = false ) => {
		setBusy( true );
		if ( ! refresh ) {
			setIndex( null );
		}
		setError( '' );
		api.library( refresh )
			.then( ( result ) => {
				setIndex( result );
				keepShown( result );
				if ( refresh ) {
					toast( __( 'The library is up to date.', 'theme-builder-for-elementor' ) );
				}
			} )
			.catch( ( e ) => ( refresh && index ? toast( e.message, 'error' ) : setError( e.message ) ) )
			.finally( () => setBusy( false ) );
	};

	useEffect( () => {
		if ( data.settings.library ) {
			load();
		}
	}, [] ); // eslint-disable-line react-hooks/exhaustive-deps

	return { index, error, busy, load };
}

const time = ( iso ) => new Date( iso ).toLocaleString( [], { dateStyle: 'medium', timeStyle: 'short' } );

/**
 * "Updated …" and the refresh button (disabled until the 6 hours are over).
 */
export function RefreshButton( { library } ) {
	const { index, busy, load } = library;
	const next = index?.next_manual;
	let title = __( 'Load the newest designs from demos.blocks-wp.com', 'theme-builder-for-elementor' );
	if ( next ) {
		/* translators: %s: date and time */
		title = sprintf( __( 'Available again at %s', 'theme-builder-for-elementor' ), time( next ) );
	}
	return (
		<div className="tbfe-refresh">
			{ index?.loaded && (
				<span className="tbfe-muted">
					{ /* translators: %s: date and time */ sprintf( __( 'Updated %s', 'theme-builder-for-elementor' ), time( index.loaded ) ) }
				</span>
			) }
			<button type="button" className="tbfe-btn tbfe-btn--ghost tbfe-btn--sm" onClick={ () => load( true ) } disabled={ busy || !! next } title={ title }>
				<Icon name="refresh" size={ 15 } />
				{ busy ? __( 'Loading…', 'theme-builder-for-elementor' ) : __( 'Refresh library', 'theme-builder-for-elementor' ) }
			</button>
		</div>
	);
}

/**
 * Preview image with the tiny placeholder behind it and both widths.
 */
export function PreviewImage( { image, sizes = '(max-width: 782px) 100vw, 480px' } ) {
	if ( ! image ) {
		return null;
	}
	return (
		<img
			className="tbfe-preview-img"
			src={ image.src }
			srcSet={ image.srcset }
			sizes={ sizes }
			alt=""
			loading="lazy"
			decoding="async"
			style={ image.lqip ? { backgroundImage: `url("${ image.lqip }")` } : undefined }
		/>
	);
}

/**
 * Tall preview of a website or page: on hover it scrolls down to show more of the demo.
 */
export function ScrollPreview( { image } ) {
	if ( ! image ) {
		return null;
	}
	const layers = [ `url("${ image.src }")` ];
	if ( image.lqip ) {
		layers.push( `url("${ image.lqip }")` );
	}
	return <span className="tbfe-scroll-thumb" style={ { backgroundImage: layers.join( ', ' ) } } aria-hidden="true" />;
}
