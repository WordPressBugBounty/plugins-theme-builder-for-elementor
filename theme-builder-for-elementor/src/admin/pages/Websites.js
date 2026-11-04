import { __, _n, sprintf } from '@wordpress/i18n';
import { Modal, Spinner } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { useStore } from '../store';
import { api } from '../api';
import TypeArt from '../components/TypeArt';
import Icon from '../components/Icon';
import SiteImportModal from '../components/SiteImportModal';
import LayoutChoice from '../components/LayoutChoice';
import ColorStep from '../components/ColorStep';
import { useLibrary, RefreshButton, ScrollPreview } from '../library';
import { ProAction, ProBadge, ProBanner } from '../components/Pro';
import InstallWoo, { WooChip } from '../components/InstallWoo';

const config = window.tbfeAdmin || {};

const DEFAULT_FILTERS = { plan: 'all', focus: [] };
const FILTERS_KEY = 'tbfe-websites-filters';

/**
 * Industries of a website: its category, several separated by commas ("Technology, Finance").
 */
const focusOf = ( s ) =>
	( s.category || '' )
		.split( ',' )
		.map( ( c ) => c.trim() )
		.filter( Boolean );
const inPlan = ( s, plan ) => 'all' === plan || ( 'pro' === plan ) === !! s.pro;
const inFocus = ( s, focus ) => ! focus.length || focusOf( s ).some( ( c ) => focus.includes( c ) );

/**
 * The filters stay while the user opens a website and comes back (for the browser tab only).
 */
function useStoredFilters() {
	const [ filters, set ] = useState( () => {
		try {
			const saved = JSON.parse( window.sessionStorage.getItem( FILTERS_KEY ) );
			if ( saved && [ 'all', 'free', 'pro' ].includes( saved.plan ) && Array.isArray( saved.focus ) ) {
				return { plan: saved.plan, focus: saved.focus.filter( ( c ) => 'string' === typeof c ) };
			}
		} catch ( e ) {}
		return DEFAULT_FILTERS;
	} );
	const setFilters = ( next ) => {
		set( next );
		try {
			window.sessionStorage.setItem( FILTERS_KEY, JSON.stringify( next ) );
		} catch ( e ) {}
	};
	return [ filters, setFilters ];
}

/**
 * Sidebar with the filters: Free / PRO and the industries, each with the number of websites it would show.
 */
function WebsiteFilters( { sites, filters, setFilters } ) {
	const counts = {};
	sites
		.filter( ( s ) => inPlan( s, filters.plan ) )
		.forEach( ( s ) => focusOf( s ).forEach( ( c ) => ( counts[ c ] = ( counts[ c ] || 0 ) + 1 ) ) );
	const names = [ ...new Set( [ ...Object.keys( counts ), ...filters.focus ] ) ].sort( ( a, b ) => a.localeCompare( b ) );
	const plans = [
		[ 'all', __( 'All', 'theme-builder-for-elementor' ) ],
		[ 'free', __( 'Free', 'theme-builder-for-elementor' ) ],
		[ 'pro', __( 'PRO', 'theme-builder-for-elementor' ) ],
	];
	const toggle = ( c ) =>
		setFilters( { ...filters, focus: filters.focus.includes( c ) ? filters.focus.filter( ( x ) => x !== c ) : [ ...filters.focus, c ] } );
	const active = 'all' !== filters.plan || filters.focus.length > 0;

	return (
		<aside className="tbfe-site-filters" aria-label={ __( 'Filter websites', 'theme-builder-for-elementor' ) }>
			<div className="tbfe-site-filters__group" role="group" aria-labelledby="tbfe-site-filters-plan">
				<h3 className="tbfe-site-filters__title" id="tbfe-site-filters-plan">
					{ __( 'Plan', 'theme-builder-for-elementor' ) }
				</h3>
				<div className="tbfe-site-filters__switch">
					{ plans.map( ( [ value, label ] ) => (
						<label key={ value } className={ 'tbfe-site-filters__segment' + ( filters.plan === value ? ' is-active' : '' ) }>
							<input type="radio" name="tbfe-filter-plan" value={ value } checked={ filters.plan === value } onChange={ () => setFilters( { ...filters, plan: value } ) } />
							<span>{ label }</span>
							<span className="tbfe-site-filters__segment-count">{ sites.filter( ( s ) => inPlan( s, value ) && inFocus( s, filters.focus ) ).length }</span>
						</label>
					) ) }
				</div>
			</div>
			{ names.length > 1 && (
				<div className="tbfe-site-filters__group" role="group" aria-labelledby="tbfe-site-filters-industry">
					<h3 className="tbfe-site-filters__title" id="tbfe-site-filters-industry">
						{ __( 'Industry', 'theme-builder-for-elementor' ) }
					</h3>
					{ names.map( ( c ) => (
						<label key={ c } className={ 'tbfe-site-filters__option' + ( filters.focus.includes( c ) ? ' is-active' : '' ) }>
							<input type="checkbox" checked={ filters.focus.includes( c ) } onChange={ () => toggle( c ) } />
							<span className="tbfe-site-filters__label">{ c }</span>
							<span className="tbfe-site-filters__count">{ counts[ c ] || 0 }</span>
						</label>
					) ) }
				</div>
			) }
			{ active && (
				<button type="button" className="tbfe-site-filters__clear" onClick={ () => setFilters( DEFAULT_FILTERS ) }>
					{ __( 'Clear filters', 'theme-builder-for-elementor' ) }
				</button>
			) }
		</aside>
	);
}

