import { __, _n, sprintf } from '@wordpress/i18n';
import { Modal, Spinner, ToggleControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { api } from '../api';
import { useStore } from '../store';
import { useLibrary, PreviewImage } from '../library';
import Icon from './Icon';
import TypeArt from './TypeArt';
import LayoutChoice from './LayoutChoice';
import ColorStep from './ColorStep';
import LayoutRow from './LayoutRow';

/** Template types offered in the layouts step (only the ones the site has and may use). */
const LAYOUT_TYPES = [ 'header', 'footer', 'single', 'archive', 'search', 'error-404', 'product', 'product-archive' ];

/**
 * Import of the chosen pages of a website demo: options, optionally the layouts (header, footer, blog posts,
 * archives, search, 404 – from this website or the Demo Library), the colors, then one request per item.
 */
export default function SiteImportModal( { site, pageIds, onClose, onDone } ) {
	const { data, putTemplate, reload } = useStore();
	const { index } = useLibrary();
	const config = window.tbfeAdmin || {};
	const chosenPages = site.pages.filter( ( p ) => pageIds.includes( p.id ) );
	const homeChosen = chosenPages.some( ( p ) => p.home );

	const [ step, setStep ] = useState( 'options' );
	const [ withLayouts, setWithLayouts ] = useState( false );
	const [ picks, setPicks ] = useState( null );
	const [ front, setFront ] = useState( false );
	const [ menu, setMenu ] = useState( false );
	const [ publish, setPublish ] = useState( true );
	const [ layout, setLayout ] = useState( 'full-width' );
	const [ colors, setColors ] = useState( null );
	const [ tasks, setTasks ] = useState( [] );
	const [ result, setResult ] = useState( null );

	const typeOf = ( slug ) => data.types.find( ( t ) => t.slug === slug );
	const typeLabel = ( slug ) => typeOf( slug )?.label || slug;

	// The choices of each type: the templates of this website first, then the other demos of the library.
	const items = index?.items || [];
	const rows = LAYOUT_TYPES.filter( ( slug ) => typeOf( slug ) && ! typeOf( slug ).locked )
		.map( ( slug ) => {
			const own = site.templates.filter( ( t ) => t.type === slug ).map( ( t ) => ( { ...( items.find( ( i ) => i.id === t.id ) || {} ), id: t.id, title: t.title, type: slug, own: true } ) );
			const others = items.filter( ( i ) => i.type === slug && ! own.some( ( o ) => o.id === i.id ) && ! ( i.pro && ! data.pro.active ) );
			return { slug, choices: [ ...own, ...others ] };
		} )
		.filter( ( row ) => row.choices.length > 0 );

	// Preselected: the template of this website for its type, else "keep the current one".
	const openLayouts = () => {
		if ( ! picks ) {
			const first = {};
			rows.forEach( ( row ) => {
				first[ row.slug ] = row.choices.find( ( c ) => c.own )?.id || 0;
			} );
			setPicks( first );
		}
		setStep( 'layouts' );
	};
	const chosenTemplates = withLayouts && picks
		? rows.map( ( row ) => row.choices.find( ( c ) => c.id === picks[ row.slug ] ) ).filter( Boolean )
		: [];

	const run = async () => {
		setTasks( [
			...chosenTemplates.map( ( t ) => ( { key: 't' + t.id, label: sprintf( '%s: %s', typeLabel( t.type ), t.title ), state: 'wait' } ) ),
			...chosenPages.map( ( p ) => ( { key: 'p' + p.id, label: sprintf( /* translators: %s: page title */ __( 'Page: %s', 'theme-builder-for-elementor' ), p.title ), state: 'wait' } ) ),
			{ key: 'finish', label: __( 'Links, menu and front page', 'theme-builder-for-elementor' ), state: 'wait' },
		] );
		setStep( 'run' );

		const mark = ( key, state, message = '' ) => setTasks( ( current ) => current.map( ( t ) => ( t.key === key ? { ...t, state, message } : t ) ) );
		const newTemplates = [];
		const newPages = [];
		let errors = 0;

		// The chosen layouts are used at once, instead of the current templates of their type.
		for ( const t of chosenTemplates ) {
			mark( 't' + t.id, 'run' );
			try {
				const template = await api.importItem( t.id, true, colors, true );
				putTemplate( template );
				newTemplates.push( template.id );
				mark( 't' + t.id, 'done' );
			} catch ( e ) {
				errors++;
				mark( 't' + t.id, 'error', e.message );
			}
		}
		for ( const p of chosenPages ) {
			mark( 'p' + p.id, 'run' );
			try {
				const page = await api.importPage( p.id, publish || ( p.home && front ) ? 'publish' : 'draft', 0, layout, colors );
				newPages.push( page );
				mark( 'p' + p.id, 'done' );
			} catch ( e ) {
				errors++;
				mark( 'p' + p.id, 'error', e.message );
			}
		}
		mark( 'finish', 'run' );
		try {
			const done = await api.importFinish( {
				pages: newPages.map( ( p ) => ( { id: p.id, demo_url: p.demo_url, home: p.home } ) ),
				templates: newTemplates,
				front: front && homeChosen,
				menu: menu && newPages.length > 0,
				name: site.title,
			} );
			mark( 'finish', 'done' );
			setResult( { ...done, pages: newPages, templates: newTemplates.length, errors } );
		} catch ( e ) {
			mark( 'finish', 'error', e.message );
			setResult( { pages: newPages, templates: newTemplates.length, errors: errors + 1 } );
		}
		reload();
		setStep( 'done' );
		if ( onDone ) {
			onDone();
		}
	};

	const closeAllowed = 'run' !== step;
	const total = chosenPages.length + chosenTemplates.length;

	// The colors come first: they apply to the pages and to the layouts chosen later. The palette is read from the
	// pages and all templates of the website (the other demos of the library use the same colors).
	const colorStep = (
		<ColorStep
			entries={ [ ...chosenPages.map( ( p ) => ( { kind: 'page', id: p.id } ) ), ...site.templates.map( ( t ) => ( { kind: 'item', id: t.id } ) ) ] }
			preview={ ( chosenPages.find( ( p ) => p.home ) || chosenPages[ 0 ] )?.preview || site.preview }
			defaultGlobals
			onChange={ setColors }
		/>
	);
	const importButton = (
		<button type="button" className="tbfe-btn tbfe-btn--primary" onClick={ run }>
			<Icon name="download" size={ 16 } />
			{ sprintf(
				/* translators: %d: number of items */
				_n( 'Import %d item', 'Import %d items', total, 'theme-builder-for-elementor' ),
				total
			) }
		</button>
	);

	return (
		<Modal
			title={ sprintf( /* translators: %s: website demo title */ __( 'Import from “%s”', 'theme-builder-for-elementor' ), site.title ) }
			onRequestClose={ () => closeAllowed && onClose() }
			isDismissible={ closeAllowed }
			shouldCloseOnClickOutside={ closeAllowed }
			className={ 'tbfe-modal tbfe-modal--site' + ( 'layouts' === step ? ' tbfe-modal--wide' : '' ) }
		>
			{ 'options' === step && (
				<>
					<p className="tbfe-modal__lead">{ __( 'These items are added as new pages and templates – nothing on your site is overwritten.', 'theme-builder-for-elementor' ) }</p>
					<ul className="tbfe-import-summary">
						{ chosenPages.map( ( p ) => (
							<li key={ p.id }>
								<Icon name="layers" size={ 15 } />
								{ p.title }
								{ p.home && <span className="tbfe-chip tbfe-chip--home">{ __( 'Home', 'theme-builder-for-elementor' ) }</span> }
							</li>
						) ) }
					</ul>

					{ colorStep }

					{ chosenPages.length > 0 && <LayoutChoice value={ layout } onChange={ setLayout } /> }

					<div className="tbfe-panel tbfe-site-options">
						{ rows.length > 0 && (
							<ToggleControl
								__nextHasNoMarginBottom
								label={ __( 'Choose the header, footer and layouts too', 'theme-builder-for-elementor' ) }
								help={ __( 'Blog posts, archives, search results and the 404 page – from this website or the Template Library. You choose them in the next step.', 'theme-builder-for-elementor' ) }
								checked={ withLayouts }
								onChange={ setWithLayouts }
							/>
						) }
						{ homeChosen && (
							<ToggleControl
								__nextHasNoMarginBottom
								label={ __( 'Set the Home page as the front page', 'theme-builder-for-elementor' ) }
								checked={ front }
								onChange={ setFront }
							/>
						) }
						{ chosenPages.length > 1 && (
							<ToggleControl
								__nextHasNoMarginBottom
								label={ __( 'Create a menu with the imported pages', 'theme-builder-for-elementor' ) }
								help={ __( 'The menus in the imported header and footer use it.', 'theme-builder-for-elementor' ) }
								checked={ menu }
								onChange={ setMenu }
							/>
						) }
						{ chosenPages.length > 0 && (
							<ToggleControl
								__nextHasNoMarginBottom
								label={ __( 'Publish the pages', 'theme-builder-for-elementor' ) }
								help={ __( 'Off: the pages are drafts (a front page is always published).', 'theme-builder-for-elementor' ) }
								checked={ publish }
								onChange={ setPublish }
							/>
						) }
					</div>

					<div className="tbfe-modal__footer">
						<button type="button" className="tbfe-btn tbfe-btn--ghost" onClick={ onClose }>
							{ __( 'Back', 'theme-builder-for-elementor' ) }
						</button>
						{ withLayouts ? (
							<button type="button" className="tbfe-btn tbfe-btn--primary" onClick={ openLayouts } disabled={ ! index }>
								{ index ? __( 'Next: layouts', 'theme-builder-for-elementor' ) : __( 'Loading the library…', 'theme-builder-for-elementor' ) }
								<span aria-hidden="true">→</span>
							</button>
						) : (
							importButton
						) }
					</div>
				</>
			) }

			{ 'layouts' === step && picks && (
				<>
					<p className="tbfe-modal__lead">
						{ __( 'Choose the look of each part, or keep the one your site has now. The templates of this website come first.', 'theme-builder-for-elementor' ) }
					</p>
					<div className="tbfe-layout-rows">
						{ rows.map( ( row ) => (
							<LayoutRow key={ row.slug } title={ typeOf( row.slug ).plural || typeLabel( row.slug ) }>
									<button
										type="button"
										role="radio"
										aria-checked={ 0 === picks[ row.slug ] }
										className={ 'tbfe-layout-card tbfe-layout-card--keep' + ( 0 === picks[ row.slug ] ? ' is-selected' : '' ) }
										onClick={ () => setPicks( { ...picks, [ row.slug ]: 0 } ) }
									>
										<span className="tbfe-layout-card__thumb">
											<Icon name="check" size={ 20 } />
										</span>
										<span className="tbfe-layout-card__name">{ __( 'Keep the current one', 'theme-builder-for-elementor' ) }</span>
									</button>
									{ row.choices.map( ( c ) => (
										<div key={ c.id } className={ 'tbfe-layout-card' + ( c.id === picks[ row.slug ] ? ' is-selected' : '' ) }>
											<button type="button" role="radio" aria-checked={ c.id === picks[ row.slug ] } className="tbfe-layout-card__pick" onClick={ () => setPicks( { ...picks, [ row.slug ]: c.id } ) }>
												<span className="tbfe-layout-card__thumb">{ c.image ? <PreviewImage image={ c.image } sizes="200px" /> : <TypeArt type={ row.slug } /> }</span>
												<span className="tbfe-layout-card__name">
													{ c.title }
													{ c.own && <span className="tbfe-chip tbfe-chip--home">{ __( 'This website', 'theme-builder-for-elementor' ) }</span> }
												</span>
											</button>
											{ c.preview && (
												<a className="tbfe-layout-card__preview" href={ c.preview } target="_blank" rel="noreferrer">
													<Icon name="eye" size={ 13 } />
													{ __( 'Preview', 'theme-builder-for-elementor' ) }
												</a>
											) }
										</div>
									) ) }
							</LayoutRow>
						) ) }
					</div>
					{ chosenTemplates.length > 0 && (
						<p className="tbfe-muted tbfe-layout-note">
							<Icon name="rules" size={ 14 } />
							{ __( 'The chosen templates are used at once, in the colors of the first step. The current templates of these parts with the same display are switched off (they stay as drafts) – nothing is deleted.', 'theme-builder-for-elementor' ) }
						</p>
					) }
					<div className="tbfe-modal__footer">
						<button type="button" className="tbfe-btn tbfe-btn--ghost" onClick={ () => setStep( 'options' ) }>
							{ __( 'Back', 'theme-builder-for-elementor' ) }
						</button>
						{ importButton }
					</div>
				</>
			) }

			{ ( 'run' === step || 'done' === step ) && (
				<>
					<ul className="tbfe-tasks">
						{ tasks.map( ( t ) => (
							<li key={ t.key } className={ 'tbfe-task tbfe-task--' + t.state }>
								<span className="tbfe-task__icon">
									{ 'run' === t.state && <Spinner /> }
									{ 'done' === t.state && <Icon name="check" size={ 16 } /> }
									{ 'error' === t.state && <Icon name="warning" size={ 16 } /> }
								</span>
								<span className="tbfe-task__label">{ t.label }</span>
								{ t.message && <span className="tbfe-task__message">{ t.message }</span> }
							</li>
						) ) }
					</ul>

					{ 'done' === step && result && (
						<div className={ 'tbfe-banner tbfe-banner--' + ( result.errors ? 'warning' : 'success' ) }>
							<Icon name={ result.errors ? 'warning' : 'check' } size={ 22 } />
							<div>
								<strong>{ result.errors ? __( 'Imported with some problems', 'theme-builder-for-elementor' ) : __( 'Imported', 'theme-builder-for-elementor' ) }</strong>
								<p>
									{ sprintf(
										/* translators: 1: number of pages, 2: number of templates */
										__( '%1$d pages and %2$d templates were added. Images were copied into your media library.', 'theme-builder-for-elementor' ),
										result.pages.length,
										result.templates
									) }
								</p>
							</div>
						</div>
					) }

					<div className="tbfe-modal__footer">
						<span />
						{ 'done' === step ? (
							<div className="tbfe-modal__buttons">
								<button type="button" className="tbfe-btn tbfe-btn--ghost" onClick={ onClose }>
									{ __( 'Import more', 'theme-builder-for-elementor' ) }
								</button>
								{ result?.pages[ 0 ] && (
									<a className="tbfe-btn tbfe-btn--soft" href={ result.pages[ 0 ].edit_url }>
										<Icon name="edit" size={ 16 } />
										{ __( 'Edit with Elementor', 'theme-builder-for-elementor' ) }
									</a>
								) }
								<a className="tbfe-btn tbfe-btn--primary" href={ result?.front ? config.siteUrl : result?.pages[ 0 ]?.url || config.siteUrl } target="_blank" rel="noreferrer">
									<Icon name="external" size={ 16 } />
									{ __( 'View', 'theme-builder-for-elementor' ) }
								</a>
							</div>
						) : (
							<span className="tbfe-muted">{ __( 'Importing… please keep this window open.', 'theme-builder-for-elementor' ) }</span>
						) }
					</div>
				</>
			) }
		</Modal>
	);
}
