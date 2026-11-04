import { __, sprintf } from '@wordpress/i18n';
import { Spinner, ToggleControl } from '@wordpress/components';
import { useEffect, useMemo, useState } from '@wordpress/element';
import { api } from '../api';
import { useStore } from '../store';
import Icon from '../components/Icon';
import { ProBadge, ProBanner } from '../components/Pro';

/**
 * Settings: a menu on the left and one page per part – General, Widgets, the features of PRO, Tools and License.
 */
export default function SettingsPage( { section = '' } ) {
	const { data } = useStore();
	const sections = data.sections || [];
	const current = section || 'general';
	const pro = data.pro || {};

	const items = [
		{ id: 'general', label: __( 'General', 'theme-builder-for-elementor' ), icon: 'settings' },
		{ id: 'widgets', label: __( 'Widgets', 'theme-builder-for-elementor' ), icon: 'widgets' },
		...sections.map( ( s ) => ( { id: s.id, label: s.label, icon: s.icon, pro: s.pro, locked: s.locked } ) ),
		{ id: 'tools', label: __( 'Tools', 'theme-builder-for-elementor' ), icon: 'refresh' },
	];
	const active = sections.find( ( s ) => s.id === current );

	let page;
	if ( 'widgets' === current ) {
		page = <WidgetsPage />;
	} else if ( 'tools' === current ) {
		page = <ToolsPage />;
	} else if ( active ) {
		page = <SectionPage key={ active.id } section={ active } />;
	} else {
		page = <GeneralPage />;
	}

	return (
		<div className="tbfe-settings-layout">
			<aside className="tbfe-sidenav tbfe-settings-nav" aria-label={ __( 'Settings', 'theme-builder-for-elementor' ) }>
				{ items.map( ( item ) => (
					<a
						key={ item.id }
						href={ '#/settings' + ( 'general' === item.id ? '' : '/' + item.id ) }
						className={ 'tbfe-sidenav__item' + ( current === item.id ? ' is-active' : '' ) }
						aria-current={ current === item.id ? 'page' : undefined }
					>
						<span className="tbfe-settings-nav__label">
							<Icon name={ item.icon } size={ 16 } />
							{ item.label }
						</span>
						{ item.pro && <ProBadge locked={ item.locked } /> }
					</a>
				) ) }
				{ pro.installed && pro.license && (
					<a href={ pro.license } className="tbfe-sidenav__item">
						<span className="tbfe-settings-nav__label">
							<Icon name="key" size={ 16 } />
							{ __( 'License', 'theme-builder-for-elementor' ) }
						</span>
						<Icon name="external" size={ 14 } />
					</a>
				) }
			</aside>
			<div className="tbfe-settings-main">{ page }</div>
		</div>
	);
}

function GeneralPage() {
	const { data, setSettings, toast } = useStore();
	const [ values, setValues ] = useState( data.settings );
	const [ busy, setBusy ] = useState( false );
	const changed = JSON.stringify( values ) !== JSON.stringify( data.settings );

	const save = () => {
		setBusy( true );
		api.saveSettings( values )
			.then( ( saved ) => {
				setSettings( saved );
				setValues( saved );
				toast( __( 'Settings saved.', 'theme-builder-for-elementor' ) );
			} )
			.catch( ( e ) => toast( e.message, 'error' ) )
			.finally( () => setBusy( false ) );
	};

	return (
		<section className="tbfe-card tbfe-settings__group">
			<h1 className="tbfe-h1">{ __( 'General', 'theme-builder-for-elementor' ) }</h1>
			<ToggleControl
				__nextHasNoMarginBottom
				label={ __( 'Keep pages built with Elementor as they are', 'theme-builder-for-elementor' ) }
				help={ __( 'Single templates are not used for posts and pages whose content you designed with Elementor. Headers and footers still apply.', 'theme-builder-for-elementor' ) }
				checked={ !! values.skip_elementor_built }
				onChange={ ( v ) => setValues( { ...values, skip_elementor_built: v } ) }
			/>
			<ToggleControl
				__nextHasNoMarginBottom
				label={ __( 'Template library and Websites Import', 'theme-builder-for-elementor' ) }
				help={ __( 'Loads the list of templates and websites from demos.blocks-wp.com. No data about your site is sent.', 'theme-builder-for-elementor' ) }
				checked={ !! values.library }
				onChange={ ( v ) => setValues( { ...values, library: v } ) }
			/>
			<label className="tbfe-field tbfe-field--inline">
				<span className="tbfe-field__label">{ __( 'Content width of classic archives (px)', 'theme-builder-for-elementor' ) }</span>
				<input
					type="number"
					className="tbfe-input"
					min={ 320 }
					max={ 3000 }
					value={ values.container_width }
					onChange={ ( e ) => setValues( { ...values, container_width: parseInt( e.target.value, 10 ) || 0 } ) }
				/>
			</label>
			<div className="tbfe-settings__save">
				<button type="button" className="tbfe-btn tbfe-btn--primary" disabled={ ! changed || busy } onClick={ save }>
					{ busy ? __( 'Saving…', 'theme-builder-for-elementor' ) : __( 'Save settings', 'theme-builder-for-elementor' ) }
				</button>
			</div>
		</section>
	);
}

