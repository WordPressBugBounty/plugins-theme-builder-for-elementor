import { __, _n, sprintf } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { useStore } from '../store';
import TypeArt from '../components/TypeArt';
import Icon from '../components/Icon';
import ConditionsModal from '../components/ConditionsModal';
import { status } from '../components/TemplateRow';
import { ProAction, ProBadge, ProBanner } from '../components/Pro';

function MigrationNotice( { report } ) {
	const [ hidden, setHidden ] = useState( () => window.localStorage?.getItem( 'tbfe-migration-seen' ) === report.date );
	if ( hidden || ! report.templates ) {
		return null;
	}
	const close = () => {
		try {
			window.localStorage.setItem( 'tbfe-migration-seen', report.date );
		} catch ( e ) {}
		setHidden( true );
	};
	return (
		<div className="tbfe-banner">
			<Icon name="sparkle" size={ 22 } />
			<div>
				<strong>{ __( 'Welcome to Theme Builder 2.0', 'theme-builder-for-elementor' ) }</strong>
				<p>
					{ sprintf(
						/* translators: 1: number of templates, 2: number of conditions */
						__( 'Your %1$d templates from version 1 were moved here with all their designs, and your settings became %2$d display conditions. Check them below.', 'theme-builder-for-elementor' ),
						report.templates,
						report.conditions
					) }
				</p>
			</div>
			<button type="button" className="tbfe-icon-btn" onClick={ close } aria-label={ __( 'Hide', 'theme-builder-for-elementor' ) }>
				<Icon name="close" size={ 16 } />
			</button>
		</div>
	);
}

/**
 * After Settings › Tools › Return to version 1 (and maybe an update again): the templates wait for the migration.
 */
function RevertedNotice() {
	return (
		<div className="tbfe-banner">
			<Icon name="sparkle" size={ 22 } />
			<div>
				<strong>{ __( 'Your templates are in version 1', 'theme-builder-for-elementor' ) }</strong>
				<p>{ __( 'They were returned to version 1, so Theme Builder 2.0 does not show them on the site. Migrate them again to use them here.', 'theme-builder-for-elementor' ) }</p>
				<p>
					<a className="tbfe-btn tbfe-btn--soft tbfe-btn--sm" href="#/settings/tools">
						{ __( 'Migrate the templates', 'theme-builder-for-elementor' ) }
					</a>
				</p>
			</div>
		</div>
	);
}