/**
 * Websites Import: website demos (a home page with subpages and its templates). Open a demo and pick the pages to
 * import from their previews.
 *
 * Opened from the editor of a page or post (config.into), one demo page is picked and its design goes into that page.
 */
export default function Websites( { siteId } ) {
	const { data } = useStore();
	const library = useLibrary();
	const { index, error, load } = library;
	const [ search, setSearch ] = useState( '' );
	const [ filters, setFilters ] = useStoredFilters();

	if ( ! data.settings.library ) {
		return (
			<div className="tbfe-card tbfe-empty">
				<Icon name="layers" size={ 32 } />
				<h2>{ __( 'The library is turned off', 'theme-builder-for-elementor' ) }</h2>
				<p>{ __( 'Turn it on in the settings to import website demos.', 'theme-builder-for-elementor' ) }</p>
				<a className="tbfe-btn tbfe-btn--primary" href="#/settings">
					{ __( 'Open settings', 'theme-builder-for-elementor' ) }
				</a>
			</div>
		);
	}
	if ( error ) {
		return (
			<div className="tbfe-card tbfe-empty">
				<Icon name="warning" size={ 32 } />
				<h2>{ __( 'The website demos could not be loaded', 'theme-builder-for-elementor' ) }</h2>
				<p>{ error }</p>
				<button type="button" className="tbfe-btn tbfe-btn--primary" onClick={ () => load() }>
					{ __( 'Try again', 'theme-builder-for-elementor' ) }
				</button>
			</div>
		);
	}
	if ( ! index ) {
		return (
			<div className="tbfe-loading">
				<Spinner />
			</div>
		);
	}

	const into = config.into || null;
	const site = siteId ? index.sites.find( ( s ) => String( s.id ) === String( siteId ) ) : null;
	if ( site ) {
		return <WebsiteDetail site={ site } into={ into } />;
	}

	const found = index.sites.filter( ( s ) => ! search || ( s.title + ' ' + s.description + ' ' + s.tags.join( ' ' ) ).toLowerCase().includes( search.toLowerCase() ) );
	const sites = found.filter( ( s ) => inPlan( s, filters.plan ) && inFocus( s, filters.focus ) );

	return (
		<div className="tbfe-websites">
			{ into && <IntoBanner into={ into } /> }
			<div className="tbfe-list-head">
				<div>
					<h1 className="tbfe-h1">{ __( 'Websites Import', 'theme-builder-for-elementor' ) }</h1>
					<p className="tbfe-muted">{ __( 'Complete website designs. Open one, pick the pages you like and import just those – with the header and footer if you want.', 'theme-builder-for-elementor' ) }</p>
				</div>
				<div className="tbfe-list-head__tools">
					<label className="tbfe-search-field">
						<Icon name="search" size={ 16 } />
						<input type="search" value={ search } onChange={ ( e ) => setSearch( e.target.value ) } placeholder={ __( 'Search websites', 'theme-builder-for-elementor' ) } aria-label={ __( 'Search websites', 'theme-builder-for-elementor' ) } />
					</label>
					<RefreshButton library={ library } />
				</div>
			</div>

			<div className="tbfe-websites__layout">
				{ index.sites.length > 0 && <WebsiteFilters sites={ found } filters={ filters } setFilters={ setFilters } /> }
				<div className="tbfe-websites__main">
					{ ! sites.length && index.sites.length > 0 ? (
						<div className="tbfe-card tbfe-empty">
							<Icon name="search" size={ 32 } />
							<h2>{ __( 'No websites match the filters', 'theme-builder-for-elementor' ) }</h2>
							<p>{ __( 'Try another industry or clear the filters.', 'theme-builder-for-elementor' ) }</p>
							<button
								type="button"
								className="tbfe-btn tbfe-btn--primary"
								onClick={ () => {
									setFilters( DEFAULT_FILTERS );
									setSearch( '' );
								} }
							>
								{ __( 'Clear filters', 'theme-builder-for-elementor' ) }
							</button>
						</div>
					) : ! sites.length ? (
						<div className="tbfe-card tbfe-empty">
							<TypeArt type="header" />
							<h2>{ __( 'No website demos yet', 'theme-builder-for-elementor' ) }</h2>
							<p>{ __( 'New websites are added regularly. Check again soon.', 'theme-builder-for-elementor' ) }</p>
						</div>
					) : (
						<div className="tbfe-demos tbfe-demos--sites">
							{ sites.map( ( s ) => (
								<article key={ s.id } className={ 'tbfe-demo tbfe-demo--site' + ( s.pro && ! data.pro.active ? ' is-locked' : '' ) }>
									<a className="tbfe-demo__thumb" href={ '#/websites/' + s.id } aria-label={ s.title }>
										{ s.image ? <ScrollPreview image={ s.image } /> : <TypeArt type="header" /> }
										<span className="tbfe-demo__type">
											{ sprintf( /* translators: %d: number of pages */ _n( '%d page', '%d pages', s.pages.length, 'theme-builder-for-elementor' ), s.pages.length ) }
										</span>
										{ s.new && <span className="tbfe-demo__new">{ __( 'New', 'theme-builder-for-elementor' ) }</span> }
										{ s.pro && (
											<span className="tbfe-demo__pro">
												<ProBadge locked={ ! data.pro.active } />
											</span>
										) }
										{ s.woocommerce && ! data.pro.woo && (
											<span className="tbfe-demo__woo">
												<WooChip />
											</span>
										) }
									</a>
									<div className="tbfe-demo__body">
										<h3>
											<a href={ '#/websites/' + s.id }>{ s.title }</a>
										</h3>
										{ s.description && <p className="tbfe-muted">{ s.description }</p> }
										<p className="tbfe-demo__pages">{ s.pages.map( ( p ) => p.title ).join( ' · ' ) }</p>
										<div className="tbfe-demo__actions">
											<a className="tbfe-btn tbfe-btn--ghost tbfe-btn--sm" href={ s.preview } target="_blank" rel="noreferrer">
												<Icon name="eye" size={ 15 } />
												{ __( 'Live preview', 'theme-builder-for-elementor' ) }
											</a>
											<a className="tbfe-btn tbfe-btn--primary tbfe-btn--sm" href={ '#/websites/' + s.id }>
												{ into ? __( 'Choose a page', 'theme-builder-for-elementor' ) : __( 'Choose pages', 'theme-builder-for-elementor' ) }
											</a>
										</div>
									</div>
								</article>
							) ) }
						</div>
					) }
				</div>
			</div>
		</div>
	);
}

