import { __ } from '@wordpress/i18n';
import { FormTokenField } from '@wordpress/components';
import { useEffect, useRef, useState } from '@wordpress/element';
import { api } from '../api';

/**
 * Choice of posts, terms or users by name (stored as IDs).
 */
export default function ObjectPicker( { object, ids, onChange } ) {
	const [ known, setKnown ] = useState( {} ); // id => label
	const [ suggestions, setSuggestions ] = useState( [] );
	const timer = useRef();

	// Labels of the chosen IDs.
	useEffect( () => {
		const missing = ids.filter( ( id ) => ! known[ id ] );
		if ( ! missing.length ) {
			return;
		}
		api.objects( object, { include: missing } ).then( ( list ) => {
			setKnown( ( current ) => ( { ...current, ...Object.fromEntries( list.map( ( o ) => [ o.id, o.label ] ) ) } ) );
		} );
	}, [ object, ids.join( ',' ) ] ); // eslint-disable-line react-hooks/exhaustive-deps

	const search = ( text ) => {
		clearTimeout( timer.current );
		timer.current = setTimeout( () => {
			api.objects( object, { search: text } ).then( ( list ) => {
				setKnown( ( current ) => ( { ...current, ...Object.fromEntries( list.map( ( o ) => [ o.id, o.label ] ) ) } ) );
				setSuggestions( list.map( ( o ) => `${ o.label } (#${ o.id })` ) );
			} );
		}, 250 );
	};

	const tokens = ids.map( ( id ) => `${ known[ id ] || '…' } (#${ id })` );

	return (
		<FormTokenField
			__next40pxDefaultSize
			__nextHasNoMarginBottom
			label={ __( 'Specific items (optional)', 'theme-builder-for-elementor' ) }
			placeholder={ __( 'All — or type to choose…', 'theme-builder-for-elementor' ) }
			value={ tokens }
			suggestions={ suggestions }
			onInputChange={ search }
			onFocus={ () => search( '' ) }
			maxSuggestions={ 20 }
			onChange={ ( values ) => {
				const next = values
					.map( ( v ) => {
						const match = String( typeof v === 'object' ? v.value : v ).match( /\(#(\d+)\)$/ );
						return match ? parseInt( match[ 1 ], 10 ) : 0;
					} )
					.filter( Boolean );
				onChange( [ ...new Set( next ) ] );
			} }
			__experimentalExpandOnFocus
			__experimentalShowHowTo={ false }
		/>
	);
}
