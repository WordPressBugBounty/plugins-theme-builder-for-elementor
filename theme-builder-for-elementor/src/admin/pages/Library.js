import { __, sprintf } from '@wordpress/i18n';
import { Spinner } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { api } from '../api';
import { useStore } from '../store';
import TypeArt from '../components/TypeArt';
import Icon from '../components/Icon';
import { useLibrary, RefreshButton, PreviewImage } from '../library';
import TypeHelp from '../components/TypeHelp';
import { ProAction, ProBadge, ProBanner } from '../components/Pro';
import InstallWoo, { WooChip } from '../components/InstallWoo';
import ColorImportModal from '../components/ColorImportModal';

export default function Library( { filter } ) {
	const { data, putTemplate, toast } = useStore();
	const library = useLibrary();
	const { index, error, load } = library;
	const [ search, setSearch ] = useState( '' );
	const [ importing, setImporting ] = useState( 0 );
	const [ choosing, setChoosing ] = useState( null );

	const typeLabel = ( slug ) => data.types.find( ( t ) => t.slug === slug )?.label || slug;
	const matches = ( text ) => ! search || text.toLowerCase().includes( search.toLowerCase() );

	const doImport = ( item, colors = null ) => {
		setChoosing( null );
		setImporting( item.id );
		api.importItem( item.id, false, colors )
			.then( ( template ) => {
				putTemplate( template );
				toast(
					sprintf( /* translators: %s: demo title */ __( '“%s” was imported as a draft.', 'theme-builder-for-elementor' ), item.title ),
					'success',
					{ href: template.edit_url, label: __( 'Edit', 'theme-builder-for-elementor' ) }
				);
			} )
			.catch( ( e ) => toast( e.message, 'error' ) )
			.finally( () => setImporting( 0 ) );
	};

	if ( ! data.settings.library ) {
		return (
			<div className="tbfe-card tbfe-empty">
				<Icon name="sparkle" size={ 32 } />
				<h2>{ __( 'The template library is turned off', 'theme-builder-for-elementor' ) }</h2>
				<p>{ __( 'Turn it on in the settings to browse and import ready-made templates.', 'theme-builder-for-elementor' ) }</p>
				<a className="tbfe-btn tbfe-btn--primary" href="#/settings">
					{ __( 'Open settings', 'theme-builder-for-elementor' ) }
				</a>
			</div>
		);
	}

	const [ edition, setEdition ] = useState( '' );
	const items = ( index?.items || [] ).filter( ( i ) => ( ! filter || i.type === filter ) && ( ! edition || ( 'pro' === edition ) === !! i.pro ) && matches( i.title + ' ' + i.tags.join( ' ' ) ) );
	// A PRO demo is locked without an active license (or without WooCommerce for the shop templates).
	const locked = ( item ) => item.pro && ( ! data.pro.active || data.types.find( ( t ) => t.slug === item.type )?.locked );
	const current = data.types.find( ( t ) => t.slug === filter );

	return (
		<div className="tbfe-library">
			<div className="tbfe-list-head">
				<div>
					<h1 className="tbfe-h1">{ __( 'Template Library', 'theme-builder-for-elementor' ) }</h1>
					<p className="tbfe-muted">{ __( 'Ready-made designs for every part of the site. Preview them live and import with one click — they arrive as drafts.', 'theme-builder-for-elementor' ) }</p>
				</div>
				<div className="tbfe-list-head__tools">
					<label className="tbfe-search-field">
						<Icon name="search" size={ 16 } />
						<input type="search" value={ search } onChange={ ( e ) => setSearch( e.target.value ) } placeholder={ __( 'Search demos', 'theme-builder-for-elementor' ) } aria-label={ __( 'Search demos', 'theme-builder-for-elementor' ) } />
					</label>
					<RefreshButton library={ library } />
				</div>
			</div>

			<div className="tbfe-filters" role="tablist" aria-label={ __( 'Demo types', 'theme-builder-for-elementor' ) }>
				<a href="#/library" role="tab" aria-selected={ ! filter } className={ 'tbfe-filter' + ( ! filter ? ' is-active' : '' ) }>
					{ __( 'All templates', 'theme-builder-for-elementor' ) }
				</a>
				{ data.types.map( ( t ) => (
					<a key={ t.slug } href={ '#/library/' + t.slug } role="tab" aria-selected={ filter === t.slug } className={ 'tbfe-filter' + ( filter === t.slug ? ' is-active' : '' ) }>
						{ t.plural }
						{ t.pro && <ProBadge locked={ t.locked } /> }
					</a>
				) ) }
				<span className="tbfe-filters__edition" role="group" aria-label={ __( 'Edition', 'theme-builder-for-elementor' ) }>
					{ [
						[ '', __( 'All', 'theme-builder-for-elementor' ) ],
						[ 'free', __( 'Free', 'theme-builder-for-elementor' ) ],
						[ 'pro', 'PRO' ],
					].map( ( [ value, label ] ) => (
						<button key={ value } type="button" aria-pressed={ edition === value } className={ 'tbfe-edition' + ( edition === value ? ' is-active' : '' ) } onClick={ () => setEdition( value ) }>
							{ label }
						</button>
					) ) }
				</span>
			</div>

			<TypeHelp types={ data.types } type={ current } />
			{ current?.locked && <ProBanner title={ current.plural } /> }

			{ error && (
				<div className="tbfe-card tbfe-empty">
					<Icon name="warning" size={ 32 } />
					<h2>{ __( 'The library could not be loaded', 'theme-builder-for-elementor' ) }</h2>
					<p>{ error }</p>
					<button type="button" className="tbfe-btn tbfe-btn--primary" onClick={ () => load() }>
						{ __( 'Try again', 'theme-builder-for-elementor' ) }
					</button>
				</div>
			) }
			{ ! error && ! index && (
				<div className="tbfe-loading">
					<Spinner />
				</div>
			) }

			{ index && ! items.length && (
				<div className="tbfe-card tbfe-empty">
					<TypeArt type={ filter || 'section' } />
					<h2>{ __( 'No demos here yet', 'theme-builder-for-elementor' ) }</h2>
					<p>{ __( 'New designs are added regularly. Check again soon.', 'theme-builder-for-elementor' ) }</p>
				</div>
			) }

			{ index && (
				<div className="tbfe-demos">
					{ items.map( ( item ) => (
						<article key={ item.id } className={ 'tbfe-demo' + ( locked( item ) ? ' is-locked' : '' ) }>
							<div className="tbfe-demo__thumb">
								{ item.image ? <PreviewImage image={ item.image } /> : <TypeArt type={ item.type } /> }
								<span className="tbfe-demo__type">{ typeLabel( item.type ) }</span>
								{ item.new && <span className="tbfe-demo__new">{ __( 'New', 'theme-builder-for-elementor' ) }</span> }
								{ item.woocommerce && ! data.pro.woo && (
									<span className="tbfe-demo__woo">
										<WooChip />
									</span>
								) }
								{ item.pro && (
									<span className="tbfe-demo__pro">
										<ProBadge locked={ locked( item ) } />
									</span>
								) }
							</div>
							<div className="tbfe-demo__body">
								<h3>{ item.title }</h3>
								{ item.description && <p className="tbfe-muted">{ item.description }</p> }
								<div className="tbfe-demo__actions">
									{ item.preview && (
										<a className="tbfe-btn tbfe-btn--ghost tbfe-btn--sm" href={ item.preview } target="_blank" rel="noreferrer">
											<Icon name="eye" size={ 15 } />
											{ __( 'Live preview', 'theme-builder-for-elementor' ) }
										</a>
									) }
									{ locked( item ) && <ProAction short /> }
									{ ! locked( item ) && item.woocommerce && ! data.pro.woo && <InstallWoo short /> }
									{ ! locked( item ) && ( ! item.woocommerce || data.pro.woo ) && (
										<button type="button" className="tbfe-btn tbfe-btn--primary tbfe-btn--sm" onClick={ () => setChoosing( item ) } disabled={ !! importing }>
											<Icon name="download" size={ 15 } />
											{ importing === item.id ? __( 'Importing…', 'theme-builder-for-elementor' ) : __( 'Import', 'theme-builder-for-elementor' ) }
										</button>
									) }
								</div>
							</div>
						</article>
					) ) }
				</div>
			) }

			{ choosing && <ColorImportModal item={ choosing } onClose={ () => setChoosing( null ) } onImport={ ( colors ) => doImport( choosing, colors ) } /> }
		</div>
	);
}