/**
 * The page or post that gets the design.
 */
function IntoBanner( { into } ) {
	return (
		<div className="tbfe-into-banner">
			<Icon name="download" size={ 20 } />
			<p>
				{ sprintf(
					/* translators: %s: title of the page or post */
					__( 'Importing a design into “%s”. Open a website and pick the page you like.', 'theme-builder-for-elementor' ),
					into.title
				) }
			</p>
			<a className="tbfe-btn tbfe-btn--ghost tbfe-btn--sm" href={ into.back }>
				{ __( 'Cancel', 'theme-builder-for-elementor' ) }
			</a>
		</div>
	);
}

/**
 * Confirms that the content of the page or post is replaced, imports the demo page into it and opens Elementor.
 */
function IntoImportModal( { into, page, onClose } ) {
	const { toast } = useStore();
	const [ busy, setBusy ] = useState( false );
	const [ layout, setLayout ] = useState( 'full-width' );
	const [ colors, setColors ] = useState( null );

	const run = async () => {
		setBusy( true );
		try {
			const done = await api.importPage( page.id, 'draft', into.id, layout, colors );
			await api.importFinish( { pages: [ { id: done.id, demo_url: done.demo_url, home: false } ], templates: [], front: false, menu: false, name: '' } );
			window.location.href = done.edit_url;
		} catch ( e ) {
			setBusy( false );
			toast( e.message, 'error' );
		}
	};

	return (
		<Modal title={ __( 'Import the design', 'theme-builder-for-elementor' ) } onRequestClose={ busy ? () => {} : onClose } className="tbfe-modal">
			<p className="tbfe-modal__lead">
				{ sprintf(
					/* translators: 1: demo page, 2: title of the page or post */
					__( 'The content of “%2$s” is replaced with the design of “%1$s”. The previous content stays in the revisions of the post.', 'theme-builder-for-elementor' ),
					page.title,
					into.title
				) }
			</p>
			<LayoutChoice value={ layout } onChange={ setLayout } />
			<ColorStep entries={ [ { kind: 'page', id: page.id } ] } preview={ page.preview } onChange={ setColors } />
			<div className="tbfe-modal__footer">
				<button type="button" className="tbfe-btn tbfe-btn--ghost" onClick={ onClose } disabled={ busy }>
					{ __( 'Cancel', 'theme-builder-for-elementor' ) }
				</button>
				<button type="button" className="tbfe-btn tbfe-btn--primary" onClick={ run } disabled={ busy }>
					{ busy ? __( 'Importing…', 'theme-builder-for-elementor' ) : __( 'Import and edit with Elementor', 'theme-builder-for-elementor' ) }
				</button>
			</div>
		</Modal>
	);
}

