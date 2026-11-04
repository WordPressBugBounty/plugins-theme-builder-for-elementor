import { createContext, useCallback, useContext, useEffect, useState } from '@wordpress/element';
import { api } from './api';

const StoreContext = createContext( null );

let toastId = 0;

export function StoreProvider( { children } ) {
	const [ data, setData ] = useState( null );
	const [ error, setError ] = useState( '' );
	const [ toasts, setToasts ] = useState( [] );

	const load = useCallback( () => {
		return api
			.bootstrap()
			.then( ( result ) => {
				setData( result );
				setError( '' );
			} )
			.catch( ( e ) => setError( e.message ) );
	}, [] );

	useEffect( () => {
		load();
	}, [ load ] );

	const toast = useCallback( ( message, type = 'success', action = null ) => {
		const id = ++toastId;
		setToasts( ( list ) => [ ...list, { id, message, type, action } ] );
		setTimeout( () => setToasts( ( list ) => list.filter( ( t ) => t.id !== id ) ), 6000 );
	}, [] );

	const dismissToast = ( id ) => setToasts( ( list ) => list.filter( ( t ) => t.id !== id ) );

	/** Replaces or adds one template in the local list. */
	const putTemplate = useCallback( ( template ) => {
		setData( ( current ) => {
			const others = current.templates.filter( ( t ) => t.id !== template.id );
			return { ...current, templates: [ template, ...others ] };
		} );
	}, [] );

	const removeTemplate = useCallback( ( id ) => {
		setData( ( current ) => ( { ...current, templates: current.templates.filter( ( t ) => t.id !== id ) } ) );
	}, [] );

	const setSettings = useCallback( ( settings ) => setData( ( current ) => ( { ...current, settings } ) ), [] );

	const value = {
		data,
		error,
		reload: load,
		toast,
		toasts,
		dismissToast,
		putTemplate,
		removeTemplate,
		setSettings,
		type: ( slug ) => data?.types.find( ( t ) => t.slug === slug ),
	};

	return <StoreContext.Provider value={ value }>{ children }</StoreContext.Provider>;
}

export const useStore = () => useContext( StoreContext );
