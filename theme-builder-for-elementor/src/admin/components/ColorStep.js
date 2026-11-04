import { __ } from '@wordpress/i18n';
import { Spinner, ToggleControl } from '@wordpress/components';
import { useEffect, useMemo, useState } from '@wordpress/element';
import { api } from '../api';
import Icon from './Icon';

/**
 * Colors of demos before the import: presets (original, the site's global colors, ready palettes) or an own color for
 * each color of the demo. Shades follow their main color. With "globals" the main colors are linked to the global
 * colors of Elementor, which get the chosen colors.
 *
 * onChange gets the payload for the import requests: null (original colors) or { map, links, globals }.
 */

const ROLES = {
	primary: __( 'Main color', 'theme-builder-for-elementor' ),
	secondary: __( 'Second color', 'theme-builder-for-elementor' ),
	accent: __( 'Accent', 'theme-builder-for-elementor' ),
	heading: __( 'Headings', 'theme-builder-for-elementor' ),
	text: __( 'Text', 'theme-builder-for-elementor' ),
	background: __( 'Light background', 'theme-builder-for-elementor' ),
	tint: __( 'Shade', 'theme-builder-for-elementor' ),
	border: __( 'Lines', 'theme-builder-for-elementor' ),
	white: __( 'White', 'theme-builder-for-elementor' ),
	other: __( 'Other', 'theme-builder-for-elementor' ),
};

const PALETTES = [
	{ name: __( 'Ocean', 'theme-builder-for-elementor' ), colors: { primary: '#1d4ed8', secondary: '#0891b2', accent: '#f59e0b', heading: '#0f172a', text: '#334155', background: '#f1f5f9' } },
	{ name: __( 'Forest', 'theme-builder-for-elementor' ), colors: { primary: '#15803d', secondary: '#4d7c0f', accent: '#eab308', heading: '#14231a', text: '#3f4f44', background: '#f3f7f2' } },
	{ name: __( 'Sunset', 'theme-builder-for-elementor' ), colors: { primary: '#e11d48', secondary: '#ea580c', accent: '#facc15', heading: '#1f1720', text: '#4b3f47', background: '#fff7f3' } },
	{ name: __( 'Violet', 'theme-builder-for-elementor' ), colors: { primary: '#7c3aed', secondary: '#db2777', accent: '#06b6d4', heading: '#1e1b2e', text: '#4a4560', background: '#f6f3ff' } },
	{ name: __( 'Graphite', 'theme-builder-for-elementor' ), colors: { primary: '#111827', secondary: '#4b5563', accent: '#f59e0b', heading: '#030712', text: '#374151', background: '#f3f4f6' } },
];

// Color math (the same as TBFE\Library\Colors on the server).
const hsl = ( hex ) => {
	const [ r, g, b ] = [ 1, 3, 5 ].map( ( i ) => parseInt( hex.slice( i, i + 2 ), 16 ) / 255 );
	const max = Math.max( r, g, b );
	const min = Math.min( r, g, b );
	const l = ( max + min ) / 2;
	const d = max - min;
	if ( ! d ) {
		return [ 0, 0, l ];
	}
	const s = l > 0.5 ? d / ( 2 - max - min ) : d / ( max + min );
	let h;
	if ( max === r ) {
		h = ( g - b ) / d + ( g < b ? 6 : 0 );
	} else if ( max === g ) {
		h = ( b - r ) / d + 2;
	} else {
		h = ( r - g ) / d + 4;
	}
	return [ h * 60, s, l ];
};
const toHex = ( h, s, l ) => {
	const k = ( n ) => ( n + h / 30 ) % 12;
	const a = s * Math.min( l, 1 - l );
	const f = ( n ) => l - a * Math.max( -1, Math.min( k( n ) - 3, 9 - k( n ), 1 ) );
	return '#' + [ 0, 8, 4 ].map( ( n ) => Math.round( f( n ) * 255 ).toString( 16 ).padStart( 2, '0' ) ).join( '' );
};
// A shade after its main color changed: the new hue, the saturation in the same proportion, its own lightness.
const follow = ( tint, oldBase, newBase ) => {
	const [ , ts, tl ] = hsl( tint );
	const [ , os ] = hsl( oldBase );
	const [ nh, ns ] = hsl( newBase );
	return toHex( nh, os > 0 ? Math.min( 1, ( ts * ns ) / os ) : ts, tl );
};