/**
 * One field of a feature page.
 */
function Field( { field, value, disabled, onChange } ) {
	if ( 'toggle' === field.type ) {
		return <ToggleControl __nextHasNoMarginBottom label={ field.label } help={ field.help } checked={ !! value } disabled={ disabled } onChange={ onChange } />;
	}
	let input;
	if ( 'select' === field.type || 'page' === field.type ) {
		input = (
			<select className="tbfe-input" value={ value ?? '' } disabled={ disabled } onChange={ ( e ) => onChange( e.target.value ) }>
				{ Object.entries( field.options || {} ).map( ( [ key, label ] ) => (
					<option key={ key } value={ key }>
						{ label }
					</option>
				) ) }
			</select>
		);
	} else if ( 'number' === field.type ) {
		input = (
			<input type="number" className="tbfe-input" min={ field.min } max={ field.max } value={ value ?? '' } disabled={ disabled } onChange={ ( e ) => onChange( '' === e.target.value ? '' : Number( e.target.value ) ) } />
		);
	} else if ( 'textarea' === field.type ) {
		input = <textarea className="tbfe-input" rows={ 3 } value={ value ?? '' } disabled={ disabled } placeholder={ field.placeholder } onChange={ ( e ) => onChange( e.target.value ) } />;
	} else {
		input = <input type="text" className="tbfe-input" value={ value ?? '' } disabled={ disabled } placeholder={ field.placeholder } onChange={ ( e ) => onChange( e.target.value ) } />;
	}
	return (
		<label className="tbfe-field tbfe-setting-field">
			<span className="tbfe-field__label">{ field.label }</span>
			{ input }
			{ field.help && <span className="tbfe-muted tbfe-setting-field__help">{ field.help }</span> }
		</label>
	);
}

/**
 * A page of a feature (fields from PHP), or what a locked PRO feature offers.
 */
function SectionPage( { section } ) {
	const { reload, toast } = useStore();
	const [ values, setValues ] = useState( () => ( { ...section.values } ) );
	const [ busy, setBusy ] = useState( false );
	const changed = JSON.stringify( values ) !== JSON.stringify( { ...section.values } );

	const save = () => {
		setBusy( true );
		api.saveSection( section.id, values )
			.then( ( saved ) => {
				setValues( { ...saved } );
				toast( __( 'Settings saved.', 'theme-builder-for-elementor' ) );
				reload();
			} )
			.catch( ( e ) => toast( e.message, 'error' ) )
			.finally( () => setBusy( false ) );
	};

	// A field can depend on another one (show: { field: value }).
	const visible = ( field ) => ! field.show || Object.entries( field.show ).every( ( [ key, expected ] ) => ( Array.isArray( expected ) ? expected.includes( values[ key ] ) : values[ key ] === expected ) );

	return (
		<section className="tbfe-card tbfe-settings__group">
			<h1 className="tbfe-h1 tbfe-h1--pro">
				{ section.label }
				{ section.pro && <ProBadge locked={ section.locked } /> }
			</h1>
			{ section.description && <p className="tbfe-muted tbfe-type-lead">{ section.description }</p> }
			{ section.locked && <ProBanner title={ sprintf( /* translators: %s: feature */ __( '%s is a PRO feature', 'theme-builder-for-elementor' ), section.label ) } /> }
			{ section.features?.length > 0 && (
				<ul className="tbfe-feature-list">
					{ section.features.map( ( feature ) => (
						<li key={ feature }>
							<Icon name="check" size={ 16 } />
							{ feature }
						</li>
					) ) }
				</ul>
			) }
			{ section.fields.length > 0 && (
				<div className={ 'tbfe-setting-fields' + ( section.locked ? ' is-locked' : '' ) }>
					{ section.fields.filter( visible ).map( ( field ) =>
						'heading' === field.type ? (
							<h2 key={ field.id } className="tbfe-h2 tbfe-setting-heading">
								{ field.label }
							</h2>
						) : (
							<Field key={ field.id } field={ field } value={ values[ field.id ] } disabled={ section.locked } onChange={ ( v ) => setValues( { ...values, [ field.id ]: v } ) } />
						)
					) }
				</div>
			) }
			{ section.fields.length > 0 && ! section.locked && (
				<div className="tbfe-settings__save">
					<button type="button" className="tbfe-btn tbfe-btn--primary" disabled={ ! changed || busy } onClick={ save }>
						{ busy ? __( 'Saving…', 'theme-builder-for-elementor' ) : __( 'Save settings', 'theme-builder-for-elementor' ) }
					</button>
				</div>
			) }
		</section>
	);
}