export default function Dashboard( { onNew } ) {
	const { data } = useStore();
	const [ conditionsFor, setConditionsFor ] = useState( null );
	const config = window.tbfeAdmin || {};
	const located = data.types.filter( ( t ) => t.location && 'woocommerce' !== t.group );
	const parts = data.types.filter( ( t ) => ! t.location && 'woocommerce' !== t.group );
	const shop = data.types.filter( ( t ) => 'woocommerce' === t.group );
	const shopLocked = shop.some( ( t ) => t.locked );
	const activeCount = data.templates.filter( ( t ) => 'active' === status( t, data.types.find( ( x ) => x.slug === t.type ) ).key ).length;
	const conditionsTemplate = conditionsFor && data.templates.find( ( t ) => t.id === conditionsFor.id );

	const card = ( type ) => {
		const templates = data.templates.filter( ( t ) => t.type === type.slug );
		const active = templates.filter( ( t ) => 'active' === status( t, type ).key );
		return (
			<article key={ type.slug } className={ 'tbfe-part' + ( type.locked ? ' is-locked' : '' ) }>
				<a className="tbfe-part__art" href={ '#/templates/' + type.slug } tabIndex={ -1 } aria-hidden="true">
					<TypeArt type={ type.slug } />
				</a>
				<div className="tbfe-part__body">
					<div className="tbfe-part__head">
						<h3>
							<a href={ '#/templates/' + type.slug }>{ type.label }</a>
							{ type.pro && <ProBadge locked={ type.locked } /> }
						</h3>
						{ type.location && ! type.locked && (
							<span className={ 'tbfe-pill tbfe-pill--' + ( active.length ? 'active' : 'idle' ) }>
								{ active.length ? __( 'Custom', 'theme-builder-for-elementor' ) : __( 'Theme default', 'theme-builder-for-elementor' ) }
							</span>
						) }
					</div>
					<p className="tbfe-muted">{ type.description }</p>
					{ active.length > 0 && (
						<ul className="tbfe-part__active">
							{ active.slice( 0, 2 ).map( ( t ) => (
								<li key={ t.id }>
									<button type="button" className="tbfe-link" onClick={ () => setConditionsFor( t ) } disabled={ t.locked }>
										{ t.title }
									</button>
									<span>{ t.summary }</span>
								</li>
							) ) }
						</ul>
					) }
					<div className="tbfe-part__foot">
						<span className="tbfe-muted">
							{ sprintf( /* translators: %d: number of templates */ _n( '%d template', '%d templates', templates.length, 'theme-builder-for-elementor' ), templates.length ) }
						</span>
						<div className="tbfe-part__btns">
							<a className="tbfe-icon-btn" href={ '#/library/' + type.slug } title={ __( 'Demos', 'theme-builder-for-elementor' ) } aria-label={ __( 'Demos', 'theme-builder-for-elementor' ) }>
								<Icon name="sparkle" size={ 16 } />
							</a>
							{ type.locked ? (
								<ProAction short />
							) : (
								<button type="button" className="tbfe-btn tbfe-btn--soft tbfe-btn--sm" onClick={ () => onNew( type.slug ) }>
									<Icon name="plus" size={ 15 } />
									{ __( 'Create', 'theme-builder-for-elementor' ) }
								</button>
							) }
						</div>
					</div>
				</div>
			</article>
		);
	};

	return (
		<div className="tbfe-dashboard">
			{ data.reverted ? <RevertedNotice /> : data.migration && <MigrationNotice report={ data.migration } /> }

			<section className="tbfe-hero">
				<div className="tbfe-hero__text">
					<h1>{ __( 'Design every part of your site with Elementor', 'theme-builder-for-elementor' ) }</h1>
					<p>{ __( 'Create a template, design it in Elementor and choose where it is shown. Your theme stays, the plugin replaces just the parts you design.', 'theme-builder-for-elementor' ) }</p>
					<ol className="tbfe-steps">
						<li>
							<span>1</span>
							{ __( 'Create or import a template', 'theme-builder-for-elementor' ) }
						</li>
						<li>
							<span>2</span>
							{ __( 'Design it with Elementor', 'theme-builder-for-elementor' ) }
						</li>
						<li>
							<span>3</span>
							{ __( 'Set the display conditions', 'theme-builder-for-elementor' ) }
						</li>
					</ol>
					<div className="tbfe-hero__actions">
						<button type="button" className="tbfe-btn tbfe-btn--primary tbfe-btn--lg" onClick={ () => onNew() }>
							<Icon name="plus" size={ 18 } />
							{ __( 'Create template', 'theme-builder-for-elementor' ) }
						</button>
						<a className="tbfe-btn tbfe-btn--light tbfe-btn--lg" href="#/websites">
							<Icon name="sparkle" size={ 18 } />
							{ __( 'Import a website', 'theme-builder-for-elementor' ) }
						</a>
					</div>
				</div>
				<dl className="tbfe-hero__stats">
					<div>
						<dt>{ __( 'Active templates', 'theme-builder-for-elementor' ) }</dt>
						<dd>{ activeCount }</dd>
					</div>
					<div>
						<dt>{ __( 'Theme', 'theme-builder-for-elementor' ) }</dt>
						<dd className="tbfe-hero__small">{ data.theme.name }</dd>
					</div>
					<div>
						<dt>{ __( 'Elementor', 'theme-builder-for-elementor' ) }</dt>
						<dd className="tbfe-hero__small">{ data.elementor }</dd>
					</div>
				</dl>
			</section>

			<h2 className="tbfe-h2">{ __( 'Site parts', 'theme-builder-for-elementor' ) }</h2>
			<div className="tbfe-parts">{ located.map( card ) }</div>

			<h2 className="tbfe-h2">{ __( 'Building blocks', 'theme-builder-for-elementor' ) }</h2>
			<div className="tbfe-parts tbfe-parts--small">{ parts.map( card ) }</div>

			{ shop.length > 0 && (
				<>
					<h2 className="tbfe-h2 tbfe-h2--pro">
						{ __( 'WooCommerce shop', 'theme-builder-for-elementor' ) }
						<ProBadge locked={ shopLocked } />
					</h2>
					{ shopLocked && <ProBanner title={ __( 'Design your whole shop with Elementor', 'theme-builder-for-elementor' ) } /> }
					<div className="tbfe-parts">{ shop.map( card ) }</div>
				</>
			) }

			<section className="tbfe-card tbfe-help">
				<div>
					<h2 className="tbfe-h2">{ __( 'Need a hand?', 'theme-builder-for-elementor' ) }</h2>
					<p className="tbfe-muted">{ __( 'Step-by-step guides for headers, archives, conditions and more.', 'theme-builder-for-elementor' ) }</p>
				</div>
				<div className="tbfe-help__links">
					<a className="tbfe-btn tbfe-btn--soft" href={ config.docsUrl } target="_blank" rel="noreferrer">
						<Icon name="book" size={ 16 } />
						{ __( 'Documentation', 'theme-builder-for-elementor' ) }
					</a>
					<a className="tbfe-btn tbfe-btn--soft" href={ config.demosUrl } target="_blank" rel="noreferrer">
						<Icon name="globe" size={ 16 } />
						{ __( 'Live demos', 'theme-builder-for-elementor' ) }
					</a>
				</div>
			</section>

			{ conditionsTemplate && <ConditionsModal template={ conditionsTemplate } onClose={ () => setConditionsFor( null ) } /> }
		</div>
	);
}