export default function ColorStep( { entries, preview, defaultGlobals = false, onChange } ) {
	const [ palette, setPalette ] = useState( null );
	const [ site, setSite ] = useState( {} );
	const [ error, setError ] = useState( '' );
	const [ targets, setTargets ] = useState( {} );
	const [ manual, setManual ] = useState( {} );
	const [ preset, setPreset ] = useState( 'original' );
	const [ globals, setGlobals ] = useState( defaultGlobals );
	const key = JSON.stringify( entries );

	useEffect( () => {
		setPalette( null );
		api.palette( entries )
			.then( ( result ) => {
				setPalette( result.palette || [] );
				setSite( result.site || {} );
			} )
			.catch( ( e ) => setError( e.message ) );
	}, [ key ] );

	// Main colors first, then the rest by use.
	const order = [ 'primary', 'secondary', 'accent', 'heading', 'text', 'background', 'white', 'tint', 'border', 'other' ];
	const colors = useMemo( () => ( palette || [] ).slice().sort( ( a, b ) => order.indexOf( a.role ) - order.indexOf( b.role ) ), [ palette ] );

	// Shades follow their main color (unless the visitor chose one by hand).
	const withShades = ( next, keep = manual ) => {
		const out = { ...next };
		colors
			.filter( ( c ) => 'tint' === c.role && c.linked && ! keep[ c.color ] )
			.forEach( ( c ) => {
				if ( out[ c.linked ] && out[ c.linked ] !== c.linked ) {
					out[ c.color ] = follow( c.color, c.linked, out[ c.linked ] );
				} else {
					delete out[ c.color ];
				}
			} );
		return out;
	};

	const choosePreset = ( name ) => {
		setPreset( name );
		setManual( {} );
		if ( 'original' === name ) {
			setTargets( {} );
			return;
		}
		const next = {};
		const roles = 'site' === name ? null : PALETTES.find( ( p ) => p.name === name )?.colors;
		colors.forEach( ( c ) => {
			if ( 'site' === name ) {
				if ( c.slot && site[ c.slot ] ) {
					next[ c.color ] = site[ c.slot ];
				}
			} else if ( roles?.[ c.role ] ) {
				next[ c.color ] = roles[ c.role ];
			}
		} );
		setTargets( withShades( next, {} ) );
	};

	const setColor = ( color, value ) => {
		setPreset( 'custom' );
		const keep = { ...manual, [ color ]: true };
		setManual( keep );
		setTargets( withShades( { ...targets, [ color ]: value }, keep ) );
	};

	// The payload for the import requests.
	useEffect( () => {
		if ( ! palette ) {
			return;
		}
		const map = {};
		Object.keys( targets ).forEach( ( old ) => {
			if ( targets[ old ] && targets[ old ].toLowerCase() !== old ) {
				map[ old ] = targets[ old ].toLowerCase();
			}
		} );
		const links = {};
		colors.forEach( ( c ) => {
			if ( c.slot ) {
				links[ c.color ] = c.slot;
			}
		} );
		const useGlobals = globals && Object.keys( links ).length > 0;
		onChange( Object.keys( map ).length || useGlobals ? { map, links, globals: useGlobals } : null );
	}, [ targets, globals, palette ] );

	const previewUrl = useMemo( () => {
		const pairs = Object.keys( targets )
			.filter( ( old ) => targets[ old ] && targets[ old ].toLowerCase() !== old )
			.map( ( old ) => old.slice( 1 ) + '-' + targets[ old ].slice( 1 ).toLowerCase() );
		if ( ! preview ) {
			return '';
		}
		return pairs.length ? preview + ( preview.includes( '?' ) ? '&' : '?' ) + 'tbfe_colors=' + pairs.join( '.' ) : preview;
	}, [ targets, preview ] );

	if ( error ) {
		return <p className="tbfe-muted">{ error }</p>;
	}
	if ( ! palette ) {
		return (
			<div className="tbfe-colors tbfe-colors--loading">
				<Spinner />
				{ __( 'Reading the colors of the demo…', 'theme-builder-for-elementor' ) }
			</div>
		);
	}
	if ( ! colors.length ) {
		return null;
	}

	const swatches = ( list ) => (
		<span className="tbfe-colors__strip" aria-hidden="true">
			{ list.map( ( c, i ) => (
				<i key={ i } style={ { background: c } } />
			) ) }
		</span>
	);
	const main = colors.filter( ( c ) => [ 'primary', 'secondary', 'accent', 'heading', 'text', 'background' ].includes( c.role ) );
	const hasSite = colors.some( ( c ) => c.slot && site[ c.slot ] );

	return (
		<div className="tbfe-colors">
			<div className="tbfe-colors__head">
				<span className="tbfe-field__label">{ __( 'Colors', 'theme-builder-for-elementor' ) }</span>
				{ previewUrl && (
					<a href={ previewUrl } target="_blank" rel="noreferrer" className="tbfe-colors__preview">
						<Icon name="eye" size={ 14 } />
						{ __( 'Live preview in these colors', 'theme-builder-for-elementor' ) }
					</a>
				) }
			</div>
			<div className="tbfe-colors__presets" role="radiogroup" aria-label={ __( 'Color presets', 'theme-builder-for-elementor' ) }>
				<button type="button" role="radio" aria-checked={ 'original' === preset } className={ 'tbfe-colors__preset' + ( 'original' === preset ? ' is-selected' : '' ) } onClick={ () => choosePreset( 'original' ) }>
					{ swatches( main.map( ( c ) => c.color ) ) }
					{ __( 'Original', 'theme-builder-for-elementor' ) }
				</button>
				{ hasSite && (
					<button type="button" role="radio" aria-checked={ 'site' === preset } className={ 'tbfe-colors__preset' + ( 'site' === preset ? ' is-selected' : '' ) } onClick={ () => choosePreset( 'site' ) }>
						{ swatches( [ 'primary', 'secondary', 'text', 'accent' ].map( ( s ) => site[ s ] ).filter( Boolean ) ) }
						{ __( 'My site colors', 'theme-builder-for-elementor' ) }
					</button>
				) }
				{ PALETTES.map( ( p ) => (
					<button key={ p.name } type="button" role="radio" aria-checked={ p.name === preset } className={ 'tbfe-colors__preset' + ( p.name === preset ? ' is-selected' : '' ) } onClick={ () => choosePreset( p.name ) }>
						{ swatches( Object.values( p.colors ) ) }
						{ p.name }
					</button>
				) ) }
			</div>
			<div className="tbfe-colors__grid">
				{ colors.map( ( c ) => {
					const value = targets[ c.color ] || c.color;
					return (
						<label key={ c.color } className={ 'tbfe-colors__item' + ( value !== c.color ? ' is-changed' : '' ) }>
							<span className="tbfe-colors__old" style={ { background: c.color } } title={ c.color } />
							<span className="tbfe-colors__arrow" aria-hidden="true">
								→
							</span>
							<input type="color" value={ value } onChange={ ( e ) => setColor( c.color, e.target.value ) } aria-label={ ( ROLES[ c.role ] || c.role ) + ' ' + c.color } />
							<span className="tbfe-colors__role">
								{ ROLES[ c.role ] || c.role }
								<small>{ value }</small>
							</span>
						</label>
					);
				} ) }
			</div>
			<ToggleControl
				__nextHasNoMarginBottom
				label={ __( 'Link the main colors to the global colors of Elementor', 'theme-builder-for-elementor' ) }
				help={ __( 'The global colors (Elementor › Site Settings › Global Colors) get these colors, so you can recolor the whole site later in one place. They change the existing pages that use them too.', 'theme-builder-for-elementor' ) }
				checked={ globals }
				onChange={ setGlobals }
			/>
		</div>
	);
}