/**
 * Widgets: switch widgets off to keep the Elementor panel short (a switched-off widget is not loaded at all).
 */
function WidgetsPage() {
	const { toast } = useStore();
	const [ list, setList ] = useState( null );
	const [ disabled, setDisabled ] = useState( [] );
	const [ saved, setSaved ] = useState( [] );
	const [ search, setSearch ] = useState( '' );
	const [ busy, setBusy ] = useState( false );

	useEffect( () => {
		api.widgets()
			.then( ( widgets ) => {
				const off = widgets.filter( ( w ) => ! w.enabled ).map( ( w ) => w.name );
				setList( widgets );
				setDisabled( off );
				setSaved( off );
			} )
			.catch( ( e ) => toast( e.message, 'error' ) );
	}, [] ); // eslint-disable-line react-hooks/exhaustive-deps

	const groups = useMemo(
		() => [
			{ id: 'theme', label: __( 'Theme Builder', 'theme-builder-for-elementor' ) },
			{ id: 'pro', label: __( 'WooCommerce (PRO)', 'theme-builder-for-elementor' ) },
		],
		[]
	);

	if ( ! list ) {
		return (
			<div className="tbfe-loading">
				<Spinner />
			</div>
		);
	}

	const changed = JSON.stringify( [ ...disabled ].sort() ) !== JSON.stringify( [ ...saved ].sort() );
	const toggle = ( name, on ) => setDisabled( on ? disabled.filter( ( n ) => n !== name ) : [ ...disabled, name ] );
	const setGroup = ( group, on ) => {
		const names = list.filter( ( w ) => w.group === group ).map( ( w ) => w.name );
		setDisabled( on ? disabled.filter( ( n ) => ! names.includes( n ) ) : [ ...new Set( [ ...disabled, ...names ] ) ] );
	};
	const save = () => {
		setBusy( true );
		api.saveWidgets( disabled )
			.then( () => {
				setSaved( disabled );
				toast( __( 'Widgets saved. Reload the Elementor editor to see the change.', 'theme-builder-for-elementor' ) );
			} )
			.catch( ( e ) => toast( e.message, 'error' ) )
			.finally( () => setBusy( false ) );
	};
	const matches = ( w ) => ! search || w.title.toLowerCase().includes( search.toLowerCase() );

	return (
		<section className="tbfe-card tbfe-settings__group">
			<div className="tbfe-list-head">
				<div>
					<h1 className="tbfe-h1">{ __( 'Widgets', 'theme-builder-for-elementor' ) }</h1>
					<p className="tbfe-muted">{ __( 'Switch off the widgets you do not use: they leave the Elementor panel and are not loaded. Widgets already on your pages are not shown while switched off.', 'theme-builder-for-elementor' ) }</p>
				</div>
				<div className="tbfe-list-head__tools">
					<label className="tbfe-search-field">
						<Icon name="search" size={ 16 } />
						<input type="search" value={ search } onChange={ ( e ) => setSearch( e.target.value ) } placeholder={ __( 'Search widgets', 'theme-builder-for-elementor' ) } aria-label={ __( 'Search widgets', 'theme-builder-for-elementor' ) } />
					</label>
				</div>
			</div>
			{ groups.map( ( group ) => {
				const widgets = list.filter( ( w ) => w.group === group.id && matches( w ) );
				if ( ! widgets.length ) {
					return null;
				}
				const on = widgets.filter( ( w ) => ! disabled.includes( w.name ) ).length;
				return (
					<div key={ group.id } className="tbfe-widget-group">
						<div className="tbfe-widget-group__head">
							<h2 className="tbfe-h2">
								{ group.label }
								<span className="tbfe-count">{ sprintf( /* translators: 1: enabled widgets, 2: all widgets */ __( '%1$d of %2$d', 'theme-builder-for-elementor' ), on, widgets.length ) }</span>
							</h2>
							<div className="tbfe-widget-group__all">
								<button type="button" className="tbfe-btn tbfe-btn--ghost tbfe-btn--sm" onClick={ () => setGroup( group.id, true ) }>
									{ __( 'All on', 'theme-builder-for-elementor' ) }
								</button>
								<button type="button" className="tbfe-btn tbfe-btn--ghost tbfe-btn--sm" onClick={ () => setGroup( group.id, false ) }>
									{ __( 'All off', 'theme-builder-for-elementor' ) }
								</button>
							</div>
						</div>
						<div className="tbfe-widget-grid">
							{ widgets.map( ( w ) => {
								const enabled = ! disabled.includes( w.name );
								return (
									<label key={ w.name } className={ 'tbfe-widget-card' + ( enabled ? ' is-on' : '' ) }>
										<i className={ w.icon } aria-hidden="true" />
										<span className="tbfe-widget-card__title">
											{ w.title }
											{ w.pro && <ProBadge locked={ w.locked } /> }
										</span>
										<input type="checkbox" className="tbfe-switch" checked={ enabled } onChange={ ( e ) => toggle( w.name, e.target.checked ) } aria-label={ w.title } />
									</label>
								);
							} ) }
						</div>
					</div>
				);
			} ) }
			<div className="tbfe-settings__save tbfe-settings__save--sticky">
				<button type="button" className="tbfe-btn tbfe-btn--primary" disabled={ ! changed || busy } onClick={ save }>
					{ busy ? __( 'Saving…', 'theme-builder-for-elementor' ) : __( 'Save widgets', 'theme-builder-for-elementor' ) }
				</button>
			</div>
		</section>
	);
}