function WebsiteDetail( { site, into } ) {
	const { data } = useStore();
	const locked = site.pro && ! data.pro.active;
	// The website needs WooCommerce: install it first (Import stays off until then).
	const needsWoo = site.woocommerce && ! data.pro.woo;
	const [ pages, setPages ] = useState( [] );
	const [ templates, setTemplates ] = useState( [] );
	const [ importing, setImporting ] = useState( false );

	const typeLabel = ( slug ) => data.types.find( ( t ) => t.slug === slug )?.label || slug;
	const toggle = ( list, setList, id ) => setList( list.includes( id ) ? list.filter( ( x ) => x !== id ) : [ ...( into ? [] : list ), id ] );
	const count = pages.length + templates.length;

	return (
		<div className="tbfe-website">
			{ into && <IntoBanner into={ into } /> }
			<a className="tbfe-back" href="#/websites">
				← { __( 'All websites', 'theme-builder-for-elementor' ) }
			</a>
			<div className="tbfe-list-head">
				<div>
					<h1 className="tbfe-h1">{ site.title }</h1>
					{ site.description && <p className="tbfe-muted">{ site.description }</p> }
				</div>
				<div className="tbfe-list-head__tools">
					<a className="tbfe-btn tbfe-btn--ghost" href={ site.preview } target="_blank" rel="noreferrer">
						<Icon name="eye" size={ 16 } />
						{ __( 'Live preview', 'theme-builder-for-elementor' ) }
					</a>
				</div>
			</div>

			{ locked && <ProBanner title={ __( 'This is a PRO website', 'theme-builder-for-elementor' ) } /> }
			{ ! locked && needsWoo && (
				<div className="tbfe-pro-banner tbfe-woo-banner">
					<span className="tbfe-pro-banner__icon">
						<Icon name="cart" size={ 20 } />
					</span>
					<div className="tbfe-pro-banner__text">
						<strong>{ __( 'This website needs WooCommerce', 'theme-builder-for-elementor' ) }</strong>
						<p>{ __( 'Its pages use the shop widgets or templates of WooCommerce. Install WooCommerce first – it takes a moment – then import the pages.', 'theme-builder-for-elementor' ) }</p>
					</div>
					<InstallWoo size="" />
				</div>
			) }
			<h2 className="tbfe-h2">{ into ? __( 'Choose the page to import', 'theme-builder-for-elementor' ) : __( 'Choose the pages to import', 'theme-builder-for-elementor' ) }</h2>
			<div className="tbfe-page-picker">
				{ site.pages.map( ( p ) => {
					const selected = pages.includes( p.id );
					return (
						<article key={ p.id } className={ 'tbfe-page-card' + ( selected ? ' is-selected' : '' ) }>
							<button type="button" className="tbfe-page-card__pick" aria-pressed={ selected } onClick={ () => toggle( pages, setPages, p.id ) }>
								<span className="tbfe-page-card__thumb">
									{ p.image ? <ScrollPreview image={ p.image } /> : <TypeArt type="single" /> }
									<span className="tbfe-page-card__check" aria-hidden="true">
										<Icon name="check" size={ 16 } />
									</span>
								</span>
								<span className="tbfe-page-card__name">
									{ p.title }
									{ p.home && <span className="tbfe-chip tbfe-chip--home">{ __( 'Home', 'theme-builder-for-elementor' ) }</span> }
								</span>
							</button>
							<div className="tbfe-page-card__foot">
								<span className="tbfe-muted">{ selected ? __( 'Selected', 'theme-builder-for-elementor' ) : __( 'Click to select', 'theme-builder-for-elementor' ) }</span>
								{ p.preview && (
									<a href={ p.preview } target="_blank" rel="noreferrer">
										<Icon name="eye" size={ 14 } />
										{ __( 'Preview', 'theme-builder-for-elementor' ) }
									</a>
								) }
							</div>
						</article>
					);
				} ) }
			</div>

			<div className={ 'tbfe-import-bar' + ( count ? ' is-active' : '' ) }>
				<span>
					{ into
						? sprintf(
								/* translators: %s: title of the page or post */
								__( 'Pick one page. Its design goes into “%s”.', 'theme-builder-for-elementor' ),
								into.title
						  )
						: count
						? sprintf(
								/* translators: %d: number of pages */
								_n( '%d page selected – the header, footer and layouts can be chosen in the next step', '%d pages selected – the header, footer and layouts can be chosen in the next step', pages.length, 'theme-builder-for-elementor' ),
								pages.length
						  )
						: __( 'Select the pages you want to import.', 'theme-builder-for-elementor' ) }
				</span>
				<div className="tbfe-import-bar__buttons">
					{ count > 0 && (
						<button
							type="button"
							className="tbfe-btn tbfe-btn--ghost"
							onClick={ () => {
								setPages( [] );
								setTemplates( [] );
							} }
						>
							{ __( 'Clear', 'theme-builder-for-elementor' ) }
						</button>
					) }
					{ locked && <ProAction size="" /> }
					{ ! locked && needsWoo && <InstallWoo size="" /> }
					{ ! locked && ! needsWoo && (
						<button type="button" className="tbfe-btn tbfe-btn--primary" disabled={ ! count } onClick={ () => setImporting( true ) }>
							<Icon name="download" size={ 16 } />
							{ into ? __( 'Import into this post', 'theme-builder-for-elementor' ) : __( 'Import selected', 'theme-builder-for-elementor' ) }
						</button>
					) }
				</div>
			</div>

			{ importing && into && <IntoImportModal into={ into } page={ site.pages.find( ( p ) => p.id === pages[ 0 ] ) } onClose={ () => setImporting( false ) } /> }

			{ importing && ! into && (
				<SiteImportModal
					site={ site }
					pageIds={ pages }
					templateIds={ templates }
					onClose={ () => setImporting( false ) }
					onDone={ () => {
						setPages( [] );
						setTemplates( [] );
					} }
				/>
			) }
		</div>
	);
}

