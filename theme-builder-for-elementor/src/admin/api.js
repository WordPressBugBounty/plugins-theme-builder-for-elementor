import apiFetch from '@wordpress/api-fetch';

const NS = '/tbfe/v1';

const request = ( path, options = {} ) =>
	apiFetch( { path: NS + path, ...options } ).catch( ( error ) => {
		const failure = new Error( error?.message || 'Request failed.' );
		failure.code = error?.code || '';
		failure.data = error?.data || {};
		throw failure;
	} );

export const api = {
	bootstrap: () => request( '/bootstrap' ),
	templates: () => request( '/templates' ),
	createTemplate: ( data ) => request( '/templates', { method: 'POST', data } ),
	updateTemplate: ( id, data ) => request( `/templates/${ id }`, { method: 'POST', data } ),
	deleteTemplate: ( id ) => request( `/templates/${ id }`, { method: 'DELETE' } ),
	duplicateTemplate: ( id ) => request( `/templates/${ id }/duplicate`, { method: 'POST' } ),
	objects: ( object, { search = '', include = [] } = {} ) =>
		request(
			`/objects?object=${ encodeURIComponent( object ) }&search=${ encodeURIComponent( search ) }&include=${ include.join( ',' ) }`
		),
	saveSettings: ( data ) => request( '/settings', { method: 'POST', data } ),
	saveSection: ( id, data ) => request( `/settings/section/${ id }`, { method: 'POST', data } ),
	widgets: () => request( '/widgets' ),
	saveWidgets: ( disabled ) => request( '/widgets', { method: 'POST', data: { disabled } } ),
	library: ( refresh = false ) => request( '/library' + ( refresh ? '?refresh=1' : '' ) ),
	keepPreviews: ( keys ) => request( '/library/keep', { method: 'POST', data: { keys } } ),
	importItem: ( id, activate = false, colors = null, replace = false ) => request( '/library/import', { method: 'POST', data: { id, activate, colors, replace } } ),
	importPage: ( page, status, into = 0, layout = 'full-width', colors = null ) => request( '/library/import-page', { method: 'POST', data: { page, status, into, layout, colors } } ),
	palette: ( entries ) => request( '/library/palette', { method: 'POST', data: { entries } } ),
	importFinish: ( data ) => request( '/library/import-finish', { method: 'POST', data } ),
	installPlugin: ( slug ) => request( '/install-plugin', { method: 'POST', data: { slug } } ),
	tool: ( name ) => request( `/tools/${ name }`, { method: 'POST' } ),
};