function ToolsPage() {
	const { data, toast, reload } = useStore();
	const [ busy, setBusy ] = useState( '' );

	const tool = ( name, confirmText ) => {
		// eslint-disable-next-line no-alert
		if ( confirmText && ! window.confirm( confirmText ) ) {
			return;
		}
		setBusy( name );
		api.tool( name )
			.then( ( result ) => {
				toast( result.message );
				if ( 'regenerate-css' !== name ) {
					reload();
				}
			} )
			.catch( ( e ) => toast( e.message, 'error' ) )
			.finally( () => setBusy( '' ) );
	};

	return (
		<section className="tbfe-card tbfe-settings__group">
			<h1 className="tbfe-h1">{ __( 'Tools', 'theme-builder-for-elementor' ) }</h1>
			<div className="tbfe-tool">
				<div>
					<strong>{ __( 'Regenerate CSS', 'theme-builder-for-elementor' ) }</strong>
					<p className="tbfe-muted">{ __( 'When a template looks different on the site than in the editor.', 'theme-builder-for-elementor' ) }</p>
				</div>
				<button type="button" className="tbfe-btn tbfe-btn--soft" disabled={ !! busy } onClick={ () => tool( 'regenerate-css' ) }>
					<Icon name="refresh" size={ 16 } />
					{ __( 'Regenerate', 'theme-builder-for-elementor' ) }
				</button>
			</div>
			<div className="tbfe-tool">
				<div>
					<strong>{ __( 'Migrate data of version 1 again', 'theme-builder-for-elementor' ) }</strong>
					<p className="tbfe-muted">{ __( 'Moves templates and settings of version 1 that were not migrated yet. Existing templates stay.', 'theme-builder-for-elementor' ) }</p>
				</div>
				<button type="button" className="tbfe-btn tbfe-btn--soft" disabled={ !! busy } onClick={ () => tool( 'migrate' ) }>
					{ __( 'Migrate', 'theme-builder-for-elementor' ) }
				</button>
			</div>
			<div className="tbfe-tool tbfe-tool--danger">
				<div>
					<strong>{ __( 'Return to version 1', 'theme-builder-for-elementor' ) }</strong>
					<p className="tbfe-muted">{ __( 'Gives the migrated templates back to version 1, so you can install 1.2.5 again. Templates created in 2.0 stay in the database.', 'theme-builder-for-elementor' ) }</p>
				</div>
				<button
					type="button"
					className="tbfe-btn tbfe-btn--danger"
					disabled={ !! busy || data.reverted }
					onClick={ () => tool( 'revert', __( 'Return the templates to version 1? Version 2 will not show them until you migrate again.', 'theme-builder-for-elementor' ) ) }
				>
					{ data.reverted ? __( 'Returned', 'theme-builder-for-elementor' ) : __( 'Return', 'theme-builder-for-elementor' ) }
				</button>
			</div>
		</section>
	);
}
